<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
 */

/*
 * Redirect traffic to the site root to the products section on the Engagenie website
 */
Route::get('/', function () {
    return redirect('https://engagenie.com/#focus');
});

Route::get('/terms', function () {
    return view('partials.terms');
});
Route::get('/privacy', function () {
    return view('partials.privacy');
});
Route::get('hostname', function () {
    return uniqid(gethostname(), true);
});
Route::get('app', [
    'as' => 'app',
    'uses' => 'IndexController@index',
]);
Route::group(['prefix' => 'app', 'middleware' => ['auth', 'checkSchoolSubscription']], function () {
    Route::get('teacher/{realm_id}', 'TeachersController@index');
    Route::get('student/{realm_id}', 'StudentsController@index');
});
Route::get('config', 'SchoolsController@show');
Route::group(['prefix' => 'config', 'middleware' => 'auth'], function () {
    Route::post('testgoogleauth', 'SchoolsController@testGoogleAuth');
    Route::get('current-sections', 'SchoolsController@currentSections');
    Route::get('schools/{school_id}/sections', 'SchoolsController@getSections');
    Route::post('schools/{school_id}/credits', 'SchoolsController@buyCredits');
    Route::get('schools/{school_id}/freshbooks', 'SchoolsController@getFreshbooksDetails');
    Route::post('schools/{school_id}/freshbooks', 'SchoolsController@setFreshbooksDetails');
    Route::get('schools/{school_id}/xero', 'SchoolsController@getXeroContact');
    Route::post('schools/{school_id}/xero', 'SchoolsController@setXeroContact');
    Route::post('schools/{school_id}/code', 'SchoolsController@setCode');
    Route::delete('schools/{school_id}/code', 'SchoolsController@deleteCode');
});

// Groove custom profiles
Route::get('groove/profiles', 'GrooveController@index');

// API
Route::group(['prefix' => 'api', 'middleware' => 'auth'], function () {
    Route::get('sections', 'SectionsController@index');
    Route::get('sections/{section_id}/users', 'UsersController@index');
    Route::post('sections', 'SectionsController@store');
    Route::put('sections/{id}', 'SectionsController@update');
    Route::get('sections/{section_id}/grading-groups', 'SectionsController@getGradingGroups');
    Route::post('assignments', 'AssignmentsController@store');
    Route::post('sections/{section_id}/assignments/{assignment_id}/submissions', 'AssignmentsController@submit');
    Route::get('assignments/{assignment_id}/files', 'AssignmentsController@getFiles');
    Route::get('sections/{section_id}/assignments', 'AssignmentsController@index');
    Route::get('sections/{section_id}/schoology-assignments', 'AssignmentsController@getSchoologyAssignments');
    Route::post('assignments/gettemplatefiles', 'AssignmentsController@getTemplateFiles');
    Route::get('students/{enrollment_id}/files', 'StudentsController@getFolderItems');
});

Route::get('health-check', 'IndexController@health_check');

Route::get('cookie-preload', 'IndexController@cookie_preload');
