<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssignmentFile extends Model
{
    protected $fillable = [
        'name',
        'file_id',
        'enrollment_id',
        'assignment_id',
    ];

    public function assignment()
    {
        return $this->belongsTo('App\Assignment');
    }

    public function enrollment()
    {
        return $this->belongsTo('App\Enrollment');
    }
}
