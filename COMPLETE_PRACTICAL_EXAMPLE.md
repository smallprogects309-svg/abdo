# مثال عملي شامل - نظام إدارة الكورسات

## 📚 نريد بناء API لإدارة الكورسات

### الـ Requirements:
- ✅ إنشاء، عرض، تحديث، حذف الكورسات
- ✅ يعمل مع Web و Mobile
- ✅ Validation شامل
- ✅ Error handling منظم

---

## 1️⃣ إنشاء Form Request

**File**: `app/Http/Requests/StoreCourseRequest.php`

```php
<?php

namespace App\Http\Requests;

class StoreCourseRequest extends BaseFormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'instructor_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|numeric|min:1',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'عنوان الكورس مطلوب',
            'description.required' => 'وصف الكورس مطلوب',
            'instructor_id.required' => 'المدرس مطلوب',
        ];
    }
}
```

---

## 2️⃣ إنشاء Repository

**File**: `app/Repositories/CourseRepository.php`

```php
<?php

namespace App\Repositories;

use App\Models\Course;

class CourseRepository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel(new Course());
    }

    /**
     * Get courses by category
     */
    public function getByCategoryId($categoryId)
    {
        return $this->model->where('category_id', $categoryId)->get();
    }

    /**
     * Get courses by instructor
     */
    public function getByInstructorId($instructorId)
    {
        return $this->model->where('instructor_id', $instructorId)->get();
    }

    /**
     * Get active courses with pagination
     */
    public function getActive($perPage = 15)
    {
        return $this->model
            ->where('status', 'active')
            ->with('instructor', 'category')
            ->paginate($perPage);
    }

    /**
     * Search courses
     */
    public function search($query, $perPage = 15)
    {
        return $this->model
            ->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with('instructor', 'category')
            ->paginate($perPage);
    }

    /**
     * Get course with relations
     */
    public function find($id)
    {
        return $this->model
            ->with('instructor', 'category', 'lessons', 'reviews')
            ->find($id);
    }
}
```

---

## 3️⃣ إنشاء Service

**File**: `app/Services/CourseService.php`

```php
<?php

namespace App\Services;

use App\Repositories\CourseRepository;
use Exception;

class CourseService extends BaseService
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Get all active courses
     */
    public function getAllActive($perPage = 15)
    {
        try {
            return $this->courseRepository->getActive($perPage);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch courses: ' . $e->getMessage());
        }
    }

    /**
     * Get course by ID
     */
    public function getCourseById($id)
    {
        try {
            $course = $this->courseRepository->find($id);
            
            if (!$course) {
                throw new Exception('Course not found');
            }

            return $course;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Create new course
     */
    public function createCourse(array $data)
    {
        try {
            // Additional validation
            if ($data['price'] < 0) {
                throw new Exception('Price cannot be negative');
            }

            // Create course
            $course = $this->courseRepository->create($data);

            return $course;
        } catch (Exception $e) {
            throw new Exception('Failed to create course: ' . $e->getMessage());
        }
    }

    /**
     * Update course
     */
    public function updateCourse($id, array $data)
    {
        try {
            $course = $this->courseRepository->find($id);

            if (!$course) {
                throw new Exception('Course not found');
            }

            return $this->courseRepository->update($id, $data);
        } catch (Exception $e) {
            throw new Exception('Failed to update course: ' . $e->getMessage());
        }
    }

    /**
     * Delete course
     */
    public function deleteCourse($id)
    {
        try {
            $course = $this->courseRepository->find($id);

            if (!$course) {
                throw new Exception('Course not found');
            }

            return $this->courseRepository->delete($id);
        } catch (Exception $e) {
            throw new Exception('Failed to delete course: ' . $e->getMessage());
        }
    }

    /**
     * Search courses
     */
    public function searchCourses($query, $perPage = 15)
    {
        try {
            if (empty($query)) {
                throw new Exception('Search query is required');
            }

            return $this->courseRepository->search($query, $perPage);
        } catch (Exception $e) {
            throw new Exception('Search failed: ' . $e->getMessage());
        }
    }
}
```

