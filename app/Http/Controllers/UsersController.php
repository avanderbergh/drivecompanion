<?php

namespace App\Http\Controllers;

use App\Events\UserUpdated;
use App\Facades\UserService;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Section;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($section_id)
    {
        $users =  Section::find($section_id)->users;
        foreach ($users as $user) {
            $user->enrollments->where('section_id', $section_id)->first();
            $user->files=UserService::getFolderFiles($user->email, $user->enrollments[0]->folder_id);
        }
        return $users;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
