<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'section_id',
        'name',
        'template_id',
        'folder_id',
        'schoology_assignment_id',
    ];

    public function section()
    {
        return $this->belongsTo('App\Section');
    }

    public function files()
    {
        return $this->hasMany('App\AssignmentFile');
    }
}
