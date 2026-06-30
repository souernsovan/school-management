<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'dob',
        'qualification',
        'specialization',
        'hire_date',
        'email',
        'phone',
        'address',
        'status',
    ];

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    
}