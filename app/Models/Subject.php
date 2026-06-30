<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'class_id',
        'name',
        'code',
        'credit',
    ];

    protected $casts = [
        'credit' => 'integer',
    ];

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
