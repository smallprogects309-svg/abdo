<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;
use App\Models\Core\Course;
use App\Traits\Timestampable;

class Enrollment extends Model
{
    use HasFactory, Timestampable;

    protected $timestampFormat = 'iso';

    protected $fillable = [
        'user_id',
        'course_id',
        'enrolled_date',
        'expires_at',
    ];

    protected $casts = [
        'enrolled_date' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Helper methods
     */

    public function isActive(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }
}
