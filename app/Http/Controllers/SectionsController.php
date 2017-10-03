<?php

namespace App\Http\Controllers;

use Log;
use App\School;
use App\Section;
use Carbon\Carbon;
use App\Enrollment;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Jobs\ProcessEnrollment;
use App\Facades\SectionService;
use Avanderbergh\Schoology\Facades\Schoology;

class SectionsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $school = School::findOrFail($request->school_id);
        if ($school->code){
            if ($school->code != $request->code){
                return view('errors.custom.display')
                    ->with('title', 'Incorrect School Code')
                    ->with('message', 'Your Schoology administrator requires you to enter a school code. Please ask your Schoology Administrator for your school code and try again');
            }
        }
        Log::info('Creating Section: ', [
            'id' => $request->section_id,
            'title' => $request->section_title,
            'school_id' => $request->school_id
        ]);
        try {
            SectionService::createSectionAndFolder(
                $request->section_id,
                $request->section_title,
                $request->folder_id,
                $request->school_id
            );
        } catch (\Exception $e) {
            return view('errors.custom.display')
                ->with('title', 'Could not create the class folder in Google Drive')
                ->with('message', 'Please check that the email address for your account stored in Schoology matches your Google Apps email and that your Google Apps account is active then reload Drive Companion.');
        }
        $school->credits -= 1;
        $school->save();
        return redirect(url('/app/teacher/'.$request->section_id, [], true));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $section_id = $id;
        $school_id = $request->school_id;
        $uid = $request->uid;
        $students_folder_id = $request->students_folder_id;
        $teacher_email = $request->teacher_email;
        $section_name = $request->section_name;
        $max_results = $request->max_results;
        $pusher_channel = $request->pusher_channel;
        Schoology::authorize();
        $google_config=config('google');
        /*
         * Get the enrollments for the current class.
         */
        $result = Schoology::apiResult('section/' . $section_id . '/enrollments');
        $users = array();
        /*
         * The results are paginated, so make more requests until all is done.
         */
        while (property_exists($result->links, 'next')) {
            $users = array_merge($users, $result->enrollment);
            $result = Schoology::apiResult('section/' . $section_id . '/enrollments' . substr($result->links->next, strpos($result->links->next, '?')));
        }
        $users = array_merge($users, $result->enrollment);
        $students = array();
        foreach ($users as $user) {
            if (!$user->admin && $user->status=='1') {
                $students[]=$user;
            }
        }
        // Remove un-enrolled students from the class.
        $section = Section::find($section_id);
        foreach ($section->enrollments as $enrollment) {
            $found = false;
            foreach ($students as $student) {
                if ($student->id == $enrollment->id) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $enrollment->delete();
            }
        }
        // Restore re-enrolled students
        $removed_enrollments = Enrollment::onlyTrashed()->where('section_id', $section_id)->get();
        foreach ($removed_enrollments as $removed_enrollment) {
            foreach ($students as $student) {
                if ($student->id == $removed_enrollment->id) {
                    $removed_enrollment->restore();
                }
            }
        }
        /*
         * Go through the class array and create enrollments if they don't exist.
         */
        $max = config('queue.number');
        $queue_no=rand(1, $max);
        $count = count($students);
        foreach ($students as $student) {
            $job = (new ProcessEnrollment($uid, $student, $section_id, $school_id, $students_folder_id, $teacher_email, $section_name, $google_config, $count, $max_results, $pusher_channel, $queue_no))
                ->onQueue(config('queue.name').'_'.$queue_no);
            $this->dispatch($job);
            $queue_no++;
            if ($queue_no>$max) {
                $queue_no=1;
            }
        }
        $section->last_accessed_at = Carbon::now();
        $section->save();
    }

    public function getGradingGroups($section_id)
    {
        Schoology::authorize();
        $grading_groups = Schoology::apiResult("sections/".$section_id."/grading_groups");
        return $grading_groups->grading_groups;
    }
}
