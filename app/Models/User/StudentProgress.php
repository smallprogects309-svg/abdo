<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;
use App\Models\Core\Lesson;
use App\Traits\Timestampable;

class StudentProgress extends Model
{
    use HasFactory, Timestampable;

    protected $timestampFormat = 'iso';

    protected $table = 'student_progress';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'watched_percentage',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Helper methods
     */

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }
}
