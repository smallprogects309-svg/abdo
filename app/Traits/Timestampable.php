<?php

namespace App\Traits;

use Carbon\Carbon;

/**
 * Timestampable Trait
 * 
 * للـ timestamps على Models بـ تنسيقات متقدمة
 * 
 * في Model استخدم:
 * protected $dateFormat = 'iso';  // أو 'human', 'full', 'short'
 */
trait Timestampable
{
    /**
     * Get created_at in various formats
     */
    public function getCreatedAtAttribute($value)
    {
        return $this->formatTimestamp($value);
    }

    /**
     * Get updated_at in various formats
     */
    public function getUpdatedAtAttribute($value)
    {
        return $this->formatTimestamp($value);
    }

    /**
     * Get deleted_at in various formats
     */
    public function getDeletedAtAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return $this->formatTimestamp($value);
    }

    /**
     * Format timestamp based on Model setting
     */
    protected function formatTimestamp($value)
    {
        $carbon = Carbon::parse($value);

        $format = $this->getTimestampFormat();

        return match ($format) {
            'iso' => $carbon->toIso8601String(),          // 2024-04-11T15:45:30Z
            'human' => $carbon->diffForHumans(),          // 2 hours ago
            'human_ar' => $this->diffForHumansArabic($carbon),  // قبل ساعتين
            'full' => $carbon->format('Y-m-d H:i:s'),     // 2024-04-11 15:45:30
            'full_ar' => $this->formatArabic($carbon),    // 11 أبريل 2024، 3:45 مساءً
            'short' => $carbon->format('Y-m-d'),          // 2024-04-11
            'time' => $carbon->format('H:i:s'),           // 15:45:30
            'date' => $carbon->format('d/m/Y'),           // 11/04/2024
            'timestamp' => $carbon->timestamp,            // Unix timestamp
            default => $carbon->toIso8601String(),
        };
    }

    /**
     * Get timestamp format from Model or use default
     */
    protected function getTimestampFormat(): string
    {
        if (isset($this->timestampFormat)) {
            return $this->timestampFormat;
        }

        return config('app.timestamp_format', 'iso');
    }

    /**
     * Human readable format (Arabic)
     * مثال: قبل ساعتين، منذ يوم، قبل شهر
     */
    protected function diffForHumansArabic(Carbon $carbon): string
    {
        $now = Carbon::now();
        $diff = $carbon->diff($now);

        if ($diff->days >= 365) {
            return 'منذ ' . intval($diff->days / 365) . ($diff->days / 365 > 1 ? ' سنين' : ' سنة');
        }

        if ($diff->days >= 30) {
            return 'منذ ' . intval($diff->days / 30) . ($diff->days / 30 > 1 ? ' أشهر' : ' شهر');
        }

        if ($diff->days >= 1) {
            return 'منذ ' . $diff->days . ($diff->days > 1 ? ' أيام' : ' يوم');
        }

        if ($diff->hours >= 1) {
            return 'قبل ' . $diff->hours . ($diff->hours > 1 ? ' ساعات' : ' ساعة');
        }

        if ($diff->minutes >= 1) {
            return 'قبل ' . $diff->minutes . ($diff->minutes > 1 ? ' دقائق' : ' دقيقة');
        }

        return 'الآن';
    }

    /**
     * Format date in Arabic
     * مثال: 11 أبريل 2024، 3:45 مساءً
     */
    protected function formatArabic(Carbon $carbon): string
    {
        $months = [
            'January' => 'يناير',
            'February' => 'فبراير',
            'March' => 'مارس',
            'April' => 'أبريل',
            'May' => 'مايو',
            'June' => 'يونيو',
            'July' => 'يوليو',
            'August' => 'أغسطس',
            'September' => 'سبتمبر',
            'October' => 'أكتوبر',
            'November' => 'نوفمبر',
            'December' => 'ديسمبر',
        ];

        $englishMonth = $carbon->format('F');
        $arabicMonth = $months[$englishMonth] ?? $englishMonth;

        $day = $carbon->format('d');
        $year = $carbon->format('Y');
        $time = $carbon->format('h:i');
        $period = $carbon->format('A') === 'AM' ? 'صباحاً' : 'مساءً';

        return "{$day} {$arabicMonth} {$year}، {$time} {$period}";
    }

    /**
     * Get timestamp with optional timezone
     */
    public function getFormattedTimestamp($field = 'created_at', $format = 'iso', $timezone = null)
    {
        $value = $this->attributes[$field] ?? null;

        if (is_null($value)) {
            return null;
        }

        $carbon = Carbon::parse($value);

        if (!is_null($timezone)) {
            $carbon->setTimezone($timezone);
        }

        return match ($format) {
            'iso' => $carbon->toIso8601String(),
            'human' => $carbon->diffForHumans(),
            'human_ar' => $this->diffForHumansArabic($carbon),
            'full' => $carbon->format('Y-m-d H:i:s'),
            'full_ar' => $this->formatArabic($carbon),
            'short' => $carbon->format('Y-m-d'),
            'time' => $carbon->format('H:i:s'),
            'date' => $carbon->format('d/m/Y'),
            'timestamp' => $carbon->timestamp,
            default => $carbon->toIso8601String(),
        };
    }

    /**
     * Check if timestamp is recent (within X seconds)
     */
    public function isRecent($field = 'created_at', $seconds = 3600): bool
    {
        $value = $this->attributes[$field] ?? null;

        if (is_null($value)) {
            return false;
        }

        $carbon = Carbon::parse($value);
        return $carbon->diffInSeconds(Carbon::now()) <= $seconds;
    }

    /**
     * Get timestamp in multiple formats at once
     */
    public function getTimestampMultiFormat($field = 'created_at'): array
    {
        $value = $this->attributes[$field] ?? null;

        if (is_null($value)) {
            return [];
        }

        return [
            'iso' => $this->getFormattedTimestamp($field, 'iso'),
            'human' => $this->getFormattedTimestamp($field, 'human'),
            'human_ar' => $this->getFormattedTimestamp($field, 'human_ar'),
            'full' => $this->getFormattedTimestamp($field, 'full'),
            'full_ar' => $this->getFormattedTimestamp($field, 'full_ar'),
            'short' => $this->getFormattedTimestamp($field, 'short'),
            'time' => $this->getFormattedTimestamp($field, 'time'),
            'date' => $this->getFormattedTimestamp($field, 'date'),
            'timestamp' => $this->getFormattedTimestamp($field, 'timestamp'),
        ];
    }
}
