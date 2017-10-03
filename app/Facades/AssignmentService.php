<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class AssignmentService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'assignmentservice';
    }
}
