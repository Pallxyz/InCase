<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemResolution extends Model
{
    public const SUBMITTED = 'submitted'; // dikumpulkan
    public const LOST = 'lost';           // hilang

    protected $fillable = ['user_id', 'item_id', 'date', 'status'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}