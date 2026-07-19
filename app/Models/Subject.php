<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'teacher_id',
    'class_id',
    'name',
    'room',
    'homework',
    'has_exam',
    'day',
    'start_time',
    'end_time',
    'is_active',
])]
class Subject extends Model
{
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(
            SchoolClass::class,
            'class_id'
        );
    }

    public function items()
    {
        return $this->belongsToMany(
            Item::class,
            'subject_items'
        );
    }
    protected function casts(): array
    {
        return [

            'has_exam' => 'boolean',

            'is_active' => 'boolean',

        ];
    }
}
