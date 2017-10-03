<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable=[
        'id',
        'name',
        'folder_id',
        'owner_id',
        'students_folder_id',
        'assignments_folder_id',
        'templates_folder_id',
        'school_id'
    ];

    public function enrollments()
    {
        return $this->hasMany('App\Enrollment');
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'enrollments');
    }

    public function assignments()
    {
        return $this->hasMany('App\Assignment');
    }

    public function owner()
    {
        return $this->belongsTo('App\User');
    }

    public function school()
    {
        return $this->belongsTo('App\School');
    }
}
