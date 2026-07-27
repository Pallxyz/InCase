<?php

namespace App\Notifications;

use App\Models\Subject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ItemReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Subject $subject,
        private Collection $missingItemNames
    ) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Cek barang: ' . $this->subject->name)
            ->body('Belum kescan: ' . $this->missingItemNames->implode(', '))
            ->data(['subject_id' => $this->subject->id]);
    }
}