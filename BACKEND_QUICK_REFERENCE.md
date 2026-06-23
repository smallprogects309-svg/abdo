# ⚡ Backend Quick Reference Card

## 🗂️ Folder Paths

```
Controllers (API)
├── app/Http/Controllers/Api/Mobile/     👈 React Native
├── app/Http/Controllers/Api/Web/        👈 Browser Apps
└── app/Http/Controllers/Api/Shared/     👈 Common endpoints

Business Logic
├── app/Services/                        👈 Services
├── app/Repositories/                    👈 Repositories
├── app/Traits/                          👈 Traits
└── app/Http/Requests/                   👈 Validation

Routes
├── routes/api.php                       👈 Main entry
├── routes/api/v1/mobile.php            👈 Mobile routes
├── routes/api/v1/web.php               👈 Web routes
└── routes/api/v1/shared.php            👈 Shared routes
```

---

## 📝 Templates

### Create Controller
```php
<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\YourService;

class YourController extends BaseApiController
{
    private YourService $service;

    public function __construct(YourService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        try {
            return $this->successResponse($data, 'Success');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
```

### Create Service
```php
<?php

namespace App\Services;

use App\Repositories\YourRepository;

class YourService extends BaseService {}
```

### Create Repository
```php
<?php

namespace App\Repositories;

use App\Models\YourModel;

class YourRepository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel(new YourModel());
    }
}
```

### Create Form Request
```php
<?php

namespace App\Http\Requests;

class StoreYourRequest extends BaseFormRequest
{
    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }
}
```

### Add Routes
```php
<?php

use App\Http\Controllers\Api\Mobile\YourController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->group(function () {
    Route::get('/your-endpoint', [YourController::class, 'index']);
});
```

---

## 🎯 Response Helpers

```php
// Success
return $this->successResponse($data, 'Message', 200);

// Error
return $this->errorResponse('Error message', 400);

// Paginated
return $this->paginatedResponse($paginated, 'Message');
```

---

## 📋 Response Format

### Success
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { /* your data */ }
}
```

### Error
```json
{
  "success": false,
  "message": "Error occurred",
  "errors": { /* validation errors */ }
}
```

### Paginated
```json
{
  "success": true,
  "message": "Data retrieved",
  "data": [ /* array of items */ ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

---

## 🔧 Common HTTP Status Codes

```
200 - OK (Success)
201 - Created (Resource created)
400 - Bad Request (Invalid input)
401 - Unauthorized (Auth required)
403 - Forbidden (Permission denied)
404 - Not Found (Resource not found)
422 - Validation Error
500 - Server Error
```

---

## 🚀 URL Patterns

```
GET    /api/v1/mobile/courses           - List (paginated)
GET    /api/v1/mobile/courses/1         - Show
POST   /api/v1/mobile/courses           - Create
PUT    /api/v1/mobile/courses/1         - Update
DELETE /api/v1/mobile/courses/1         - Delete
GET    /api/v1/mobile/courses/search    - Search
```

---

## 📚 Repository Methods

```php
$repo->all()                    // Get all
$repo->paginate($perPage)       // Get paginated
$repo->find($id)                // Get by ID
$repo->create($data)            // Create new
$repo->update($id, $data)       // Update
$repo->delete($id)              // Delete
$repo->getBy($attr, $value)     // Get by attribute
$repo->getAllBy($attr, $value)  // Get all by attribute
$repo->search($query)           // Search
```

---

## ✅ Step-by-Step New Endpoint

1. **Create Form Request**
   - File: `app/Http/Requests/StoreYourRequest.php`
   - Add validation rules

2. **Create Repository**
   - File: `app/Repositories/YourRepository.php`
   - Add custom database methods

3. **Create Service**
   - File: `app/Services/YourService.php`
   - Add business logic

4. **Create Controller**
   - File: `app/Http/Controllers/Api/Mobile/YourController.php`
   - Add action methods

5. **Add Routes**
   - File: `routes/api/v1/mobile.php`
   - Add route definitions

6. **Test**
   - Use Postman or curl
   - Check responses

---

## 🎓 File Naming Conventions

```
Controllers:    UserController, CourseController
Services:       UserService, CourseService
Repositories:   UserRepository, CourseRepository
Forms:          StoreUserRequest, UpdateUserRequest
Models:         User, Course
Migrations:     create_users_table, create_courses_table
```

---

## 🔍 Find & Replace Guide

```
// When moving old controller to new structure:

OLD: namespace App\Http\Controllers;
NEW: namespace App\Http\Controllers\Api\Mobile;

OLD: class UserController extends Controller
NEW: class UserController extends BaseApiController
```

---

## 💪 Pro Tips

✨ Always use dependency injection
✨ Keep services focused on one thing
✨ Use custom repository methods for complex queries
✨ Add proper error messages
✨ Use form requests for validation
✨ Return consistent response format
✨ Test all endpoints
✨ Document your API

---

## 🆘 Troubleshooting

**Problem: Class not found**
→ Run: `composer dump-autoload`

**Problem: Route not working**
→ Check: Middleware, namespace, route file imported

**Problem: Validation not working**
→ Check: Form request rules, request passing to method

**Problem: Repository not injecting**
→ Check: Constructor parameter, type hinting

---

## 📞 Example Calls

### Get All Courses
```bash
curl -X GET http://localhost:8000/api/v1/mobile/courses \
  -H "Accept: application/json"
```

### Create Course
```bash
curl -X POST http://localhost:8000/api/v1/mobile/courses \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"title":"Laravel","price":49.99}'
```

### Update Course
```bash
curl -X PUT http://localhost:8000/api/v1/mobile/courses/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"title":"Advanced Laravel"}'
```

### Delete Course
```bash
curl -X DELETE http://localhost:8000/api/v1/mobile/courses/1 \
  -H "Accept: application/json"
```

---

## 📖 Documentation Files

| File | Purpose |
|------|---------|
| BACKEND_DOCUMENTATION_INDEX.md | Overview |
| BACKEND_STRUCTURE.md | Structure details |
| USAGE_GUIDE.md | How to use |
| API_RESPONSE_FORMAT.md | Response format |
| COMPLETE_PRACTICAL_EXAMPLE.md | Full example |
| COMPLETION_SUMMARY.md | Next steps |
| README_ORGANIZATION.md | Summary |
| BACKEND_QUICK_REFERENCE.md | This file |

---

## 🎯 Bookmark This!

Keep this quick reference handy when developing!

**Happy Coding! ✨**

