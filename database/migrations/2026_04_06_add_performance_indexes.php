<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Add performance indexes using raw SQL
     * This is the simplest and safest approach
     */
    public function up(): void
    {
        // Use raw SQL with IF NOT EXISTS to avoid conflicts
        // This ensures indexes are added only if they don't exist
        
        DB::unprepared('ALTER TABLE users ADD INDEX IF NOT EXISTS idx_role (role)');
        DB::unprepared('ALTER TABLE users ADD INDEX IF NOT EXISTS idx_subscription (subscription_status)');
        
        DB::unprepared('ALTER TABLE courses ADD INDEX IF NOT EXISTS idx_instructor (instructor_id)');
        DB::unprepared('ALTER TABLE courses ADD INDEX IF NOT EXISTS idx_created (created_at)');
        
        DB::unprepared('ALTER TABLE lessons ADD INDEX IF NOT EXISTS idx_course (course_id)');
        DB::unprepared('ALTER TABLE lessons ADD INDEX IF NOT EXISTS idx_lesson_order (course_id, order_position)');
        
        DB::unprepared('ALTER TABLE enrollments ADD INDEX IF NOT EXISTS idx_user (user_id)');
        DB::unprepared('ALTER TABLE enrollments ADD INDEX IF NOT EXISTS idx_course (course_id)');
        DB::unprepared('ALTER TABLE enrollments ADD INDEX IF NOT EXISTS idx_expires (expires_at)');
    }

    public function down(): void
    {
        // Don't drop indexes - they help performance
    }
};
