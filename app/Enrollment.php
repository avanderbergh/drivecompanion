<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'section_id',
        'user_id',
        'folder_id',
        'admin',
    ];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function section()
    {
        return $this->belongsTo('App\Section');
    }

    public function assignment_files()
    {
        return $this->hasManyThrough('App\AssignmentFile', 'App\Assignment');
    }

    public function assignments()
    {
        return $this->hasMany('App\Assignment');
    }
}
