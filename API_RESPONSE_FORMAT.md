# API Response Format Standardization

## 📋 موحدة Response Format

جميع API responses يجب أن تتبع هذا الصيغة الموحدة لسهولة التعامل مع الـ Frontend.

### Success Response

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    "id": 1,
    "name": "Ahmed",
    "email": "ahmed@example.com"
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required"],
    "name": ["The name must be at least 3 characters"]
  }
}
```

### Paginated Response

```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [
    { "id": 1, "name": "Course 1" },
    { "id": 2, "name": "Course 2" }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

---

## HTTP Status Codes

| Code | Meaning | Use Case |
|------|---------|----------|
| 200 | OK | Success |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Validation error |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Authorization failed |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Server Error | Internal error |

---

## Response Helper Methods

### في BaseApiController

```php
// ✅ Success
$this->successResponse($data, 'Message', 200);

// ❌ Error
$this->errorResponse('Error message', 400);

// 📄 Paginated
$this->paginatedResponse($paginated, 'Message');
```

---

## Controller Examples

### Example 1: Get User Profile

**Request**: `GET /api/v1/mobile/user/profile`

**Response**:
```json
{
  "success": true,
  "message": "Profile retrieved successfully",
  "data": {
    "id": 1,
    "name": "Ahmed",
    "email": "ahmed@example.com",
    "role": "student",
    "avatar": "https://..."
  }
}
```

### Example 2: Create Course

**Request**: `POST /api/v1/web/courses`

```json
{
  "title": "Laravel Basics",
  "description": "Learn Laravel from scratch",
  "instructor_id": 5
}
```

**Response**:
```json
{
  "success": true,
  "message": "Course created successfully",
  "data": {
    "id": 123,
    "title": "Laravel Basics",
    "slug": "laravel-basics",
    "created_at": "2024-04-10T10:30:00Z"
  }
}
```

### Example 3: List Courses (Paginated)

**Request**: `GET /api/v1/web/courses?page=1&per_page=10`

**Response**:
```json
{
  "success": true,
  "message": "Courses retrieved successfully",
  "data": [
    { "id": 1, "title": "Course 1" },
    { "id": 2, "title": "Course 2" }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 10,
    "total": 25
  }
}
```

### Example 4: Validation Error

**Request**: `POST /api/v1/mobile/user/profile` (missing name)

**Response** (422):
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required"],
    "email": ["The email field is required"]
  }
}
```

### Example 5: Not Found

**Request**: `GET /api/v1/web/courses/999`

**Response** (404):
```json
{
  "success": false,
  "message": "Course not found",
  "errors": null
}
```

---

## Notes

- جميع الـ timestamps يكون بصيغة ISO 8601
- جميع الـ errors تتضمن أكواد HTTP مناسبة
- الـ messages يمكن أن تكون بالعربية أو الإنجليزية حسب الحاجة
- استخدم status codes الصحيحة دائماً
