<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'type', 'days_per_week'])]
class School extends Model
{
    /**
     * Nama-nama hari sekolah, urut, sesuai jumlah hari sekolah ini.
     */
    public function dayNames(): array
    {
        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return array_slice($allDays, 0, $this->days_per_week);
    }
}