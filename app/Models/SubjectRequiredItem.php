<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['subject_id', 'name'])]
class SubjectRequiredItem extends Model
{
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}