<?php

namespace App\Http\Controllers;

use Log;
use Session;
use Exception;
use JavaScript;
use App\School;
use App\Section;
use App\Http\Requests;
use App\Facades\UserService;
use PulkitJalan\Google\Client;
use App\Facades\SectionService;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_ParentReference;
use Avanderbergh\Schoology\Facades\Schoology;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TeachersController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param $realm_id
     * @return Response
     */
    public function index($realm_id)
    {
        try {
            $user = UserService::createUserAndFolder();
        } catch (Exception $e) {
            return view('errors.custom.display')
                ->with('title', 'Could not create your Drive Companion Folder')
                ->with('message', 'Please ensure that your email address in Schoology matches your Google Apps Email address and that your Google Apps account is active. Then reload Drive Companion');
        }
        try {
            $section = Section::findOrFail($realm_id);
        } catch (ModelNotFoundException $e) {
            $school_id=session('schoology')['school_nid'];
            $school=School::findOrFail($school_id);
            if ($school->credits < 1) {
                return view('teacher.nocredits');
            }
            $section=Schoology::apiResult('sections/'.$realm_id);
            return view('teacher.createsection')
                ->with('section', $section)
                ->with('user', $user)
                ->with('school', $school);
        }
        if ($section->owner_id != $user->id) {
            SectionService::shareSectionFolder($section, $user);
        } else {
            // The current user is the owner of the course, check if the folders exist.
            $client = new Client(config('google'), $user->email);
            $service = $client->make('drive');
            // Get the User Folder.
            $user_folder=UserService::checkFolder($user->folder_id, $service);
            if ($user_folder['error']) {
                if (is_a($user_folder['error'], 'Google_Service_Exception') && $user_folder['error']->getCode()==404) {
                    $fileMetadata= new Google_Service_Drive_DriveFile(array(
                        'name' => 'Drive Companion',
                        'mimeType' => 'application/vnd.google-apps.folder',
                        'folderColorRgb' => '#4986e7',
                    ));
                    $folder = $service->files->create($fileMetadata, array(
                        'fields' => 'id'
                    ));
                    $user->folder_id = $folder->id;
                    $user->save();
                    Log::info('Restored User Folder', [
                        'user' => $user->toJson(),
                        'section' => $section->toJson(),
                    ]);
                } else if (is_a($user_folder['error'], 'Google_Auth_Exception') && $user_folder['error']->getCode()==400) {
                    Log::warning('Could not Authenticate', [
                    'user' => $user->toJson(),
                    'section' => $section->toJson(),
                    'error' => $user_folder['error'],
                    ]);
                    return view('errors.custom.display')
                    ->with('title', 'Could not connect to Google Drive using email '.$user->email)
                    ->with('message', 'Please check that this email matches your Google Apps email and that your Google Apps Account is active.');
                } else {
                    Log::error('Unknown error while fetching user folder', [
                    'user' => $user->toJson(),
                    'section' => $section->toJson(),
                    'error' => $user_folder['error'],
                    ]);
                    return view('errors.custom.display')
                    ->with('title', 'An Error Occurred')
                    ->with('message', $user_folder['error']->getMessage());
                }
            }
            // Check if the section folder exists.
            $section_folder=UserService::checkFolder($section->folder_id, $service);
            if ($section_folder['error']) {
                if (is_a($section_folder['error'], 'Google_Service_Exception') && $section_folder['error']->getCode()==404) {
                    $fileMetadata= new Google_Service_Drive_DriveFile(array(
                        'name' => $section->name,
                        'mimeType' => 'application/vnd.google-apps.folder',
                        'folderColorRgb' => '#4986e7',
                        'parents' => array($user->folder_id),
                    ));
                    $folder = $service->files->create($fileMetadata, array(
                        'fields' => 'id'
                    ));
                    $section->folder_id=$folder->id;
                    $section->save();
                    Log::info('Restored Section Folder', [
                        'user' => $user->toJson(),
                        'section' => $section->toJson(),
                    ]);
                } else if (is_a($section_folder['error'], 'Google_Auth_Exception') && $user_folder['error']->getCode()==400) {
                    Log::warning('Could not Authenticate', [
                    'user' => $user->toJson(),
                    'section' => $section->toJson(),
                    'error' => $section_folder['error'],
                    ]);
                    return view('errors.custom.display')
                    ->with('title', 'Could not connect to Google Drive using email '.$user->email)
                    ->with('message', 'Please check that this email matches your Google Apps email and that your Google Apps Account is active.');
                } else {
                    Log::error('Unknown error while fetching section folder', [
                    'user' => $user->toJson(),
                    'section' => $section->toJson(),
                    'error' => $section_folder['error'],
                    ]);
                    return view('errors.custom.display')
                    ->with('title', 'An Error Occurred')
                    ->with('message', $section_folder['error']->getMessage());
                }
            }
            // Check if the Students folder folder exists
            $students_folder=UserService::checkFolder($section->students_folder_id, $service);
            if ($students_folder['error']) {
                if (is_a($students_folder['error'], 'Google_Service_Exception') && $students_folder['error']->getCode()==404) {
                    $fileMetadata= new Google_Service_Drive_DriveFile(array(
                        'name' => 'Student Folders: '.$section->name,
                        'mimeType' => 'application/vnd.google-apps.folder',
                        'folderColorRgb' => '#4986e7',
                        'parents' => array($section->folder_id),
                    ));
                    $folder = $service->files->create($fileMetadata, array(
                        'fields' => 'id'
                    ));
                    $section->students_folder_id=$folder->id;
                    $section->save();
                    Log::info('Restored Students Folder', [
                        'user' => $user->toJson(),
                        'section' => $section->toJson(),
                    ]);
                } else if (is_a($students_folder['error'], 'Google_Auth_Exception') && $user_folder['error']->getCode()==400) {
                    Log::warning('Could not Authenticate', [
                    'user' => $user->toJson(),
                    'section' => $section->toJson(),
                    'error' => $students_folder['error'],
                    ]);
                    return view('errors.custom.display')
                    ->with('title', 'Could not connect to Google Drive using email '.$user->email)
                    ->with('message', 'Please check that this email matches your Google Apps email and that your Google Apps Account is active.');
                } else {
                    Log::error('Unknown error while fetching students folder', [
                    'user' => $user->toJson(),
                    'section' => $section->toJson(),
                    'error' => $students_folder['error'],
                    ]);
                    return view('errors.custom.display')
                    ->with('title', 'An Error Occurred')
                    ->with('message', $students_folder['error']->getMessage());
                }
            }
            // Check if the Assignments Folder Exists
            $assignments_folder=UserService::checkFolder($section->assignments_folder_id, $service);
            if ($assignments_folder['error']) {
                if (is_a($assignments_folder['error'], 'Google_Service_Exception') && $assignments_folder['error']->getCode()==404) {
                    $fileMetadata= new Google_Service_Drive_DriveFile(array(
                        'name' => 'Assignments: '.$section->name,
                        'mimeType' => 'application/vnd.google-apps.folder',
                        'folderColorRgb' => '#4986e7',
                        'parents' => array($section->folder_id),
                    ));
                    $folder = $service->files->create($fileMetadata, array(
                        'fields' => 'id'
                    ));
                    $section->assignments_folder_id=$folder->id;
                    $section->save();
                    Log::info('Restored Assignments Folder', [
                       'user' => $user->toJson(),
                       'section' => $section->toJson(),
                    ]);
                } else if (is_a($assignments_folder['error'], 'Google_Auth_Exception') && $user_folder['error']->getCode()==400) {
                    Log::warning('Could not Authenticate', [
                    'user' => $user->toJson(),
                    'section' => $section->toJson(),
                    'error' => $assignments_folder['error'],
                    ]);
                    return view('errors.custom.display')
                    ->with('title', 'Could not connect to Google Drive using email '.$user->email)
                    ->with('message', 'Please check that this email matches your Google Apps email and that your Google Apps Account is active.');
                } else {
                    Log::error('Unknown error while fetching assignments folder', [
                    'user' => $user->toJson(),
                    'section' => $section->toJson(),
                    'error' => $assignments_folder['error'],
                    ]);
                    return view('errors.custom.display')
                    ->with('title', 'An Error Occurred')
                    ->with('message', $assignments_folder['error']->getMessage());
                }
            }
            // Check if the Assignment Templates Folder exists
            $templates_folder=UserService::checkFolder($section->templates_folder_id, $service);
            if ($templates_folder['error']) {
                if (is_a($templates_folder['error'], 'Google_Service_Exception') && $templates_folder['error']->getCode()==404) {
                    $fileMetadata= new Google_Service_Drive_DriveFile(array(
                        'name' => 'Templates: '.$section->name,
                        'mimeType' => 'application/vnd.google-apps.folder',
                        'folderColorRgb' => '#4986e7',
                        'parents' => array($section->folder_id),
                    ));
                    $folder = $service->files->create($fileMetadata, array(
                        'fields' => 'id'
                    ));
                    $section->templates_folder_id=$folder->id;
                    $section->save();
                    Log::info('Restored Templates Folder', [
                        'user' => $user->toJson(),
                        'section' => $section->toJson(),
                    ]);
                } else if (is_a($templates_folder['error'], 'Google_Auth_Exception') && $user_folder['error']->getCode()==400) {
                    Log::warning('Could not Authenticate', [
                    'user' => $user->toJson(),
                    'section' => $section->toJson(),
                    'error' => $templates_folder['error'],
                    ]);
                    return view('errors.custom.display')
                    ->with('title', 'Could not connect to Google Drive using email '.$user->email)
                    ->with('message', 'Please check that this email matches your Google Apps email and that your Google Apps Account is active.');
                } else {
                    Log::error('Unknown error while fetching templates folder', [
                    'user' => $user->toJson(),
                    'section' => $section->toJson(),
                    'error' => $templates_folder['error'],
                    ]);
                    return view('errors.custom.display')
                    ->with('title', 'An Error Occurred')
                    ->with('message', $templates_folder['error']->getMessage());
                }
            }
        }
        JavaScript::put([
            'section_id' => $realm_id,
            'user' => $user,
            'section' => $section,
            'PUSHER_KEY' => config('pusher.key'),
            'SESSION_ID' => Session::getId(),
        ]);
        Log::info('Loading dashboard', ['id' => $user->id, 'name' => $user->name]);
        return view('teacher.dashboard')
            ->with('section', $section)
            ->with('user', $user);
    }
}
