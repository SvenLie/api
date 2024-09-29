<?php

namespace App\Models\HtwDresden\Timetable;

use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    protected $fillable = [
        'moduleNumber',
        'module',
        'period',
        'type',
        'place',
        'lecturer',
        'link',
        'start',
        'end',
        'weekday'
    ];
}
