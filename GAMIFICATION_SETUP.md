# 🎮 Gamification System - Implementation Guide

## ✅ ما تم تطبيقه

### 1. **Migration** 📊

**ملف:** `database/migrations/2026_04_05_add_gamification_to_users_table.php`

أضفنا 3 أعمدة للـ users table:

- `points` (INT) - النقاط الكلية للمستخدم
- `level` (INT) - مستوى المستخدم (يزيد كل 500 نقطة)
- `achievements` (TEXT JSON) - الإنجازات والشارات (اختياري)

### 2. **PointsService** 🎯

**ملف:** `app/Services/PointsService.php`

المميزات:

```php
// Constants
const VIDEO_COMPLETED = 50;        // 50 نقطة عند الانتهاء من فيديو
const QUIZ_PASSED = 100;           // 100 نقطة عند نجاح الاختبار
const LEVEL_THRESHOLD = 500;       // 500 نقطة لرفع مستوى

// Methods
addVideoCompletionPoints($user)     // إضافة نقاط الفيديو
addQuizPassPoints($user)            // إضافة نقاط الاختبار
addPoints($user, $points, $reason)  // إضافة نقاط مخصصة
calculateLevel($points)             // حساب المستوى وفقاً للنقاط
getProgressToNextLevel($user)       // الحصول على تقدم المستخدم
getLeaderboard($limit = 10)         // الحصول على أفضل 10 مستخدمين
resetPoints($user)                  // إعادة تعيين النقاط (للإدارة)
```

### 3. **LeaderboardController** 🏆

**ملف:** `app/Http/Controllers/Api/LeaderboardController.php`

الـ Endpoints:

```php
// Public endpoints
GET /api/leaderboard              // أفضل 10 مستخدمين
  - Query param: ?limit=10        // يمكن تغيير العدد (ماكس 100)

// Protected endpoints (auth:sanctum)
GET /api/leaderboard/me           // ترتيب المستخدم الحالي
GET /api/user/stats               // إحصائيات المستخدم الكاملة
```

### 4. **StudentProgressObserver** 👁️

**ملف:** `app/Observers/StudentProgressObserver.php`

يراقب جدول student_progress ويضيف نقاط تلقائياً عند:

- إتمام الفيديو (watched_percentage = 100%)

### 5. **AppServiceProvider** ⚙️

**ملف:** `app/Providers/AppServiceProvider.php`

تم:

- تسجيل `PointsService` كـ singleton
- تسجيل `StudentProgressObserver` لمراقبة التغييرات

### 6. **Updated Models** 📝

- **User Model:** أضفنا `points`, `level`, `achievements` للـ fillable و casts
- **StudentProgress Model:** بدون تغييرات (العلاقة موجودة بالفعل)

### 7. **API Routes** 🛣️

**ملف:** `routes/api.php`

```
Public:
POST   /api/login
POST   /api/register
GET    /api/leaderboard           🆕

Protected (auth:sanctum):
GET    /api/leaderboard/me        🆕
GET    /api/user/stats            🆕
... (existing routes)
```

---

## 📡 Response Examples

### GET /api/leaderboard

```json
{
    "success": true,
    "data": [
        {
            "rank": 1,
            "user_id": 2,
            "name": "Student User",
            "email": "student@test.com",
            "points": 450,
            "level": 1,
            "progress": {
                "current_level": 1,
                "next_level": 2,
                "points_in_current_level": 450,
                "points_required_for_next": 500,
                "percentage": 90
            }
        }
    ],
    "total": 1,
    "timestamp": "2026-04-05T10:30:00Z"
}
```

### GET /api/leaderboard/me (Protected)

```json
{
    "success": true,
    "data": {
        "rank": 1,
        "user_id": 2,
        "name": "Student User",
        "email": "student@test.com",
        "points": 450,
        "level": 1,
        "progress": {
            "current_level": 1,
            "next_level": 2,
            "points_in_current_level": 450,
            "points_required_for_next": 500,
            "percentage": 90
        }
    },
    "message": "Your ranking"
}
```

### GET /api/user/stats (Protected)

