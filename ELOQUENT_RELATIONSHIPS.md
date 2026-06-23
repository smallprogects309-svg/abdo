# 🏗️ Eloquent Relationships Setup

## ✅ Complete Relationship Diagram

```
┌─────────┐                                    ┌──────────────┐
│  User   │◄─────────────┐                    │   Course     │
└─────────┘              │ instructor_id      └──────────────┘
    │                    │                              ▲
    │ user_id          ┌─┴──────────────┐              │
    ├──hasMany──→ Enrollment          │              │
    │                 └─────────────────┴──course_id──┘
    │
    ├──hasMany──→ StudentProgress
    │
    ├──hasManyThrough (via Enrollment)──→ Courses (enrolled)
    │
    └──hasMany──→ Course (as instructor_id)


Course
    │ course_id
    └──hasMany──→ Lesson
                    │ lesson_id
                    ├──hasMany──→ Quiz
                    │
                    └──hasMany──→ StudentProgress
```

---

## 📋 Detailed Relationships

### **User Model**

#### Relationships ✅

```php
// HasMany: User has many enrollments
public function enrollments()
{
    return $this->hasMany(Enrollment::class);
}

// BelongsToMany: User enrolled in many courses (through enrollments)
public function courses()
{
    return $this->belongsToMany(Course::class, 'enrollments');
}

// HasMany: User as instructor has many courses
public function instructorCourses()
{
    return $this->hasMany(Course::class, 'instructor_id');
}

// HasMany: User has many progress records
public function progress()
{
    return $this->hasMany(StudentProgress::class);
}
```

#### $fillable Array ✅

```php
protected $fillable = [
    'name',
    'email',
    'phone',
    'password',
    'role',              // student, admin, instructor
    'subscription_status', // active, inactive
    'subscription_until',  // nullable datetime
];
```

#### Casts ✅

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'subscription_until' => 'datetime',
    ];
}
```

#### Helper Methods ✅

```php
public function isStudent(): bool
public function isAdmin(): bool
public function isInstructor(): bool
```

---

### **Course Model**

#### Relationships ✅

```php
// BelongsTo: Course belongs to User (instructor)
public function instructor()
{
    return $this->belongsTo(User::class, 'instructor_id');
}

// HasMany: Course has many lessons
public function lessons()
{
    return $this->hasMany(Lesson::class)->orderBy('order_position');
}

// HasMany: Course has many enrollments
public function enrollments()
{
    return $this->hasMany(Enrollment::class);
}

// BelongsToMany: Course has many students (through enrollments)
public function students()
{
    return $this->belongsToMany(User::class, 'enrollments');
}
```

#### $fillable Array ✅

```php
protected $fillable = [
    'title',
    'description',
    'instructor_id',     // Foreign key to users table
    'instructor_name',   // Denormalized for convenience
    'price',
    'cover_image',
    'slug',
];
```

---

### **Lesson Model**

#### Relationships ✅

```php
// BelongsTo: Lesson belongs to Course
public function course()
{
    return $this->belongsTo(Course::class);
}

// HasMany: Lesson has many quizzes
public function quizzes()
{
    return $this->hasMany(Quiz::class)->orderBy('order_position');
}

// HasMany: Lesson has many student progress records
public function studentProgress()
{
    return $this->hasMany(StudentProgress::class);
}
```

#### $fillable Array ✅

```php
protected $fillable = [
    'course_id',
    'title',
    'description',
    'video_url',
    'pdf_url',
    'order_position',
    'duration_minutes',
];
```

---

### **Quiz Model**

#### Relationships ✅

```php
// BelongsTo: Quiz belongs to Lesson
public function lesson()
{
    return $this->belongsTo(Lesson::class);
}
```

#### $fillable Array ✅

```php
protected $fillable = [
    'lesson_id',
    'question',
    'options',           // Stored as JSON
    'correct_answer',
    'order_position',
];
```

#### Casts ✅

```php
protected $casts = [
    'options' => 'json',
];
```

---

### **StudentProgress Model**

#### Relationships ✅

```php
// BelongsTo: StudentProgress belongs to User
public function user()
{
    return $this->belongsTo(User::class);
}

