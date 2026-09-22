<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Pengingat "siapkan barangmu", dikirim pagi atau malam sebelum berangkat
 * (bukan lagi per-pelajaran 30 menit sebelum kelas).
 */
class DailyPackingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Carbon $date,
        private Collection $missingItemNames,
    ) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $when = match (true) {
            $this->date->isToday() => 'hari ini',
            $this->date->isTomorrow() => 'besok',
            default => 'pada ' . $this->date->format('d/m/Y'),
        };

        return (new WebPushMessage)
            ->title('Siapkan Barangmu')
            ->body("Belum kescan untuk {$when}: " . $this->missingItemNames->implode(', '))
            ->data(['for_date' => $this->date->toDateString()]);
    }
}