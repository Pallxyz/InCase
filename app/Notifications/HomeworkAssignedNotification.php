<?php

namespace App\Notifications;

use App\Models\Subject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class HomeworkAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Subject $subject) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('PR Baru: ' . $this->subject->name)
            ->body($this->subject->homework)
            ->data(['subject_id' => $this->subject->id]);
    }
}