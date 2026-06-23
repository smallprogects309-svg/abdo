<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Core\Lesson;
use App\Traits\Timestampable;

class Quiz extends Model
{
    use HasFactory, Timestampable;

    protected $timestampFormat = 'iso';

    protected $fillable = [
        'lesson_id',
        'timestamp_seconds',
        'question',
        'description',
        'options',
        'correct_answer',
        'explanation',
        'order_position',
    ];

    protected $casts = [
        'options' => 'json',
    ];

    /**
     * Relationships
     */

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
