<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectRoomChange extends Model
{
    protected $fillable = [
        'subject_id',
        'date',
        'location',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}