```json
{
    "success": true,
    "data": {
        "user_id": 2,
        "name": "Student User",
        "email": "student@test.com",
        "points": 450,
        "level": 1,
        "progress_to_next_level": {
            "current_level": 1,
            "next_level": 2,
            "points_in_current_level": 450,
            "points_required_for_next": 500,
            "percentage": 90
        },
        "total_users_above": 0
    }
}
```

---

## 🔄 How It Works - الآلية

### عندما يكمل الطالب فيديو:

```
1️⃣ StudentProgress updated (watched_percentage = 100%)
        ↓
2️⃣ StudentProgressObserver detects change
        ↓
3️⃣ Observer calls PointsService->addVideoCompletionPoints()
        ↓
4️⃣ PointsService adds 50 XP
        ↓
5️⃣ If total points >= 500: level increases
        ↓
6️⃣ Response returns with new points/level
```

### مثال من الكود:

```php
// في StudentProgress.php عند التحديث
protected static function boot()
{
    parent::boot();
    static::updated(function ($model) {
        // الـ Observer يعالج هذا تلقائياً
    });
}

// في StudentProgressObserver.php
public function updated(StudentProgress $progress): void
{
    if ($progress->watched_percentage == 100) {
        $this->pointsService->addVideoCompletionPoints($progress->user);
    }
}
```

---

## 🗂️ نظام المستويات

```
الحساب:
Level = (Points ÷ 500) + 1

أمثلة:
0 - 499 Points    → Level 1
500 - 999 Points  → Level 2
1000 - 1499 Points → Level 3
1500+ Points      → Level 4+
```

---

## 📊 قاعدة البيانات

### جدول users (مع الأعمدة الجديدة)

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255),
    role ENUM('admin', 'instructor', 'student') DEFAULT 'student',
    subscription_status VARCHAR(50),
    subscription_until TIMESTAMP NULL,
    points INT UNSIGNED DEFAULT 0,           🆕
    level INT UNSIGNED DEFAULT 1,            🆕
    achievements JSON NULL,                  🆕
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🧪 Testing the System

### 1. تشغيل المشروع

```bash
# Backend
cd backend
php artisan migrate
php artisan db:seed
php artisan serve

# Frontend (in another terminal)
cd frontend
npm run dev
```

### 2. اختبار الـ Endpoints

**الحصول على لوحة الترتيب:**

```bash
curl http://localhost:8000/api/leaderboard
```

**الحصول على ترتيب المستخدم:**

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/leaderboard/me
```

**الحصول على إحصائيات المستخدم:**

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/user/stats
```

### 3. عبر Postman

1. **Login أولاً:**
    - POST `http://localhost:8000/api/login`
    - Body: `{"email": "student@test.com", "password": "password"}`
    - احفظ الـ token

2. **اختبر الـ endpoints:**
    - Header: `Authorization: Bearer {token}`

---

## 🚀 التطوير المستقبلي

### إضافات مخطط لها:

- [ ] System badges/achievements
- [ ] Streak tracking (أيام متتالية)
- [ ] Social features (following, challenges)
- [ ] XP multipliers (weekend bonuses, etc)
- [ ] Leaderboard filters (weekly, monthly, all-time)
- [ ] Admin dashboard for points management
- [ ] Notification system on level-up

---

## ⚙️ ملفات تم تعديلها/إنشاؤها

✅ Created:

- `database/migrations/2026_04_05_add_gamification_to_users_table.php`
- `app/Services/PointsService.php`
- `app/Http/Controllers/Api/LeaderboardController.php`
- `app/Observers/StudentProgressObserver.php`

✏️ Updated:

- `app/Models/User.php` - fillable & casts
- `app/Providers/AppServiceProvider.php` - register service & observer
- `routes/api.php` - add leaderboard routes

---

## 🎯 Next Steps

1. **Run migrations:**

    ```bash
    php artisan migrate
    ```

2. **Test the system:**
    - Login as student
    - Watch a video (update progress to 100%)
    - Check points/levels updated
    - Check leaderboard

3. **Integrate with Frontend:**
    - Add leaderboard component
    - Show user stats on dashboard
    - Display level-up animations

---

**✅ Status: READY TO DEPLOY 🚀**