// BelongsTo: StudentProgress belongs to Lesson
public function lesson()
{
    return $this->belongsTo(Lesson::class);
}
```

#### $fillable Array ✅

```php
protected $fillable = [
    'user_id',
    'lesson_id',
    'watched_percentage',
    'completed_at',
];
```

#### Casts ✅

```php
protected $casts = [
    'completed_at' => 'datetime',
];
```

#### Helper Methods ✅

```php
public function isCompleted(): bool
{
    return $this->completed_at !== null;
}
```

---

### **Enrollment Model** (Subscription)

#### Relationships ✅

```php
// BelongsTo: Enrollment belongs to User
public function user()
{
    return $this->belongsTo(User::class);
}

// BelongsTo: Enrollment belongs to Course
public function course()
{
    return $this->belongsTo(Course::class);
}
```

#### $fillable Array ✅

```php
protected $fillable = [
    'user_id',
    'course_id',
    'enrolled_date',
    'expires_at',
];
```

#### Casts ✅

```php
protected $casts = [
    'enrolled_date' => 'datetime',
    'expires_at' => 'datetime',
];
```

#### Helper Methods ✅

```php
public function isActive(): bool
{
    return $this->expires_at === null || $this->expires_at->isFuture();
}
```

---

## 🔄 Relationship Usage Examples

### Get All Student's Enrollments

```php
$user = User::find(1);
$enrollments = $user->enrollments()->get();
```

### Get All Courses a Student is Enrolled In

```php
$user = User::find(1);
$courses = $user->courses()->get();
```

### Get All Lessons in a Course

```php
$course = Course::find(1);
$lessons = $course->lessons()->get();
```

### Get Course Instructor

```php
$course = Course::find(1);
$instructor = $course->instructor()->get();
// or
$instructorName = $course->instructor->name;
```

### Get All Courses an Instructor Teaches

```php
$instructor = User::find(1);
$courses = $instructor->instructorCourses()->get();
```

### Get Student's Progress in a Lesson

```php
$lesson = Lesson::find(1);
$progress = StudentProgress::where('user_id', 1)
    ->where('lesson_id', $lesson->id)
    ->first();

if ($progress->isCompleted()) {
    // Lesson is completed
}
```

### Get All Quizzes in a Lesson

```php
$lesson = Lesson::find(1);
$quizzes = $lesson->quizzes()->get();
```

### Get All Students in a Course

```php
$course = Course::find(1);
$students = $course->students()->get();
```

### Check if Enrollment is Active

```php
$enrollment = Enrollment::find(1);
if ($enrollment->isActive()) {
    // Subscription is still active
}
```

---

## 📊 New Migration Added

### File

```
2024_01_02_000000_add_instructor_id_to_courses_table.php
```

### Changes

- Added `instructor_id` foreign key to `courses` table
- References `id` in `users` table
- OnDelete: SET NULL (if instructor is deleted, course instructor_id becomes null)

### SQL

```sql
ALTER TABLE courses ADD COLUMN instructor_id BIGINT UNSIGNED NULLABLE;
ALTER TABLE courses ADD FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE SET NULL;
```

---

## ✅ Validation Checklist

- [x] User.php - complete with all relationships
- [x] Course.php - complete with instructor relationship
- [x] Lesson.php - complete with relationships
- [x] Quiz.php - complete with relationship
- [x] Enrollment.php - complete with relationships (subscription model)
- [x] StudentProgress.php - complete with relationships
- [x] All $fillable arrays updated
- [x] All casts properly defined
- [x] Helper/accessor methods added
- [x] New migration for instructor_id created

---

## 🚀 Ready for Use

All models are now properly configured with:
✅ Eloquent relationships (HasMany, BelongsTo, BelongsToMany)
✅ Mass assignment protection ($fillable)
✅ Type casting ($casts)
✅ Helper methods (isActive(), isCompleted(), etc.)

You can now use these relationships in your API controllers and services!
