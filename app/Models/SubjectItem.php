<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectItem extends Model
{
    protected $fillable = [
        'subject_id',
        'item_id',
    ];

    public $timestamps = false;

    /**
     * Subject.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}