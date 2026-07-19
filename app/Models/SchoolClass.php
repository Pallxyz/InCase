<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'major',
    'grade',
])]
class SchoolClass extends Model
{
    /**
     * Students in this class.
     */
    public function students()
    {
        return $this->hasMany(
            User::class,
            'class_id'
        );
    }

    /**
     * Subjects assigned to this class.
     */
    public function subjects()
    {
        return $this->hasMany(
            Subject::class,
            'class_id'
        );
    }
}