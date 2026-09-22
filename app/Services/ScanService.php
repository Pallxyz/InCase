<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\Item;
use App\Models\ItemResolution;
use App\Models\ScanLog;
use App\Models\User;
use Illuminate\Support\Str;

class ScanService
{
    public function __construct(
        private SchoolDayResolver $days,
        private ReturnCheckService $returns,
        private PackingChecklistService $checklist,
    ) {}

    /**
     * Proses 1 scan RFID. Return array ['code' => int, 'body' => array]
     * biar bisa dipake baik dari HTTP controller maupun listener MQTT.
     *
     * status di body tetap 'unknown' | 'success' | 'complete' | 'missing'
     * (alat scan tidak perlu diubah). Tambahan: 'phase' ('packing'|'return') dan 'for_date'.
     */
    public function handle(string $rfidUid): array
    {
        $item = Item::where('rfid_uid', $rfidUid)->first();

        if (! $item) {
            cache()->put('latest_unregistered_scan', [
                'uid' => $rfidUid,
                'at' => now()->toIso8601String(),
            ], now()->addMinutes(3));

            return [
                'code' => 404,
                'body' => [
                    'status' => 'unknown',
                    'message' => 'RFID tidak terdaftar di sistem.',
                ],
            ];
        }

        $student = $item->user;
        $now = now();
        $context = $this->days->resolve($student, $now);

        ScanLog::create([
            'user_id' => $student->id,
            'item_id' => $item->id,
            'status' => 'success',
            'phase' => $context['phase'],
            'for_date' => $context['date']->toDateString(),
            'scanned_at' => $now,
        ]);

        return match ($context['phase']) {
            'return' => $this->handleReturn($student, $item, $context),
            'packing' => $this->handlePacking($student, $item, $context),
            default => $this->handleIdle($student, $item, $now),
        };
    }

    /** Hari libur / tanpa jadwal: scan dicatat, tidak dicocokkan dengan pelajaran. */
    private function handleIdle(User $student, Item $item, $now): array
    {
        $holiday = Holiday::findFor($student->school_name, $student->class_id, $now);

        $body = [
            'status' => 'success',
            'item' => $item->name,
            'message' => "{$item->name} berhasil dipindai.",
        ];

        if ($holiday) {
            $body['holiday'] = $holiday->name;
            $body['message'] = "{$item->name} berhasil dipindai. Hari ini libur: {$holiday->name}.";
        }

        return ['code' => 200, 'body' => $body];
    }

    /** Persiapan: cocokkan dengan barang wajib SEMUA pelajaran di hari sekolah yang dituju. */
    private function handlePacking(User $student, Item $item, array $context): array
    {
        $date = $context['date'];

        $missing = $this->checklist->missingItems($student, $date, $context['subjects']);

        $base = [
            'phase' => 'packing',
            'for_date' => $date->toDateString(),
            'subject' => $context['subjects']->pluck('name')->unique()->implode(', '),
        ];

        if ($missing->isEmpty()) {
            return ['code' => 200, 'body' => $base + [
                'status' => 'complete',
                'message' => "Semua barang wajib {$this->dayLabel($date)} sudah lengkap.",
            ]];
        }

        return ['code' => 200, 'body' => $base + [
            'status' => 'missing',
            'missing_items' => $missing,
            'message' => 'Barang berikut belum dipindai: ' . $missing->implode(', '),
        ]];
    }

    /** Cek pulang: barang yang dibawa pagi harus kembali ke tas. */
    private function handleReturn(User $student, Item $item, array $context): array
    {
        $date = $context['date'];

        // Barang yang tadi dicatat "dikumpulkan/hilang" ternyata ketemu lagi -> catatannya dihapus.
        ItemResolution::where('user_id', $student->id)
            ->where('item_id', $item->id)
            ->whereDate('date', $date)
            ->delete();

        // (delete() di atas cukup: dipakai whereDate, bukan '==', supaya aman
        // lintas database walau kolomnya bertipe date.)

        $pending = $this->returns->pending($student, $date);

        $base = ['phase' => 'return', 'for_date' => $date->toDateString()];

        if ($pending->isEmpty()) {
            return ['code' => 200, 'body' => $base + [
                'status' => 'complete',
                'message' => 'Semua barang sudah kembali ke tas. Aman untuk pulang!',
            ]];
        }

        return ['code' => 200, 'body' => $base + [
            'status' => 'missing',
            'missing_items' => $pending->pluck('name')->values(),
            'message' => 'Barang belum kembali: ' . $pending->pluck('name')->implode(', '),
        ]];
    }

    private function dayLabel($date): string
    {
        return match (true) {
            $date->isToday() => 'hari ini',
            $date->isTomorrow() => 'besok',
            default => 'tanggal ' . $date->format('d/m/Y'),
        };
    }
}