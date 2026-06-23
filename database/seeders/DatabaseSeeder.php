<?php

namespace Database\Seeders;

use App\Models\Core\Course;
use App\Models\Core\Lesson;
use App\Models\User\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'phone' => '01000000000',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'subscription_status' => 'active',
            'points' => 0,
            'level' => 1,
        ]);

        // Create student user
        User::create([
            'name' => 'Student User',
            'email' => 'student@test.com',
            'phone' => '01100000000',
            'password' => Hash::make('password'),
            'role' => 'student',
            'subscription_status' => 'active',
            'points' => 450,
            'level' => 1,
        ]);

        // Course 1: الرياضيات
        $course1 = Course::create([
            'title' => 'الرياضيات - الفصل الأول',
            'description' => 'شرح تفصيلي لمادة الرياضيات تغطي جميع المواضيع الأساسية بأسلوب سهل وممتع',
            'instructor_name' => 'أ. محمد علي',
            'price' => 40.00,
            'cover_image' => 'https://images.unsplash.com/photo-1633356122544-f134324ef6db?w=500&h=300&fit=crop',
            'slug' => 'math-course-1',
            'is_published' => true,
        ]);

        Lesson::create([
            'course_id' => $course1->id,
            'title' => 'مقدمة إلى الجبر',
            'description' => 'في هذا الدرس سنتعرف على المفاهيم الأساسية للجبر',
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'order_position' => 1,
            'duration_minutes' => 25,
        ]);

        Lesson::create([
            'course_id' => $course1->id,
            'title' => 'المعادلات الخطية',
            'description' => 'حل المعادلات الخطية خطوة بخطوة',
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'order_position' => 2,
            'duration_minutes' => 30,
        ]);

        Lesson::create([
            'course_id' => $course1->id,
            'title' => 'الأنظمة الخطية',
            'description' => 'حل أنظمة من المعادلات الخطية',
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'order_position' => 3,
            'duration_minutes' => 35,
        ]);

        // Course 2: اللغة العربية
        $course2 = Course::create([
            'title' => 'اللغة العربية - النحو والبلاغة',
            'description' => 'دورة شاملة في قواعد اللغة العربية والبلاغة مع أمثلة عملية',
            'instructor_name' => 'أ. فاطمة أحمد',
            'price' => 40.00,
            'cover_image' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=500&h=300&fit=crop',
            'slug' => 'arabic-course-1',
            'is_published' => true,
        ]);

        Lesson::create([
            'course_id' => $course2->id,
            'title' => 'أنواع الجمل في اللغة العربية',
            'description' => 'التعرف على الجمل الاسمية والفعلية',
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'order_position' => 1,
            'duration_minutes' => 28,
        ]);

        Lesson::create([
            'course_id' => $course2->id,
            'title' => 'الإعراب والبناء',
            'description' => 'فهم إعراب الكلمات وحالات بنائها',
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'order_position' => 2,
            'duration_minutes' => 32,
        ]);

        Lesson::create([
            'course_id' => $course2->id,
            'title' => 'البلاغة والفنون الأدبية',
            'description' => 'الاستعارة والتشبيه والكناية',
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'order_position' => 3,
            'duration_minutes' => 30,
        ]);

        // Course 3: العلوم
        $course3 = Course::create([
            'title' => 'العلوم - الفيزياء والكيمياء',
            'description' => 'استكشاف عالم الفيزياء والكيمياء بتجارب عملية وشرح نظري عميق',
            'instructor_name' => 'أ. كريم حسن',
            'price' => 40.00,
            'cover_image' => 'https://images.unsplash.com/photo-1532094713988-21b0b09b731f?w=500&h=300&fit=crop',
            'slug' => 'science-course-1',
            'is_published' => true,
        ]);

        Lesson::create([
            'course_id' => $course3->id,
            'title' => 'أساسيات الفيزياء',
            'description' => 'القوة والحركة والطاقة',
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'order_position' => 1,
            'duration_minutes' => 40,
        ]);

        Lesson::create([
            'course_id' => $course3->id,
            'title' => 'الكيمياء الأساسية',
            'description' => 'العناصر والمركبات والتفاعلات',
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'order_position' => 2,
            'duration_minutes' => 38,
        ]);

        Lesson::create([
            'course_id' => $course3->id,
            'title' => 'الخصائص الفيزيائية والكيميائية',
            'description' => 'الفرق بين الخصائص الفيزيائية والكيميائية',
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'order_position' => 3,
            'duration_minutes' => 35,
        ]);
    }
}
