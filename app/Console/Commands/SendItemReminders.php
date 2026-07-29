<?php

namespace App\Console\Commands;

use App\Models\ScanLog;
use App\Models\Subject;
use App\Models\User;
use App\Notifications\ItemReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendItemReminders extends Command
{
    protected $signature = 'incase:send-item-reminders';
    protected $description = 'Kirim reminder barang yang belum discan, 30 menit sebelum jadwal mulai';

    public function handle(): void
    {
        $now = now();
        $windowEnd = $now->copy()->addMinutes(30)->format('H:i');

        $subjects = Subject::with('requiredItems')
            ->where('day', $now->englishDayOfWeek)
            ->where('is_active', true)
            ->whereTime('start_time', '>=', $now->format('H:i'))
            ->whereTime('start_time', '<=', $windowEnd)
            ->get();

        foreach ($subjects as $subject) {
            $requiredNames = $subject->requiredItems->pluck('name');

            if ($requiredNames->isEmpty()) {
                continue;
            }

            $students = User::where('role', 'student')
                ->where('class_id', $subject->class_id)
                ->get();

            foreach ($students as $student) {
                $missing = $this->missingItemsForStudent($student->id, $requiredNames);

                if ($missing->isNotEmpty()) {
                    $student->notify(new ItemReminderNotification(
                        $subject,
                        $missing
                    ));
                }
            }
        }
    }

    /**
     * Match required item names against the student's own scanned items today
     * (case-insensitive, per-student — not by global item ID).
     */
    private function missingItemsForStudent(int $studentId, \Illuminate\Support\Collection $requiredNames): \Illuminate\Support\Collection
    {
        $scannedNamesToday = ScanLog::where('scan_logs.user_id', $studentId)
            ->where('scan_logs.status', 'success')
            ->whereDate('scan_logs.scanned_at', today())
            ->join('items', 'items.id', '=', 'scan_logs.item_id')
            ->pluck('items.name')
            ->map(fn (string $name) => Str::lower(trim($name)));

        return $requiredNames->filter(
            fn (string $required) => ! $scannedNamesToday->contains(Str::lower(trim($required)))
        )->values();
    }
}