<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\DailyPackingReminderNotification;
use App\Services\PackingChecklistService;
use App\Services\SchoolDayResolver;
use Illuminate\Console\Command;

class SendItemReminders extends Command
{
    protected $signature = 'incase:send-item-reminders';

    protected $description = 'Kirim pengingat "siapkan barangmu", dijadwalkan pagi & malam sebelum berangkat';

    public function __construct(
        private SchoolDayResolver $days,
        private PackingChecklistService $checklist,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $now = now();

        User::where('role', 'student')
            ->whereNotNull('class_id')
            ->chunkById(100, function ($students) use ($now) {
                foreach ($students as $student) {
                    $this->remind($student, $now);
                }
            });
    }

    private function remind(User $student, $now): void
    {
        // resolve() sudah menangani: hari libur/tanpa jadwal dilewati (fase 'idle'),
        // dan setelah jam 18:00 otomatis mengincar hari sekolah berikutnya.
        $context = $this->days->resolve($student, $now);

        if ($context['phase'] !== 'packing') {
            return;
        }

        $missing = $this->checklist->missingItems($student, $context['date'], $context['subjects']);

        if ($missing->isEmpty()) {
            return;
        }

        $student->notify(new DailyPackingReminderNotification($context['date'], $missing));
    }
}