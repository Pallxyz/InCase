<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'class_id',
    'phone',
    'school_name',
    'student_id',
    'class_changed_at',
    'avatar',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'class_id' => 'integer',
            'class_changed_at' => 'datetime',
        ];
    }

    /**
     * Class (Student only)
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function classmates()
    {
        return $this->hasMany(
            User::class,
            'class_id',
            'class_id'
        );
    }

    /**
     * Teacher Subjects
     */
    public function subjects()
    {
        return $this->hasMany(
            Subject::class,
            'teacher_id'
        );
    }

    /**
     * Student Items
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Student Scan Logs
     */
    public function scanLogs()
    {
        return $this->hasMany(ScanLog::class);
    }
}
