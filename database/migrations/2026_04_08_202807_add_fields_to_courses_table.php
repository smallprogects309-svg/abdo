<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('courses', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('courses', 'category')) {
                $table->string('category')->nullable()->after('description');
            }
            if (!Schema::hasColumn('courses', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('category');
            }
            if (!Schema::hasColumn('courses', 'display_order')) {
                $table->integer('display_order')->default(0)->after('is_published');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('courses', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('courses', 'is_published')) {
                $table->dropColumn('is_published');
            }
            if (Schema::hasColumn('courses', 'display_order')) {
                $table->dropColumn('display_order');
            }
        });
    }
};
