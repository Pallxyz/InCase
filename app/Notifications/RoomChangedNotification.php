<?php

namespace App\Notifications;

use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class RoomChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Subject $subject,
        private string $teacherName,
        private string $location,
        private Carbon $date,
        private bool $restored = false,
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

        $verb = $this->restored ? 'mengembalikan' : 'memindahkan';

        return (new WebPushMessage)
            ->title('Ruang Belajar Diubah')
            ->body("{$this->teacherName} {$verb} {$this->subject->name} ke {$this->location} {$when}.")
            ->data([
                'subject_id' => $this->subject->id,
                'date' => $this->date->toDateString(),
            ]);
    }
}