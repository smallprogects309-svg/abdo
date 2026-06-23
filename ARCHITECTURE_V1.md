# 🚀 High-Performance Backend Architecture - V1

## 📚 Overview

هذا الدليل يشرح البنية المحسّنة للـ Backend لتحقيق أقصى أداء مع Web و Mobile.

---

## 🏗️ Architecture Layers

```
Request
  ↓
Route (routes/api/v1/)
  ↓
Form Request (Validation + Sanitization)
  ↓
Action (Business Logic - Single Responsibility)
  ↓
Repository (with Eager Loading - منع N+1)
  ↓
Model (Eloquent)
  ↓
Database / Cache
  ↓
API Resource (Lightweight JSON)
  ↓
Response (Unified JSON structure)
```

---

## 📁 File Structure

```
app/
├── Http/
│   ├── Controllers/Api/V1/              👈 Controllers V1
│   ├── Requests/Api/V1/                 👈 Form Requests V1
│   └── Resources/Api/V1/                👈 API Resources V1
├── DTOs/                                 👈 Data Transfer Objects
│   ├── BaseDTO.php
│   └── CourseDTO.php
├── Actions/                              👈 Single Responsibility Actions
│   ├── BaseAction.php
│   ├── StoreCourseAction.php
│   ├── UpdateCourseAction.php
│   └── DeleteCourseAction.php
├── Repositories/                         👈 Database Access with Eager Loading
│   ├── BaseRepository.php
│   └── CourseRepository.php
├── Contracts/                            👈 Interfaces for flexibility
│   ├── RepositoryInterface.php
│   └── CacheServiceInterface.php
├── Traits/
│   ├── ApiResponse.php                   👈 Unified Response
└── Observers/
    └── CourseObserver.php                👈 Cache invalidation
```

---

## 🔑 Key Components

### 1. DTOs (Data Transfer Objects)

**ملف**: `app/DTOs/CourseDTO.php`

```php
$dto = CourseDTO::fromArray([
    'title' => 'Laravel Basics',
    'description' => '...',
    'instructor_id' => 1,
    // ...
]);
```

**الفوائد**:
- ✅ Type safety
- ✅ Data consistency
- ✅ Easy validation
- ✅ Document structure

---

### 2. Actions (Single Responsibility)

**ملف**: `app/Actions/StoreCourseAction.php`

```php
$action = app(StoreCourseAction::class);
$course = $action->execute($dto);
```

**الفوائد**:
- ✅ One job = One action
- ✅ Reusable logic
- ✅ Easy to test
- ✅ Clear intent

---

### 3. Repository with Eager Loading

**ملف**: `app/Repositories/CourseRepository.php`

```php
// منع N+1 queries
$repository->with(['instructor', 'category'])
    ->withCount(['enrollments'])
    ->paginate(15);
```

**الفوائد**:
- ✅ منع N+1 queries
- ✅ Database optimization
- ✅ Consistent eager loading
- ✅ Better performance

---

### 4. API Resources

**ملف**: `app/Http/Resources/Api/V1/CourseResource.php`

```php
return response()->json(
    new CourseResource($course),
    200
);
```

**الفوائد**:
- ✅ Lightweight JSON
- ✅ Mobile-optimized
- ✅ Consistent format
- ✅ Easy transformation

---

### 5. Observers for Cache

**ملف**: `app/Observers/CourseObserver.php`

```php
// تلقائياً عند تحديث الـ Course
public function updated(Course $course)
{
    Cache::tags(['courses'])->flush();
}
```

**الفوائد**:
- ✅ Automatic cache invalidation
- ✅ Always fresh data
- ✅ No manual cache management
- ✅ Better performance

---

### 6. Unified Response Trait

**ملف**: `app/Traits/ApiResponse.php`

```php
// Success
$this->success($data, 'Message', 200);

// Paginated
$this->paginated($collection, 'Message');

// Error
$this->error('Message', 400);
```

---

## 💻 Complete Example

### Step 1: Create DTO

```php
$dto = CourseDTO::fromArray($request->sanitized());
```

### Step 2: Execute Action

```php
$course = app(StoreCourseAction::class)->execute($dto);
```

### Step 3: Return Resource

```php
return $this->created(
    new CourseResource($course),
    'Course created'
);
```

---

## 📊 Request/Response Flow

### Store Course

**Request**:
```bash
POST /api/v1/courses
Content-Type: application/json

{
  "title": "Laravel Basics",
  "description": "Learn Laravel",
  "instructor_id": 1,
  "category_id": 2,
  "price": 49.99,
  "duration": 40,
  "level": "beginner"
}
```

