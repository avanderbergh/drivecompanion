<?php namespace App\Services;

use Log;
use App\User;
use Exception;
use App\Events\ReportError;
use Google_Service_Exception;
use PulkitJalan\Google\Client;
use Google_Service_Drive_DriveFile;
use Illuminate\Support\Facades\Auth;
use Avanderbergh\Schoology\Facades\Schoology;

class UserService
{
    /**
     * @param $section_id
     * @return bool
     */
    public function isTeacher($section_id)
    {
        try {
            Schoology::authorize();
        } catch (Exception $e) {
            return false;
        }
        $schoologySection = Schoology::apiResult('sections/'.$section_id);
        if ($schoologySection->admin) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function creates a new user from the Schoology Api Information
     * and creates a folder from them in their Google Drive.
     * @return User
     * @throws Exception
     */
    public function createUserAndFolder()
    {
        Schoology::authorize();
        $schoology_user = Schoology::apiResult('users/me');
        if (!filter_var($schoology_user->primary_email, FILTER_VALIDATE_EMAIL)) {
        // The user doesn't have a valid email address assigned in Schoology
            Log::error('could not create user. email'.$schoology_user->primary_email);
            throw new Exception("The user does not have a valid email address");
        }
        /*
         * Get the user from the Database or create if they don't exist.
         */
        $user= User::firstOrNew(['id'=>Auth::user()->id]);
        $user->name = $schoology_user->name_display;
        $user->email = $schoology_user->primary_email;
        $user->picture = $schoology_user->picture_url;
        $user->school_id = $schoology_user->school_id;
        $user->name_first = $schoology_user->name_first;
        $user->name_last = $schoology_user->name_last;
        $user->name_first_preferred = $schoology_user->name_first_preferred;
        /*
         * Check if the user has a Google Folder ID saved
         * and create one if it doesn't exist.
         */
        if (!$user->folder_id) {
            $client = new Client(config('google'), $user->email);
            $service = $client->make('drive');

            $fileMetadata = new Google_Service_Drive_DriveFile(array(
                'name' => 'Drive Companion',
                'mimeType' => 'application/vnd.google-apps.folder',
                'folderColorRgb' => '#4986e7'));

            $folder = $service->files->create($fileMetadata, array(
                'fields' => 'id'));

            $user->folder_id = $folder->id;
        }
        $user->save();
        return $user;
    }

    function getFolderFiles($user_email, $folder_id, $maxResults = 15)
    {
        $client = new Client(config('google'), $user_email);
        $service = $client->make('drive');
        $parameters = [
            'q' => "'".$folder_id."' in parents",
            'pageSize' => $maxResults,
            'orderBy' => 'modifiedTime desc',
            'fields' => 'files(id,name,mimeType,iconLink,webViewLink,modifiedTime,lastModifyingUser/me)'
        ];
        $files = $service->files->listFiles($parameters);
        return $files->getFiles();
    }

    function createStudentFolder($google_config, $schoology_user, $count, $queue_no, $pusher_channel)
    {
        $client = new Client($google_config, $schoology_user->primary_email);
        $service = $client->make('drive');

        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => 'Drive Companion',
            'mimeType' => 'application/vnd.google-apps.folder',
            'folderColorRgb' => '#4986e7'));
        // Catch a Google_Service_Exception in case the user has an invalid email address.
        try {
            $folder = $service->files->create($fileMetadata, array(
                'fields' => 'id'));
        } catch (Google_Service_Exception $e) {
            $message = "Could not create user folder for ".$schoology_user->name_display." using email address ".$schoology_user->primary_email.
                ". Please ensure that the email address stored in Schoology is the same as the user's primary email address on your Google Apps Domain.";
            event(new ReportError('user', $message, $count, $queue_no, $pusher_channel));
            Log::error('Error while creating User', [
                'message' => $e->getMessage(),
                'id' => $schoology_user->uid,
                'name' => $schoology_user->name_display,
                'email' => $schoology_user->primary_email
            ]);
            return;
        }
        return $folder;
    }

    function createEnrollmentFolder($google_config, $user, $section_name, $section_id, $teacher_email, $students_folder_id, $count, $queue_no, $pusher_channel)
    {
        // Create a student folder of this class.
        $client = new Client($google_config, $user->email);
        $service = $client->make('drive');

        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $user->name.' - '.$section_name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'folderColorRgb' => '#4986e7',
            'parents' => array($user->folder_id)));
        try {
            $folder = $service->files->create($fileMetadata, array(
                'fields' => 'id'));
        } catch (Google_Service_Exception $e) {
            $message = "Could not create course folder for ".$user->name." using email address ".$user->email.
                ". Please ensure that the email address stored in Schoology is the same as the user's primary email address on your Google Apps Domain.";
            event(new ReportError('user', $message, $count, $queue_no, $pusher_channel));
            Log::error('Error Creating Enrollment Folder', [
                'message' => $e->getMessage(),
                'section_id' => $section_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);
            return;
        }

        //Share the folder with the currently logged in teacher.
        $newPermission = new \Google_Service_Drive_Permission();
        $newPermission->setEmailAddress($teacher_email);
        $newPermission->setType("user");
        $newPermission->setRole("writer");
        try {
            $service->permissions->create($folder->id, $newPermission, ['sendNotificationEmail'=>false]);
        } catch (Exception $e) {
        // Could not share the student folder with the teacher
            $message = "Could not give you edit rights to the folder for ".$user->name;
            event(new ReportError('user', $message, $count, $queue_no, $pusher_channel));
            Log::error('Error while setting folder permissions', [
                'message' => $e->getMessage(),
                'section_id' => $section_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);
            return;
        }

        // Now connect as the teacher to the assign the parent of the folder.
        $client = new Client($google_config, $teacher_email);
        $service = $client->make('drive');
        $emptyFileMetadata = new Google_Service_Drive_DriveFile();
        try {
            $folder = $service->files->update($folder->id, $emptyFileMetadata, array(
               'addParents' => $students_folder_id,
                'fields' => 'id, parents, trashed'
            ));
        } catch (Exception $e) {
            $message = "Could not move the student folder for ".$user->name." into the class folder";
            event(new ReportError('user', $message, $count, $queue_no, $pusher_channel));
            Log::error('Error while setting folder parents', [
                'message' => $e->getMessage(),
                'section_id' => $section_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);
            return;
        }
        return $folder;
    }

    public function checkFolder($folder_id, $service)
    {
        $folder=null;
        $error=null;
        try {
            $folder = $service->files->get($folder_id);
        } catch (Exception $error) {

        }
        // If the folder exists, check if it's in the trash and restore if it is.
        if ($folder) {
            if ($folder->trashed) {
                $service->files->untrash($folder_id);
            }
        }
        return [
            'folder' => $folder,
            'error' => $error,
        ];
    }

    public function sendMessage(User $user, $subject, $message)
    {
        $body=[
            "subject" => $subject,
            "message" => $message,
            "recipient_ids" => $user->id
        ];
        try {
            Schoology::api("messages", "POST", $body);
        } catch (Exception $e) {
        // The message could not be sent, it's possible the school has disabled private messaging.
            Log::notice("Could not send Schoology Message to user: ".$user);
        }
    }
}
