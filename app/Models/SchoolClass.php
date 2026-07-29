<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'major',
    'grade',
    'school_name',
])]
class SchoolClass extends Model
{
    public function students()
    {
        return $this->hasMany(
            User::class,
            'class_id'
        );
    }

    public function subjects()
    {
        return $this->hasMany(
            Subject::class,
            'class_id'
        );
    }
}