**Process**:
1. StoreCourseRequest validates & sanitizes
2. Convert to CourseDTO
3. StoreCourseAction executes with DB transaction
4. CourseObserver invalidates cache
5. CourseResource transforms for response

**Response**:
```json
{
  "status": "success",
  "message": "Course created successfully",
  "data": {
    "id": 1,
    "title": "Laravel Basics",
    "slug": "laravel-basics-abc123",
    "description": "Learn Laravel",
    "price": 49.99,
    "final_price": 49.99,
    "instructor": {
      "id": 1,
      "name": "Ahmed"
    },
    "category": {
      "id": 2,
      "name": "Web Development"
    },
    "created_at": "2024-04-10T10:30:00Z"
  }
}
```

---

## ⚡ Performance Optimizations

### 1. Eager Loading

```php
// ❌ Bad - N+1 queries
$courses = Course::all();
foreach ($courses as $course) {
    echo $course->instructor->name;
}

// ✅ Good - 1 query
$courses = $repository->with(['instructor'])->all();
```

### 2. Caching

```php
// Cache popular courses for 6 hours
Cache::tags(['courses'])->remember(
    'courses:popular',
    now()->addHours(6),
    fn() => $repository->getPopular(10)
);
```

### 3. Lightweight JSON

```php
// API Resources only return needed fields
return [
    'id' => $course->id,
    'title' => $course->title,
    'price' => (float) $course->price,
    // Skip: content, prerequisites, etc for list views
];
```

---

## 🔐 Data Consistency

### DTOs Ensure Type Safety

```php
$dto->price;            // float
$dto->instructor_id;    // int
$dto->learning_outcomes; // array
```

### Validation at Multiple Levels

1. **Form Request** - HTTP validation
2. **DTO** - Type validation
3. **Action** - Business logic validation
4. **Database** - Model validation

---

## 🧪 Testing Examples

### Unit Test for Action

```php
public function test_store_course_action()
{
    $dto = new CourseDTO();
    $dto->title = 'Test Course';
    $dto->instructor_id = 1;
    // ...

    $action = app(StoreCourseAction::class);
    $course = $action->execute($dto);

    $this->assertDatabaseHas('courses', [
        'title' => 'Test Course'
    ]);
}
```

### Feature Test for API

```php
public function test_store_course_api()
{
    $response = $this->postJson('/api/v1/courses', [
        'title' => 'Laravel',
        'instructor_id' => 1,
        // ...
    ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'status', 'message', 'data' => [
            'id', 'title', 'price', 'instructor'
        ]
    ]);
}
```

---

## 📈 Scalability

### Easy to Add New Features

**New Feature: Publish Course**

1. Create `PublishCourseAction`
2. Create `UpdateCourseRequest`
3. Add route
4. Done!

### Support Multiple Versions

```
routes/api/v1/ (current)
routes/api/v2/ (future)
routes/api/v3/ (future)
```

---

## 🚀 API Endpoints

### Courses

```
GET    /api/v1/courses                  - List all
GET    /api/v1/courses/{id}             - Show
POST   /api/v1/courses                  - Create
PUT    /api/v1/courses/{id}             - Update
DELETE /api/v1/courses/{id}             - Delete
GET    /api/v1/courses/search?q=        - Search
GET    /api/v1/courses/category/{id}    - By category
GET    /api/v1/courses/popular          - Popular
GET    /api/v1/courses/trending         - Trending
```

---

## ✅ Checklist

- ✅ DTOs for data transfer
- ✅ Actions for business logic
- ✅ Repository with eager loading
- ✅ API Resources for JSON
- ✅ Observers for cache
- ✅ Unified response format
- ✅ Form validation
- ✅ V1 versioning
- ✅ High performance

---

## 🎓 Best Practices

1. **Always use DTOs** - Type safety
2. **Always eager load** - منع N+1
3. **Always use Actions** - Single responsibility
4. **Always cache** - Better performance
5. **Always validate** - Data integrity
6. **Always use Resources** - Consistent JSON
7. **Always tag cache** - Easy invalidation
8. **Always test** - Quality assurance

---

## 📝 Next Steps

1. Create Models if not existing
2. Create Migrations
3. Register Observers in AppServiceProvider
4. Add routes
5. Test all endpoints
6. Deploy to staging
7. Monitor performance

---

*High-Performance Architecture Ready! 🚀*

