<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanLog extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'status',
        'phase',
        'for_date',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'for_date' => 'date',
    ];

    /**
     * Student.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scanned item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}