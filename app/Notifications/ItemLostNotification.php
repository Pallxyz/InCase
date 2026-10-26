<?php

namespace App\Notifications;

use App\Models\Item;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ItemLostNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Item $item,
        private User $student,
        private ?string $note = null,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'item_lost',
            'item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'class_id' => $this->student->class_id,
            'note' => $this->note,
            'message' => "{$this->student->name} melaporkan {$this->item->name} hilang."
                . ($this->note ? " Keterangan: {$this->note}" : ''),
        ];
    }
}