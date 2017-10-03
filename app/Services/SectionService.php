<?php
namespace App\Services;

use App\User;
use Exception;
use App\Section;
use PulkitJalan\Google\Client;
use Google_Service_Drive_DriveFile;
use Illuminate\Support\Facades\Auth;
use Google_Service_Drive_ParentReference;

class SectionService
{
    public function createSectionAndFolder($section_id, $section_title, $parent_folder_id, $school_id)
    {
        $section=Section::firstOrNew(['id'=>$section_id]);
        $section->name = $section_title;
        $section->school_id = $school_id;
        $section->owner_id=Auth::user()->id;
        $config=config('google');
        // Create a folder for the class if it does not exist?

        // Set up the Google API Client
        $client = new Client($config, Auth::user()->email);
        $service = $client->make('drive');

        // Create the Section Folder
        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $section->name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => array($parent_folder_id)
        ));
        $folder = $service->files->create($fileMetadata, array(
            'fields' => 'id'));
        $section->folder_id = $folder->id;

        // Create the Section Students Folder
        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => 'Members: '.$section->name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => array($section->folder_id)
        ));
        $folder = $service->files->create($fileMetadata, array(
            'fields' => 'id'));
        $section->students_folder_id=$folder->id;

        // Create the Assignments Folder
        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => 'Assignments: '.$section->name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => array($section->folder_id)
        ));
        $folder = $service->files->create($fileMetadata, array(
            'fields' => 'id'));
        $section->assignments_folder_id=$folder->id;

        // Create the Assignment Templates Folder
        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => 'Templates: '.$section->name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => array($section->folder_id)
        ));
        $folder = $service->files->create($fileMetadata, array(
            'fields' => 'id'));
        $section->templates_folder_id=$folder->id;

        $section->save();
        return $section;
    }

    public function shareSectionFolder(Section $section, User $user)
    {
        $config = config('google');
        // Connect as the section owner
        $owner_email = $section->owner()->first()->email;
        $client = new Client($config, $owner_email);
        $service = $client->make('drive');

        // Create a give the current user write permission to the section folder.
        $newPermission = new \Google_Service_Drive_Permission();
        $newPermission->setEmailAddress($user->email);
        $newPermission->setType("user");
        $newPermission->setRole("writer");
        $service->permissions->create($section->folder_id, $newPermission, ['sendNotificationEmail'=>false]);

        // Connect at the current user and move the section folder to their folder.
        $client = new Client($config, $user->email);
        $service = $client->make('drive');
        $fileMetadata = new \Google_Service_Drive_DriveFile();
        $service->files->update($section->folder_id, $fileMetadata, array(
            'fields' => 'id, parents',
            'addParents' => $user->folder_id,
        ));
    }
}
