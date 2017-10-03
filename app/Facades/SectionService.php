<?php namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class SectionService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sectionservice';
    }
}
