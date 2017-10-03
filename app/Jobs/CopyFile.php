<?php

namespace App\Jobs;

use Log;
use Exception;
use App\AssignmentFile;
use App\Events\FileCopied;
use App\Events\ReportError;
use Google_Service_Exception;
use PulkitJalan\Google\Client;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_Permission;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Google_Service_Drive_ParentReference;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class CopyFile
 * @package App\Jobs
 */
class CopyFile extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $count;
    protected $config;
    protected $queue_no;
    protected $file_name;
    protected $section_id;
    protected $template_id;
    protected $assignment_id;
    protected $enrollment_id;
    protected $student_email;
    protected $teacher_email;
    protected $pusher_channel;
    protected $enrollment_folder_id;
    protected $assignment_folder_id;

    /**
     * Create a new job instance.
     * @param $section_id
     * @param $template_id
     * @param $enrollment_id
     * @param $enrollment_folder_id
     * @param $assignment_id
     * @param $assignment_folder_id
     * @param $file_name
     * @param $student_email
     * @param $teacher_email
     * @param $config
     * @param $count
     * @param $pusher_channel
     * @param $queue_no
     */
    public function __construct($section_id, $template_id, $enrollment_id, $enrollment_folder_id, $assignment_id, $assignment_folder_id, $file_name, $student_email, $teacher_email, $config, $count, $pusher_channel, $queue_no)
    {
        $this->count = $count;
        $this->config = $config;
        $this->queue_no = $queue_no;
        $this->file_name = $file_name;
        $this->section_id = $section_id;
        $this->template_id = $template_id;
        $this->enrollment_id = $enrollment_id;
        $this->assignment_id = $assignment_id;
        $this->student_email = $student_email;
        $this->teacher_email = $teacher_email;
        $this->pusher_channel = $pusher_channel;
        $this->assignment_folder_id = $assignment_folder_id;
        $this->enrollment_folder_id = $enrollment_folder_id;
    }

    /**
     * Execute the job.
     *
     * @throws Exception
     * @throws \PulkitJalan\Google\Exceptions\UnknownServiceException
     */
    public function handle()
    {
        $client = new Client($this->config, $this->teacher_email);
        $service = $client->make('drive');

        // Make a new copy of the template file.
        $copiedFile = new Google_Service_Drive_DriveFile();
        $copiedFile->setName($this->file_name);
        $tries = 0;
        do {
            $success = true;
            try {
                $copiedFile = $service->files->copy($this->template_id, $copiedFile);
            } catch (Exception $e) {
                // there was an error when trying to copy the file, you need to retry.
                $success = false;
                // Exponential Backoff retry.
                time_nanosleep(2^$tries, rand(0, 1000000000));
                $tries++;
                if ($tries > 6) {
                    // The file could not be copied.
                    Log::error("Error while copying file", ['message' => $e->getMessage(),'file' => $this->file_name, 'student'=>$this->student_email]);
                    $message = "The file for ".$this->student_email." could not be copied";
                    event(new ReportError('file', $message, $this->count, $this->queue_no, $this->pusher_channel));
                    return;
                }
            }
        } while (!$success);
        // Change ownership to the student
        $newPermission = new Google_Service_Drive_Permission();
        $newPermission->setEmailAddress($this->student_email);
        $newPermission->setType("user");
        $newPermission->setRole("owner");

        $tries = 0; // Reset tries back to zero
        do {
            $success = true;
            try {
                $service->permissions->create($copiedFile->id, $newPermission, ['transferOwnership' => true]);
            } catch (Exception $e) {
                // there was an error when updating the file permissions, you need to retry.
                $success = false;
                // Exponential Back off
                time_nanosleep(2^$tries, rand(0, 1000000000));
                $tries++;
                if ($tries > 6) {
                    // Could not make the student the owner of the copied file.
                    $service->files->delete($copiedFile->id);
                    Log::error("Error while setting file permissions", ['message' => $e->getMessage(),'file' => $this->file_name, 'student'=>$this->student_email]);
                    $message = $e->getMessage().' Error encountered while changing the ownership of file to '.$this->student_email.". Please check this email address on your Google Apps Domain";
                    event(new ReportError('file', $message, $this->count, $this->queue_no, $this->pusher_channel));
                    return;
                }
            }
        } while (!$success);

        // Move the file into the Assignment Folders
        $emptyFileMetadata = new Google_Service_Drive_DriveFile();
        // Retrieve the existing parents to remove
        $file = $service->files->get($copiedFile->id, array('fields' => 'parents'));
        $previousParents = join(',', $file->parents);
        // Move the file to the new folder
        $tries = 0; // Reset tries back to 0
        do {
            $success = true;
            try {
                $copiedFile = $service->files->update($copiedFile->id, $emptyFileMetadata, array(
                    'addParents' => $this->assignment_folder_id,
                    'removeParents' => $previousParents,
                    'fields' => 'id, parents'));
            } catch (Exception $e) {
                // There was an error when adding the parents, you need to retry.
                $success = false;
                // Exponential Back off
                time_nanosleep(2^$tries, rand(0, 1000000000));
                $tries++;
                if ($tries > 6) {
                    // The file could not be moved into the Assignment Folder
                    //$service->files->delete($copiedFile->id);
                    Log::error("Error while moving file to assignment folder", ['message' => $e->getMessage(),'file' => $this->file_name, 'student'=>$this->student_email]);
                    $message = $e->getMessage()." Encountered an error while moving ".$this->file_name." into the Assignment folder!";
                    event(new ReportError('file', $message, $this->count, $this->queue_no, $this->pusher_channel));
                    return;
                }
            }
        } while (!$success);

        // Connect at the Student
        $client = new Client($this->config, $this->student_email);
        $service = $client->make('drive');

        // Move the file into the Student's Enrollment folder
        $tries = 0; // Reset tries back to 0
        do {
            $success = true;
            try {
                $copiedFile = $service->files->update($copiedFile->id, $emptyFileMetadata, array(
                    'addParents' => $this->enrollment_folder_id,
                    'fields' => 'id, parents'));
            } catch (Google_Service_Exception $e) {
                // There was an error when adding the parents, you need to retry.
                $success = false;
                // Exponential Back off
                time_nanosleep(2^$tries, rand(0, 1000000000));
                $tries++;
                if ($tries > 6) {
                    // The file could not be moved into the student's folder
                    Log::error("Error while moving file to student folder", ['message' => $e->getMessage(),'file' => $this->file_name, 'student'=>$this->student_email]);
                    $message="Encountered an error while moving ".$this->file_name." into the Student's Folder. Please ensure that the student's account is active on your Google Apps domain and that the email address matches the one saved in Schoology (".$this->student_email.")";
                    event(new ReportError('file', $message, $this->count, $this->queue_no, $this->pusher_channel));
                    return;
                }
            }
        } while (!$success);

        // Create the AssignmentFile to save in the database
        AssignmentFile::create([
            'enrollment_id'=>$this->enrollment_id,
            'assignment_id'=>$this->assignment_id,
            'file_id'=>$copiedFile->id,
            'name'=>$this->file_name,
            'link'=>$copiedFile->webViewLink
        ]);
        event(new FileCopied($copiedFile, $this->section_id, $this->count, $this->pusher_channel, $this->queue_no));
    }
}
