<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ScanLog;
use App\Models\Subject;
use Illuminate\Support\Str;

class ScanService
{
    /**
     * Proses 1 scan RFID. Return array ['code' => int, 'body' => array]
     * biar bisa dipake baik dari HTTP controller maupun listener MQTT.
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

        ScanLog::create([
            'user_id' => $student->id,
            'item_id' => $item->id,
            'status' => 'success',
            'scanned_at' => now(),
        ]);

        $now = now();

        $subject = Subject::with('requiredItems')
            ->where('class_id', $student->class_id)
            ->where('day', $now->englishDayOfWeek)
            ->where('is_active', true)
            ->whereTime('start_time', '<=', $now->format('H:i'))
            ->whereTime('end_time', '>=', $now->format('H:i'))
            ->first();

        if (! $subject) {
            return [
                'code' => 200,
                'body' => [
                    'status' => 'success',
                    'item' => $item->name,
                    'message' => "{$item->name} berhasil dipindai.",
                ],
            ];
        }

        $scannedNamesToday = ScanLog::where('scan_logs.user_id', $student->id)
            ->where('scan_logs.status', 'success')
            ->whereDate('scan_logs.scanned_at', today())
            ->join('items', 'items.id', '=', 'scan_logs.item_id')
            ->pluck('items.name')
            ->map(fn(string $name) => Str::lower(trim($name)));

        $missingOriginalNames = $subject->requiredItems->pluck('name')
            ->filter(fn(string $name) => ! $scannedNamesToday->contains(Str::lower(trim($name))));

        if ($missingOriginalNames->isEmpty()) {
            return [
                'code' => 200,
                'body' => [
                    'status' => 'complete',
                    'subject' => $subject->name,
                    'message' => "Semua barang wajib buat {$subject->name} sudah lengkap.",
                ],
            ];
        }

        return [
            'code' => 200,
            'body' => [
                'status' => 'missing',
                'subject' => $subject->name,
                'missing_items' => $missingOriginalNames->values(),
                'message' => 'Barang berikut belum dipindai: ' . $missingOriginalNames->implode(', '),
            ],
        ];
    }
}
