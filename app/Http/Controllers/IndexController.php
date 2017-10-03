<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Requests;
use Mockery\Exception;
use App\Facades\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;

class IndexController extends Controller
{
    public function index()
    {
        if (key_exists('realm_id', $_GET)) {
            $realm_id = $_GET["realm_id"];
            if (UserService::isTeacher($realm_id)) {
                return redirect(url('app/teacher/'.$realm_id, [], true));
            } else {
                return redirect(url('app/student/'.$realm_id, [], true));
            }
        } else {
            return view('errors.custom.no_realm_id');
        }
    }

    public function cookie_preload()
    {
        $html = '
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                    <title>Loading App...</title>
                </head>
                <body>
                    <p>Loading application...</p>
                    <script type="text/javascript">
                        self.close();
                    </script>
                </body>
            </html>';
        $response = new Response($html);
        return $response->withCookie(cookie('name', 'value', 5));
    }

    public function health_check()
    {
        if ($failed_jobs = DB::table('failed_jobs')->get()) {
            return count($failed_jobs)." failed jobs!";
        } else {
            return "Everything is OK!";
        }
    }

}
