<?php

namespace app\Http\Controllers;

use App\User;
use App\School;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GrooveController extends Controller
{
    public function index(Request $request)
    {
        $api_token = $request->get('api_token');
        if ($api_token != config('groove.token')) {
            abort('401');
        } else {
            $email = $request->get('email');
            $user = User::where('email', $email)->first();
            if ($user) {
                $school = School::find($user->school_id);
                return [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'picture' => $user->picture,
                    'folder_id' => $user->folder_id,
                    'school_id' => $school->id,
                    'school' => $school->name,
                    'address' => $school->address,
                    'country' => $school->country,
                    'city' => $school->city,
                    'state' => $school->state,
                    'postal_code' => $school->postal_code,
                    'google_api_configured' => $school->google_api_configured,
                    'freshbooks_id' => $school->freshbooks_id,
                    'credits' => $school->credits,
                ];
            } else {
                return null;
            }
        }
    }
}
