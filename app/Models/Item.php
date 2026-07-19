<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'rfid_uid',
        'description',
        'status',
    ];

    /**
     * Owner of the item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Subjects requiring this item.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'subject_items'
        );
    }

    /**
     * RFID scan history.
     */
    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }
}