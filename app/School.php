<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable=[
        'id',
        'name',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
    ];

    protected function sections()
    {
        return $this->hasMany('App\Section');
    }
}
