<?php

namespace App\Console\Commands;

use App\Models\ScanLog;
use App\Models\Subject;
use App\Models\User;
use App\Notifications\ItemReminderNotification;
use Illuminate\Console\Command;

class SendItemReminders extends Command
{
    protected $signature = 'incase:send-item-reminders';
    protected $description = 'Kirim reminder barang yang belum discan, 30 menit sebelum jadwal mulai';

    public function handle(): void
    {
        $now = now();
        $windowEnd = $now->copy()->addMinutes(30)->format('H:i');

        $subjects = Subject::with('items')
            ->where('day', $now->englishDayOfWeek)
            ->where('is_active', true)
            ->whereTime('start_time', '>=', $now->format('H:i'))
            ->whereTime('start_time', '<=', $windowEnd)
            ->get();

        foreach ($subjects as $subject) {
            $requiredItems = $subject->items;

            if ($requiredItems->isEmpty()) {
                continue;
            }

            $students = User::where('role', 'student')
                ->where('class_id', $subject->class_id)
                ->get();

            foreach ($students as $student) {
                $scannedItemIds = ScanLog::where('user_id', $student->id)
                    ->where('status', 'success')
                    ->whereDate('scanned_at', today())
                    ->pluck('item_id');

                $missing = $requiredItems->whereNotIn('id', $scannedItemIds);

                if ($missing->isNotEmpty()) {
                    $student->notify(new ItemReminderNotification(
                        $subject,
                        $missing->pluck('name')
                    ));
                }
            }
        }
    }
}