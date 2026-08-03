<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ScanLog;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rfid_uid' => ['required', 'string'],
        ]);

        $item = Item::where('rfid_uid', $validated['rfid_uid'])->first();

        if (! $item) {
            return response()->json([
                'status' => 'unknown',
                'message' => 'RFID tidak terdaftar di sistem.',
            ], 404);
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
            return response()->json([
                'status' => 'success',
                'item' => $item->name,
                'message' => "{$item->name} berhasil dipindai.",
            ]);
        }

        $requiredNames = $subject->requiredItems->pluck('name')
            ->map(fn (string $name) => Str::lower(trim($name)));

        $scannedNamesToday = ScanLog::where('scan_logs.user_id', $student->id)
            ->where('scan_logs.status', 'success')
            ->whereDate('scan_logs.scanned_at', today())
            ->join('items', 'items.id', '=', 'scan_logs.item_id')
            ->pluck('items.name')
            ->map(fn (string $name) => Str::lower(trim($name)));

        $missingOriginalNames = $subject->requiredItems->pluck('name')
            ->filter(fn (string $name) => ! $scannedNamesToday->contains(Str::lower(trim($name))));

        if ($missingOriginalNames->isEmpty()) {
            return response()->json([
                'status' => 'complete',
                'subject' => $subject->name,
                'message' => "Semua barang wajib buat {$subject->name} sudah lengkap.",
            ]);
        }

        return response()->json([
            'status' => 'missing',
            'subject' => $subject->name,
            'missing_items' => $missingOriginalNames->values(),
            'message' => 'Barang berikut belum dipindai: ' . $missingOriginalNames->implode(', '),
        ]);
    }
}