<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Core\Course;
use App\Models\Core\Attachment;
use App\Models\User\StudentProgress;
use App\Traits\Timestampable;

class Lesson extends Model
{
    use HasFactory, Timestampable;

    protected $timestampFormat = 'iso';

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'video_url',
        'pdf_url',
        'order_position',
        'duration_minutes',
    ];

    /**
     * Relationships
     */

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class)->orderBy('order_position');
    }

    public function studentProgress()
    {
        return $this->hasMany(StudentProgress::class);
    }
}
