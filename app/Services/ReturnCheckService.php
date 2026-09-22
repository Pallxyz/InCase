<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemResolution;
use App\Models\ScanLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Cek barang saat pulang: barang yang discan waktu persiapan (berangkat)
 * tapi belum discan lagi sebelum pulang = "belum kembali".
 */
class ReturnCheckService
{
    /** Barang yang sudah discan waktu persiapan untuk hari sekolah itu. */
    public function packedItems(User $student, Carbon $date): Collection
    {
        $ids = ScanLog::where('user_id', $student->id)
            ->where('status', 'success')
            ->where('phase', 'packing')
            ->whereDate('for_date', $date)
            ->pluck('item_id')
            ->unique();

        return Item::whereIn('id', $ids)->orderBy('name')->get();
    }

    /** id barang yang sudah discan lagi sebelum pulang. */
    public function returnedIds(User $student, Carbon $date): Collection
    {
        return ScanLog::where('user_id', $student->id)
            ->where('status', 'success')
            ->where('phase', 'return')
            ->whereDate('for_date', $date)
            ->pluck('item_id')
            ->unique();
    }

    /** Catatan "dikumpulkan/hilang", dikunci berdasarkan item_id. */
    public function resolutions(User $student, Carbon $date): Collection
    {
        return ItemResolution::where('user_id', $student->id)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('item_id');
    }

    /** Dibawa pagi tapi belum kembali (termasuk yang sudah dicatat dikumpulkan/hilang). */
    public function unreturned(User $student, Carbon $date): Collection
    {
        $returned = $this->returnedIds($student, $date);

        return $this->packedItems($student, $date)
            ->reject(fn (Item $item) => $returned->contains($item->id))
            ->values();
    }

    /** Belum kembali DAN belum dijelaskan siswa. Inilah yang masih dianggap "tertinggal". */
    public function pending(User $student, Carbon $date): Collection
    {
        $resolved = $this->resolutions($student, $date);

        return $this->unreturned($student, $date)
            ->reject(fn (Item $item) => $resolved->has($item->id))
            ->values();
    }
}