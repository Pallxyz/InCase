<?php

namespace App\Observers;

use App\Models\Subject;
use App\Models\User;
use App\Notifications\HomeworkAssignedNotification;

class SubjectObserver
{
    public function created(Subject $subject): void
    {
        if (filled($subject->homework)) {
            $this->notifyStudents($subject);
        }
    }

    public function updated(Subject $subject): void
    {
        if ($subject->wasChanged('homework') && filled($subject->homework)) {
            $this->notifyStudents($subject);
        }
    }

    private function notifyStudents(Subject $subject): void
    {
        User::where('role', 'student')
            ->where('class_id', $subject->class_id)
            ->get()
            ->each(fn (User $student) => $student->notify(
                new HomeworkAssignedNotification($subject)
            ));
    }
}