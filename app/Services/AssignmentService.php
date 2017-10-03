<?php
namespace App\Services;

use App\Assignment;
use App\Enrollment;
use App\AssignmentFile;
use Google_Service_Exception;
use PulkitJalan\Google\Client;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_Permission;
use Illuminate\Support\Facades\Auth;
use Google_Service_Drive_ParentReference;

/**
 * Class AssignmentService
 * @package App\Services
 */
class AssignmentService
{
    /**
     * @param $section_folder_id
     * @param $assignment_name
     * @param $email
     * @param $config
     * @throws \PulkitJalan\Google\Exceptions\UnknownServiceException
     */
    public function createAssignmentFolder($section_folder_id, $assignment_name, $email, $config)
    {
        $client = new Client($config, $email);
        $service = $client->make('drive');
        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $assignment_name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => array($section_folder_id)
        ));
        $folder = $service->files->create($fileMetadata, array(
            'fields' => 'id'));
        return $folder->id;
    }

    public function copyFile($enrollments, $assignment_id, $file_id, $folder_id, $filename, $email, $config)
    {
        $client = new Client($config, $email);
        $service = $client->make('drive');

        $copiedFile= new \Google_Service_Drive_DriveFile();
        $copiedFile->setName($filename);
        $copiedFile = $service->files->copy($file_id, $copiedFile);

        foreach ($enrollments as $enrollment) {
            $newPermission = new \Google_Service_Drive_Permission();
            $newPermission->setEmailAddress($enrollment->user->email);
            $newPermission->setType("user");
            $newPermission->setRole("writer");

            // Use incremental backoff.
            $tries=0;
            do {
                $success = true;
                try {
                    $service->permissions->create($copiedFile->id, $newPermission, ['sendNotificationEmail'=>false]);
                } catch (\Google_Service_Exception $e){
                    $success = false;
                    $tries++;
                    time_nanosleep(2^$tries, rand(0, 1000000000));
                    if ($tries > 6){
                        throw new Google_Service_Exception("Change permission of copied file.");
                        return;
                    }
                }
            } while (!$success);
        }
        $emptyFileMetadata = new Google_Service_Drive_DriveFile();
        $parents = array($folder_id);
        foreach ($enrollments as $enrollment) {
            $parents[] = $enrollment->folder_id;
        }
        // Use incremental backoff.
        $tries=0;
        do {
            $success = true;
            try {
                $copiedFile = $service->files->update($copiedFile->id, $emptyFileMetadata, array(
                    'addParents' => join(",",$parents),
                    'fields' => 'id, parents'));
            } catch (\Google_Service_Exception $e){
                $success = false;
                $tries++;
                time_nanosleep(2^$tries, rand(0, 1000000000));
                if ($tries > 6){
                    throw new Google_Service_Exception("Setting parents shared file.");
                    return;
                }
            }
        } while (!$success);
        foreach ($enrollments as $enrollment) {
            AssignmentFile::create([
                'enrollment_id'=>$enrollment->id,
                'assignment_id'=>$assignment_id,
                'file_id'=>$copiedFile->id,
                'name'=>$filename,
                'link'=>$copiedFile->webViewLink,
            ]);
        }
        return "Done";
    }
}
