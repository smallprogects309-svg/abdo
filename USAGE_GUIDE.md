# دليل استخدام البنية المنظمة للـ Backend

## 📚 المحتويات

1. [الهيكل العام](#الهيكل-العام)
2. [إنشاء Controller](#إنشاء-controller)
3. [إنشاء Service](#إنشاء-service)
4. [إنشاء Repository](#إنشاء-repository)
5. [إضافة Routes](#إضافة-routes)
6. [إنشاء Form Requests](#إنشاء-form-requests)

---

## الهيكل العام

```
request → Route → Controller → Service → Repository → Model → Database
                      ↓
                   Response
```

---

## إنشاء Controller

### مثال: User Controller للـ Mobile

**File**: `app/Http/Controllers/Api/Mobile/UserController.php`

```php
<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\UserService;

class UserController extends BaseApiController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get user profile
     */
    public function profile()
    {
        try {
            $user = auth()->user();
            return $this->successResponse($user, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update user profile
     */
    public function update(UpdateUserRequest $request)
    {
        try {
            $user = $this->userService->updateProfile(auth()->id(), $request->validated());
            return $this->successResponse($user, 'Profile updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get user courses
     */
    public function courses()
    {
        try {
            $courses = $this->userService->getUserCourses(auth()->id());
            return $this->successResponse($courses, 'Courses retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
```

---

## إنشاء Service

### مثال: User Service

**File**: `app/Services/UserService.php`

```php
<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService extends BaseService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, array $data)
    {
        // Validation logic
        if (empty($data['name'])) {
            throw new \Exception('Name is required');
        }

        // Update logic
        return $this->userRepository->update($userId, $data);
    }

    /**
     * Get user courses
     */
    public function getUserCourses($userId)
    {
        $user = $this->userRepository->find($userId);
        
        if (!$user) {
            throw new \Exception('User not found');
        }

        return $user->courses()->get();
    }

    /**
     * Get user with relations
     */
    public function getUserWithRelations($userId)
    {
        return $this->userRepository->find($userId)
            ->load('courses', 'quizzes', 'certificates');
    }
}
```

---

## إنشاء Repository

### مثال: User Repository

**File**: `app/Repositories/UserRepository.php`

```php
<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel(new User());
    }

    /**
     * Get user by email
     */
    public function getByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Get active users
     */
    public function getActiveUsers()
    {
        return $this->model->where('status', 'active')->get();
    }

    /**
     * Search users
     */
    public function search($query)
    {
        return $this->model
            ->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->paginate(15);
    }
}
```

---

## إضافة Routes

### فى `routes/api/v1/mobile.php`

```php
<?php

use App\Http\Controllers\Api\Mobile\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->group(function () {
    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
});

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    // Protected routes
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'update']);
    Route::get('/user/courses', [UserController::class, 'courses']);
});
```

### فى `routes/api/v1/web.php`

```php
<?php

use App\Http\Controllers\Api\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::get('/admin/users/{id}', [UserController::class, 'show']);
    Route::put('/admin/users/{id}', [UserController::class, 'update']);
});
```

### تحديث `routes/api.php`

```php
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->group(function () {
    // Shared routes
    require __DIR__ . '/api/v1/shared.php';
    
    // Web API routes
    Route::prefix('web')->group(function () {
        require __DIR__ . '/api/v1/web.php';
    });
    
    // Mobile API routes
    Route::prefix('mobile')->group(function () {
        require __DIR__ . '/api/v1/mobile.php';
    });
});
```

---

## إنشاء Form Requests

### مثال: Update User Request

**File**: `app/Http/Requests/UpdateUserRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // أو يمكنك التحقق من الصلاحيات
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يجب أن يكون البريد صحيحاً',
            'email.unique' => 'هذا البريد مسجل بالفعل',
        ];
    }
}
```

---

## Summary

| Layer | Purpose | Location |
|-------|---------|----------|
| **Route** | Define endpoints | `routes/api/v1/` |
| **Controller** | Handle requests | `app/Http/Controllers/Api/` |
| **Request** | Validate input | `app/Http/Requests/` |
| **Service** | Business logic | `app/Services/` |
| **Repository** | Database abstraction | `app/Repositories/` |
| **Model** | Database model | `app/Models/` |

---

## Best Practices

✅ **Always use Services** - لا تضع business logic مباشرة في Controller
✅ **Use Repositories** - للـ queries المعقدة من Database
✅ **Validate input** - استخدم Form Requests للـ validation
✅ **Handle exceptions** - دائماً catch و handle الـ exceptions
✅ **Use consistent response format** - استخدم BaseApiController
✅ **Separate endpoints** - فصل Web و Mobile endpoints إذا اختلفت
✅ **Version your API** - استخدم versioning (v1, v2, etc)

