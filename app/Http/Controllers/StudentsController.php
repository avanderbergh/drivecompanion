<?php

namespace App\Http\Controllers;

use App\AssignmentFile;
use App\Enrollment;
use App\Facades\UserService;
use App\User;
use JavaScript;
use App\Http\Requests;
use Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentsController extends Controller
{
    public function index($realm_id)
    {
        try {
            $uid = session('schoology')['uid'];
                $user = User::findOrFail($uid);
        } catch (ModelNotFoundException $e) {
        // The user was not found, the student loaded  the App before the teacher set up any section
            return view('errors.custom.display')
            ->with('title', "Your user was not found.")
            ->with('message', "Your course instructor needs to set up Drive Companion first.");
        }
        if (!$enrollment = $user->enrollments()->where('section_id', $realm_id)->first()) {
            // The enrollment does not exist. The student loaded the App before the teacher set up the section.
            $enrollments = $user->enrollments()->get();
            foreach ($enrollments as $k => $enrollment){
                $enrollments[$k]->section->first();
            }
            return view('student.select_section')
            ->with('enrollments', $enrollments);
        }
        JavaScript::put([
            'user' => $user,
            'enrollment' => $enrollment,
        ]);

        return view('student.dashboard')
            ->with('user', $user)
            ->with('enrollment', $enrollment);
    }

    public function getFolderItems($enrollment_id)
    {
        $enrollment = Enrollment::find($enrollment_id);
        $folder_id = $enrollment->folder_id;
        $email = $enrollment->user->email;
        $files = UserService::getFolderFiles($email, $folder_id, 100);
        foreach ($files as $k => $file) {
            $assignment_file = AssignmentFile::where('file_id', $file->id)
                ->where('enrollment_id', $enrollment_id)
                ->first();
            if ($assignment_file) {
                $assignment=$assignment_file->assignment;
                if ($assignment->schoology_assignment_id) {
                    $files[$k]->assignment=$assignment->schoology_assignment_id;
                }
            }
        }
        return $files;
    }
}
