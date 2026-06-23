<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Core\Lesson;
use App\Models\User\Enrollment;
use App\Models\User\User;
use App\Traits\Filterable;
use App\Traits\Timestampable;

class Course extends Model
{
    use HasFactory, Filterable, Timestampable;

    /**
     * Timestamp Format Configuration
     * Options: 'iso', 'human', 'human_ar', 'full', 'full_ar', 'short', 'time', 'date', 'timestamp'
     */
    protected $timestampFormat = 'iso';

    /**
     * Filterable Trait Configuration
     */
    protected $searchable = ['title', 'description', 'slug'];
    protected $filterable = ['status', 'level', 'category_id', 'is_published'];
    protected $dateFilters = ['created_at', 'updated_at', 'published_at'];

    protected $fillable = [
        'title',
        'description',
        'instructor_id',
        'instructor_name',
        'price',
        'cover_image',
        'slug',
    ];

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order_position');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function getTotalStudentsAttribute()
    {
        return $this->enrollments()->count();
    }
}