---

## 4️⃣ إنشاء Controller (Mobile)

**File**: `app/Http/Controllers/Api/Mobile/CourseController.php`

```php
<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\StoreCourseRequest;
use App\Services\CourseService;

class CourseController extends BaseApiController
{
    private CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * List all courses
     * GET /api/v1/mobile/courses
     */
    public function index()
    {
        try {
            $courses = $this->courseService->getAllActive();
            return $this->paginatedResponse(
                $courses,
                'Courses retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get single course
     * GET /api/v1/mobile/courses/{id}
     */
    public function show($id)
    {
        try {
            $course = $this->courseService->getCourseById($id);
            return $this->successResponse($course, 'Course retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Create course (admin only)
     * POST /api/v1/mobile/courses
     */
    public function store(StoreCourseRequest $request)
    {
        try {
            $course = $this->courseService->createCourse($request->validated());
            return $this->successResponse($course, 'Course created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update course
     * PUT /api/v1/mobile/courses/{id}
     */
    public function update($id, StoreCourseRequest $request)
    {
        try {
            $course = $this->courseService->updateCourse($id, $request->validated());
            return $this->successResponse($course, 'Course updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete course
     * DELETE /api/v1/mobile/courses/{id}
     */
    public function destroy($id)
    {
        try {
            $this->courseService->deleteCourse($id);
            return $this->successResponse(null, 'Course deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Search courses
     * GET /api/v1/mobile/courses/search?q=laravel
     */
    public function search()
    {
        try {
            $query = request('q');
            $courses = $this->courseService->searchCourses($query);
            return $this->paginatedResponse($courses, 'Search results');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
```

---

## 5️⃣ إضافة Routes

**File**: `routes/api/v1/mobile.php`

```php
<?php

use App\Http\Controllers\Api\Mobile\CourseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->group(function () {
    // Public course routes
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::get('/courses/search', [CourseController::class, 'search']);
});

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    // Protected course routes (admin only)
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);
});
```

---

## 6️⃣ API Responses Examples

### List Courses
```json
GET /api/v1/mobile/courses

{
  "success": true,
  "message": "Courses retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Laravel Basics",
      "description": "Learn Laravel from scratch",
      "price": 49.99,
      "instructor": { "id": 1, "name": "Ahmed" },
      "category": { "id": 2, "name": "Web Development" }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

### Get Single Course
```json
GET /api/v1/mobile/courses/1

{
  "success": true,
  "message": "Course retrieved successfully",
  "data": {
    "id": 1,
    "title": "Laravel Basics",
    "description": "Complete Laravel course",
    "price": 49.99,
    "duration": 40,
    "instructor": {
      "id": 1,
      "name": "Ahmed",
      "email": "ahmed@example.com"
    },
    "category": {
      "id": 2,
      "name": "Web Development"
    },
    "lessons": [
      { "id": 1, "title": "Introduction" },
      { "id": 2, "title": "Setup" }
    ],
    "reviews": [
      { "id": 1, "rating": 5, "comment": "Great course!" }
    ]
  }
}
```

### Create Course (Validation Error)
```json
POST /api/v1/mobile/courses
Status: 422

{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["عنوان الكورس مطلوب"],
    "price": ["السعر مطلوب"]
  }
}
```

---

## 🎯 Summary

✅ **Clean Architecture** - فصل الاهتمامات
✅ **Type Hinting** - Type safety
✅ **Error Handling** - معالجة الأخطاء
✅ **Validation** - التحقق من الـ input
✅ **Reusability** - إعادة استخدام الأكواد
✅ **Scalability** - سهولة التطور

هذا المثال يمكن تكراره لـ:
- Users Management
- Quiz Management
- Payment Processing
- Gamification
- وأي feature أخرى!

