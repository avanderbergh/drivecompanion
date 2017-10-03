<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'id', 'email', 'folder_id', 'name', 'picture', 'school_id', 'name_first', 'name_last', 'name_first_preferred'
    ];

    public function enrollments()
    {
        return $this->hasMany('App\Enrollment');
    }

    public function sections()
    {
        return $this->belongsToMany('App\Section', 'enrollments');
    }

    public function school()
    {
        return $this->belongsTo('App\School');
    }
}
