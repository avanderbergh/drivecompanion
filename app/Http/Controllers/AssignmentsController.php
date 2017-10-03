<?php

namespace App\Http\Controllers;

use Log;
use Exception;
use App\Section;
use App\Assignment;
use App\Enrollment;
use App\Http\Requests;
use App\Jobs\CopyFile;
use Google_Http_Request;
use App\Jobs\CopyFileGroup;
use App\Facades\UserService;
use Illuminate\Http\Request;
use Google_Service_Exception;
use PulkitJalan\Google\Client;
use App\Facades\AssignmentService;
use Google_Service_Drive_DriveFile;
use Illuminate\Support\Facades\Storage;
use Avanderbergh\Schoology\Facades\Schoology;

class AssignmentsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $config=config('google');
        $assignment_folder_id=AssignmentService::createAssignmentFolder($request->assignments_folder_id, $request->assignment_title, $request->teacher_email, $config);
        $assignment = Assignment::create([
            'section_id'=>$request->section_id,
            'name'=>$request->assignment_title,
            'template_id'=>$request->template_id,
            'folder_id'=>$assignment_folder_id,
            'schoology_assignment_id'=>$request->schoology_assignment_id,
        ]);
        $pusher_channel = $request->pusher_channel;
        $section=Section::find($request->section_id);
        $enrollments = Enrollment::whereIn('id', $request->checked_names)->get();
        if ($request->assignment_type==1) {
            // Make a copy for each Student.
            $count = count($enrollments);
            $max = config('queue.number');
            $queue_no = rand(1, $max);
            foreach ($enrollments as $enrollment) {
                $file_title = $assignment->name . " - " . $enrollment->user->name;
                $job = (new CopyFile(
                    $enrollment->section_id,
                    $assignment->template_id,
                    $enrollment->id,
                    $enrollment->folder_id,
                    $assignment->id,
                    $assignment->folder_id,
                    $file_title,
                    $enrollment->user->email,
                    $request->teacher_email,
                    $config,
                    $count,
                    $pusher_channel,
                    $queue_no
                ))->onQueue(config('queue.name').'_'.$queue_no);
                $this->dispatch($job);
                $queue_no++;
                if ($queue_no>$max) {
                    $queue_no=1;
                }
            }
            Log::info('Creating Individual Assignment: ', [
                'id' => $assignment->id,
                'name' => $assignment->name,
                'section_id' => $section->id,
            ]);
        } elseif ($request->assignment_type==2) {
            // Make a copy for each Grading Group
            Schoology::authorize();
            $grading_groups = $request->checked_groups;
            $count = count($grading_groups);
            $max = config('queue.number');
            $queue_no = rand(1, $max);
            foreach ($grading_groups as $grading_group) {
                $enrollments = [];
                $result = Schoology::apiResult("sections/".$section->id."/grading_groups/".$grading_group);
                // Fetch the students from Schoology
                foreach ($result->members as $member) {
                    $enrollments[] = Enrollment::findOrFail($member);
                    // Fetch enrollments from Database
                }
                $job = (new CopyFileGroup(
                    $enrollments,
                    $assignment,
                    $result->title,
                    $request->teacher_email,
                    $result->section_id,
                    $config,
                    $count,
                    $queue_no,
                    $pusher_channel
                ))->onQueue(config('queue.name').'_'.$queue_no);
                $this->dispatch($job);
                $queue_no++;
                if ($queue_no>$max) {
                    $queue_no=1;
                }
            }
            Log::info('Creating Group Assignment: ', [
                'section_id' => $section->id,
                'name' => $assignment->name,
            ]);
        } elseif ($request->assignment_type==3) {
            // Let all students edit the same file.
            Log::info('Creating Shared Assignment: ', [
                'section_id' => $section->id,
                'name' => $assignment->name,
            ]);
            $assignment_file = AssignmentService::copyFile($section->enrollments, $assignment->id, $assignment->template_id, $assignment->folder_id, $assignment->name." - All Students", $request->teacher_email, $config);
            return $assignment_file;
        } else {
            // An unknown value for $request->assignment_type was entered.
            return "This assignment type does not exist";
        }
    }
    public function getTemplateFiles(Request $request)
    {
        $accepted_mime_types = [
            "application/vnd.google-apps.document",
            "application/vnd.google-apps.drawing",
            "application/vnd.google-apps.presentation",
            "application/vnd.google-apps.spreadsheet",
        ];
        $return=[];
        $template_files=UserService::getFolderFiles($request->email, $request->templates_folder_id, $request->max_results);
        foreach ($template_files as $template_file) {
            if (in_array($template_file->mimeType, $accepted_mime_types)){
                $return[]=[
                    'text'=>$template_file->name,
                    'value'=>$template_file->id
                ];
            }
        }
        $template_files=UserService::getFolderFiles($request->email, $request->user_folder_id, $request->max_results);
        foreach ($template_files as $template_file) {
            if (in_array($template_file->mimeType, $accepted_mime_types)){
                $return[]=[
                    'text'=>$template_file->name,
                    'value'=>$template_file->id
                ];
            }
        }
        return $return;
    }
    public function index($section_id)
    {
        $section = Section::findOrFail($section_id);
        return $section->assignments()->get();
    }
    public function getFiles($assignment_id)
    {
        // Use eager loading to return the related models.
        return Assignment::findOrFail($assignment_id)
            ->files()->with('enrollment.user')->get();
    }

    public function getSchoologyAssignments($section_id)
    {
        Schoology::authorize();
        $result = Schoology::apiResult('sections/'.$section_id.'/assignments');
        $items = array();
        /*
         * The results are paginated so make more requests of the next link is present
         */
        while (property_exists($result->links, 'next')) {
            $items = array_merge($items, $result->assignment);
            $result = Schoology::apiResult('sections/'.$section_id.'/assignments'.substr($result->links->next, strpos($result->links->next, '?')));
        }
        $items = array_merge($items, $result->assignment);
        $assignments = array();
        foreach ($items as $item) {
            if ($item->type == "assignment" && $item->allow_dropbox && $item->published) {
                $assignments[]=$item;
            }
        }
        return $assignments;

    }
    public function submit(Request $request, $section_id, $assignment_id)
    {
        $client = new Client(config('google'), $request->email);
        $service = $client->make('drive');
        // Incremental back-off
        $tries = 0;
        do {
            $success = true;
            try {
                $file = $service->files->export($request->id, 'application/pdf', array(
                    'alt' => 'media' ));
            } catch (Google_Service_Exception $e) {
                $success = false;
                time_nanosleep(2^$tries, rand(0, 1000000000));
                $tries++;
                if ($tries > 6) {
                    // The file could not be moved into the student's folder
                    Log::error("Could not export pdf file for submission", ['message' => $e->getMessage(),'file' => $this->file_name, 'student'=>$this->student_email]);
                    return 'fail';
                }
            }
        } while (!$success);
        $size = $file->getBody()->getSize();
        if ($size > 0) {
            $content = $file->getBody()->read($size);

            if ($content) {
                $file_name = $request->id.'.pdf';
                Storage::put($file_name, $content);

                Schoology::authorize();
                $file_path = storage_path().'/app/'.$file_name;
                $file_id = Schoology::apiFileUpload($file_path);

                try {
                    Schoology::apiResult(
                        'sections/'.$section_id.'/submissions/'.$assignment_id.'/file',
                        'POST',
                        [
                            'file-attachment'=>
                                [
                                    'id' => [$file_id]
                                ]
                        ]
                    );

                } catch (Exception $e){
                    Log::warning("Could not upload file to Schoology: ".$e->getMessage());
                    return 'fail';
                } finally {
                    Storage::delete($file_name);
                }
                return 'success';
            }
        } else {
            //The file size was 0!
            Log::warning("Tried to open an empty file, failed!");
            return 'fail';
        }
    }
}
