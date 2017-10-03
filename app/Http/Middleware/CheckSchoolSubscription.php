<?php

namespace App\Http\Middleware;

use Auth;
use App\School;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CheckSchoolSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $school_id = $request->session()->get('schoology')['school_nid'];
        try {
            $school = School::findOrFail($school_id);
        } catch (ModelNotFoundException $e) {
            return response()->view('nosubscription');
        }
        if (!$school->google_api_configured) {
            return response()->view('google_api_not_configured');
        } else {
            return $next($request);
        }
    }
}
