<?php

namespace App\Jobs;

use Log;
use App\User;
use Exception;
use App\Enrollment;
use Google_Auth_Exception;
use App\Events\ReportError;
use App\Events\UserUpdated;
use App\Facades\UserService;
use Google_Service_Exception;
use PulkitJalan\Google\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Contracts\Queue\ShouldQueue;
use Avanderbergh\Schoology\Facades\Schoology;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class ProcessEnrollment
 * @package App\Jobs
 */
class ProcessEnrollment extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $uid;
    protected $count;
    protected $student;
    protected $queue_no;
    protected $school_id;
    protected $schoology;
    protected $section_id;
    protected $max_results;
    protected $section_name;
    protected $google_config;
    protected $teacher_email;
    protected $pusher_channel;
    protected $students_folder_id;

    /**
     * Create a new job instance.
     *
     * @param $uid
     * @param $student
     * @param $section_id
     * @param $school_id
     * @param $students_folder_id
     * @param $teacher_email
     * @param $section_name
     * @param $google_config
     * @param $count
     * @param $max_results
     * @param $pusher_channel
     * @param $queue_no
     * @internal param $schoology
     */
    public function __construct($uid, $student, $section_id, $school_id, $students_folder_id, $teacher_email, $section_name, $google_config, $count, $max_results, $pusher_channel, $queue_no)
    {
        $this->uid = $uid;
        $this->count = $count;
        $this->student = $student;
        $this->queue_no = $queue_no;
        $this->school_id = $school_id;
        $this->section_id = $section_id;
        $this->max_results = $max_results;
        $this->section_name = $section_name;
        $this->google_config = $google_config;
        $this->teacher_email = $teacher_email;
        $this->pusher_channel = $pusher_channel;
        $this->students_folder_id = $students_folder_id;
    }

    /**
     * Execute the job.
     *
     * @throws Exception
     * @throws \PulkitJalan\Google\Exceptions\UnknownServiceException
     */
    public function handle()
    {
        Bugsnag::setMetaData([
            'user' => $this->uid,
            'email' => $this->teacher_email,
            'student' => $this->student,
            'school' => $this->school_id,
            'section' => $this->section_id,
        ], false);
        // Reconnect to the database to prevent the STMT_PREPARE error
        DB::connection()->reconnect();
        // Get the student's email from Schoology
        Schoology::authorize($this->uid, time());
        $schoology_user = Schoology::apiResult('users/' . $this->student->uid);
        if (!filter_var($schoology_user->primary_email, FILTER_VALIDATE_EMAIL)) {
            // The user's email address is invalid.
            Log::warning('Email Validation Failed',['user' => $schoology_user]);
            $message="User ".$this->student->name_display." has no valid email address assigned in Schoology! Please assign a valid Google Apps email address to this user.";
                event(new ReportError('user', $message, $this->count, $this->queue_no, $this->pusher_channel));
                return;
        }
        try {
            $user = User::findOrFail($this->student->uid);
            $user->email = $schoology_user->primary_email;
            $user->name = htmlspecialchars_decode($schoology_user->name_display, ENT_QUOTES);
            $user->name_first = $schoology_user->name_first;
            $user->name_last = $schoology_user->name_last;
            $user->name_first_preferred = $schoology_user->name_first_preferred;
            $user->picture = $schoology_user->picture_url;
            $user->save();
        } catch (ModelNotFoundException $e) {
            if ($student_folder = UserService::createStudentFolder($this->google_config, $schoology_user, $this->count, $this->queue_no, $this->pusher_channel)) {
                // The student folder was created.
                Log::info('Creating User: ', [
                    'user' => $schoology_user,
                    'school' => $this->school_id,
                    'section' => $this->section_id,
                    'teacher_email' => $this->teacher_email,
                ]);
                User::create([
                    'id' => $this->student->uid,
                    'name' => htmlspecialchars_decode($this->student->name_display, ENT_QUOTES),
                    'name_first' => htmlspecialchars_decode($schoology_user->name_first, ENT_QUOTES),
                    'name_last' => htmlspecialchars_decode($schoology_user->name_last, ENT_QUOTES),
                    'name_first_preferred' => htmlspecialchars_decode($schoology_user->name_first_preferred, ENT_QUOTES),
                    'email' => $schoology_user->primary_email,
                    'folder_id' => $student_folder->id,
                    'picture' => $this->student->picture_url,
                    'school_id' => $this->school_id,
                ]);
                $user = User::find($this->student->uid);
                $message = "Dear ".$this->student->name_first.",\r\n\r\nDrive Companion is an App that links Schoology to your Google Drive. A folder named Drive Companion has been created in your Google Drive.\r\n\r\nPlease do not delete this folder as it will be used to share, collect and distribute files for your courses in Schoology.";
                UserService::sendMessage($user, "Drive Companion", $message);
            } else {
                // no student folder was created.
                Log::error('Student folder could not be created', [
                'user' => $this->student->uid,
                'school' => $this->school_id,
                'section' => $this->section_id,
                ]);
                $message="Could not create a Drive Companion folder for ".$this->student->name_display;
                event(new ReportError('user', $message, $this->count, $this->queue_no, $this->pusher_channel));
                return;
            }
        }
        // Check if the User's Drive Companion folder has been deleted.
        $client = new Client($this->google_config, $user->email);
        $service = $client->make('drive');
        try {
            $service->files->get($user->folder_id);
        } catch (Google_Service_Exception $e) {
            if ($e->getCode()==404) {
                Log::info('User folder was deleted', ['id' => $user->id,]);
                if ($student_folder = UserService::createStudentFolder($this->google_config, $schoology_user, $this->count, $this->queue_no, $this->pusher_channel)) {
                    Log::info('New student folder created', ['user_id' => $user->id,]);
                    $message = "Dear ".$this->student->name_first.",\r\n\r\nIt seems that your Drive Companion folder was deleted.\r\nThis folder has been re-created. Please do not delete this folder as it will be used to distribute and collect documents for class.";
                    UserService::sendMessage($user, "You deleted your Drive Companion folder", $message);
                    $user->folder_id = $student_folder->id;
                    $user->save();
                } else {
                    Log::error('Student folder could not be created', [
                        'user' => $this->student->uid,
                        'school' => $this->school_id,
                        'section' => $this->section_id,
                    ]);
                    $message="Could not create a folder for user ".$this->student->name_display;
                    event(new ReportError('user', $message, $this->count, $this->queue_no, $this->pusher_channel));
                    return;
                }
            } else {
                // Some other error occurred
                Log::error('Google Service Error Occurred', [
                    'user' => $this->student->uid,
                    'school' => $this->school_id,
                    'section' => $this->section_id,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]);
                $message="A Google Service Error Occurred for ".$user->name.". ".$e->getMessage();
                event(new ReportError('user', $message, $this->count, $this->queue_no, $this->pusher_channel));
                return;
            }
        }
        $search_files = true;
        try {
            $enrollment = Enrollment::withTrashed()->findOrFail($this->student->id);
            /*
             * Check if the enrollment with this id already exists for a different section
             * and permanently delete the enrollment if it does.
             * This part is needed as the same enrollment id is kept if students are moved from
             * one course to another.
             * */
            if (($enrollment->section_id !== $this->section_id) && $enrollment->trashed()){
                $enrollment->forceDelete();
                throw new ModelNotFoundException();
            }
        } catch (ModelNotFoundException $e) {
            $search_files = false;
            if ($enrollment_folder=UserService::createEnrollmentFolder($this->google_config, $user, $this->section_name, $this->section_id, $this->teacher_email, $this->students_folder_id, $this->count, $this->queue_no, $this->pusher_channel)) {
            // The Enrollment folder was created.
                // Create the enrollment.
                Log::info('Creating Enrollment: ', [
                    'id' => $this->student->id,
                    'section_id' => $this->section_id,
                    'user' => $user->name
                ]);
                Enrollment::create([
                    'id' => $this->student->id,
                    'section_id' => $this->section_id,
                    'user_id' => $user->id,
                    'folder_id' => $enrollment_folder->id, // Property of non-object
                    'admin' => 0,
                ]);
                $enrollment = Enrollment::find($this->student->id);
                $message = "Dear ".$this->student->name_first."\r\n\r\nA folder named ".$this->student->name_display." - ".$this->section_name." has been created in your Drive Companion folder in Google Drive. This folder will be used to share, collect and distribute documents for the class ".$this->section_name.". \r\n\r\nPlease do not delete or trash this folder.";
                UserService::sendMessage($user, "Drive Companion", $message);
            } else {
                // No enrollment was created.
                Log::error('Enrollment folder could not be created', [
                    'user' => $this->student->uid,
                    'school' => $this->school_id,
                    'section' => $this->section_id
                    ]);
                $message="Could not create a course folder for ".$this->student->name_display;
                event(new ReportError('user', $message, $this->count, $this->queue_no, $this->pusher_channel));
                return;
            }
        }
        if ($search_files) {
            // Check if the Enrollment Folder is trashed or Deleted
            try {
                $enrollment_folder=$service->files->get($enrollment->folder_id, array(
                    'fields' => 'id, name, trashed'
                ));
            } catch (Google_Auth_Exception $e) {
            // There was a problem authenticating the user.
                Log::error('Error authenticating user in Google drive', [
                    'message' => $e->getMessage(),
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);
                $message = "Could not authenticate user ".$user->name." in Google Drive using email address ".$user->email.
                    ". Please ensure that the email address stored in Schoology is the same as the user's primary email address on your Google Apps Domain.";
                event(new ReportError('user', $message, $this->count, $this->queue_no, $this->pusher_channel));
                return;
            } catch (Google_Service_Exception $e) {
                if ($e->getCode()==404) {
                    // The enrollment folder has been deleted.
                    Log::info('Enrollment folder was deleted', [
                        'user_id' => $user->id,
                        'section_id' => $this->section_id,
                    ]);
                    if ($enrollment_folder=UserService::createEnrollmentFolder($this->google_config, $user, $this->section_name, $this->section_id, $this->teacher_email, $this->students_folder_id, $this->count, $this->queue_no, $this->pusher_channel)) {
                        Log::info('New enrollment folder was created', [
                        'user_id' => $user->id,
                        'school' => $user->school_id,
                        'section_id' => $this->section_id,
                        ]);
                        $message = "Dear ".$this->student->name_first.",\r\n\r\nYour Drive Companion folder for ".$this->section_name." was re-created. Please do not delete this folder again.";
                        UserService::sendMessage($user, "You deleted your Drive Companion Course folder!", $message);
                        $enrollment->folder_id = $enrollment_folder->id;
                        $enrollment->save();
                    } else {
                        Log::error('Enrollment folder could not be created', [
                        'user_id' => $this->student->uid,
                        'school' => $this->school_id,
                        'section_id' => $this->section_id,
                        ]);
                        $message="A Course folder could not be created for ".$user->name;
                        event(new ReportError('user', $message, $this->count, $this->queue_no, $this->pusher_channel));
                        return;
                    }
                } else {
                    // Some other error occurred
                    Log::error('Google Service Error Occurred', [
                    'user' => $this->student->uid,
                    'school' => $this->school_id,
                    'section' => $this->section_id,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                    ]);
                    $message="A Google Service Error Occurred for ".$user->name.". ".$e->getMessage();
                    event(new ReportError('user', $message, $this->count, $this->queue_no, $this->pusher_channel));
                    return;
                }
            }
            if ($enrollment_folder->trashed) {
                // The folder has been trashed.
                // Check if the User folder is also trashed
                $user_folder=$service->files->get($user->folder_id, array(
                    'fields' => 'id, name, trashed'
                ));
                if ($user_folder->trashed) {
                    $fileMetadata = new \Google_Service_Drive_DriveFile(array(
                        'trashed' => false,
                    ));
                    $service->files->update($user_folder->id, $fileMetadata);
                    Log::info('User folder restored from trash', [
                        'user_id' => $user->id,
                        'school' => $user->school_id,
                    ]);
                    $message = "Dear ".$this->student->name_first.",\r\n\r\nYour Drive Companion folder has been restored from the Trash in Google Drive. \r\n Please do not trash this folder as it is used to distribute and collect documents for class.";
                    UserService::sendMessage($user, "Your Drive Companion folder was restored from the trash", $message);
                } else {
                    $message = "Dear ".$this->student->name_first."\r\n\r\nYour Drive Companion folder for ".$this->section_name." was restored from the Trash. Please do not trash or delete this folder as it will be used to distribute or collect documents for class.";
                    UserService::sendMessage($user, "Your Drive Companion course folder was restored from the trash", $message);
                    $enrollment_folder->trashed = false;
                    $fileMetadata = new \Google_Service_Drive_DriveFile(array(
                        'trashed' => false,
                    ));
                    $service->files->update($enrollment_folder->id, $fileMetadata);
                    Log::info('Enrollment folder restored from trash', [
                        'user_id' => $user->id,
                        'section_id' => $this->section_id,
                        'school' => $user->school_id,
                    ]);
                }
            }
            // Get the contents of the student folder.
            $tries = 0;
            do {
                $success = true;
                try {
                    $files = UserService::getFolderFiles($this->teacher_email, $enrollment->folder_id, $this->max_results);
                } catch (Exception $e) {
                    $success = false;
                    // Exponential Backoff if user rate limit is exceeded.
                    time_nanosleep(2^$tries, rand(0, 1000000000));
                    $tries++;
                    if ($tries > 6) {
                        $message = "Could not retrieve files for ".$user->name.". Please check the user folder. Please ensure that your Schoology email matches your Google Apps email address and that your Google Apps account is active.";
                        event(new ReportError('user', $message, $this->count, $this->queue_no, $this->pusher_channel));
                        Log::error('Error while retrieving student files', [
                            'message' => $e->getMessage(),
                            'user' => $this->student->uid,
                            'section_id' => $this->section_id,
                            'user' => $this->student->name_display,
                            'school' => $this->school_id,
                        ]);
                        return;
                    }
                }
            } while (!$success);

            $userFiles = array();
            foreach ($files as $file) {
                $userFiles[]=[
                    'iconLink' => $file->iconLink,
                    'name' => $file->name,
                    'webViewLink' => $file->webViewLink,
                    'modifiedTime' => $file->modifiedTime,
                    'newChanges' => !$file->lastModifyingUser->me,
                ];
            }
        } else {
            $userFiles = array();
        }

        // Trigger the event to push the data back to the user.
        event(new UserUpdated($user, $enrollment, $userFiles, $this->count, $this->pusher_channel, $this->queue_no));
    }
}
