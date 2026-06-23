# 🚀 LazyCollections Guide - معالجة البيانات الضخمة بكفاءة

## 📋 جدول المحتويات
1. [ما هي LazyCollections؟](#ما-هي-lazycollections)
2. [الـ Use Cases](#الـ-use-cases)
3. [الفرق بين get() و lazy()](#الفرق-بين-get--و-lazy)
4. [Methods الأساسية](#methods-الأساسية)
5. [أمثلة عملية](#أمثلة-عملية)
6. [Best Practices](#best-practices)
7. [Performance Tips](#performance-tips)

---

## ما هي LazyCollections؟

**LazyCollections** هي طريقة ذكية لمعالجة البيانات الضخمة بدون تحميلها كلها في الذاكرة دفعة واحدة.

```
❌ get()          → تحمّل 1M record = 1GB RAM ❌ يتوقف المعالج
✅ lazy()         → معالجة chunks = 5MB RAM ✅ معالجة فورية
```

---

## الـ Use Cases

### ✅ استخدم `lazy()` عندما:
- تحتاج لمعالجة مليون record أو أكثر
- تريد تصدير بيانات (CSV, JSON, Excel)
- تحتاج لـ bulk operations (update/delete)
- تريد معالجة البيانات مع callbacks
- تحتاج لمعالجة فورية بدون تأخير
- الذاكرة محدودة (shared hosting)

### ❌ تجنب `lazy()` عندما:
- البيانات أقل من 1000 record
- تحتاج للـ pagination (استخدم `paginate()`)
- تحتاج لـ complex sorting/filtering قبل المعالجة
- تحتاج للوصول المتكرر للـ collection

---

## الفرق بين get() و lazy()

```php
// ❌ تحميل كل البيانات (Bad for large datasets)
$courses = Course::all();  // ~1GB RAM لـ 1M record
foreach ($courses as $course) {
    echo $course->title;
}

// ✅ معالجة بـ chunks (Good for large datasets)
$courses = Course::lazy();  // ~5MB RAM فقط!
$courses->each(function ($course) {
    echo $course->title;
});
```

---

## Methods الأساسية

### 1️⃣ `lazy()` - Get all as lazy
```php
// من BaseRepository
$courses = $this->repository->lazy();
// أو مباشرة من Model
$courses = Course::lazy(1000);  // chunk size = 1000
```

### 2️⃣ `lazyBy()` - Get by attribute
```php
$activeCourses = $this->repository->lazyBy('status', 'active');
$instructorCourses = $this->repository->lazyBy('instructor_id', 5);
```

### 3️⃣ `eachLazy()` - Process with callback
```php
$this->repository->eachLazy(function ($course) {
    // معالجة كل record مباشرة بدون تحميل الكل
    $course->update(['price' => $course->price * 1.1]);
});
```

### 4️⃣ `lazyPluck()` - Extract column efficiently
```php
// استخراج الأسماء فقط
$titles = $this->repository->lazyPluck('title');

// مع key
$titlesByIds = $this->repository->lazyPluck('title', 'id');
```

### 5️⃣ `lazyFilter()` - Filter efficiently
```php
$expensive = $this->repository->lazyFilter(
    fn($course) => $course->price > 1000
);
```

### 6️⃣ `lazyMap()` - Transform efficiently
```php
$transformed = $this->repository->lazyMap(
    fn($course) => [
        'id' => $course->id,
        'title' => $course->title,
        'price' => $course->price * 1.1
    ]
);
```

### 7️⃣ `exportLazy()` - Export efficiently
```php
$data = $this->repository->exportLazy(['id', 'title', 'price']);
```

### 8️⃣ `chunk()` - Process in fixed chunks
```php
$this->repository->chunk(1000, function ($courses) {
    // معالجة 1000 record في التكرار
    // ثم الكود يُحرر الذاكرة تلقائياً
});
```

---

## أمثلة عملية

### 📊 مثال 1: تصدير 1 مليون كورس إلى CSV

```php
// ✅ الطريقة الصحيحة - بدون crash
$data = $this->repository->exportLazy();

return response()->streamDownload(function () use ($data) {
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Title', 'Price']);

    $data->each(function ($course) use ($out) {
        fputcsv($out, [$course->id, $course->title, $course->price]);
    });

    fclose($out);
}, 'courses.csv');
```

### 🔄 مثال 2: تحديث أسعار (150,000 كورس)

```php
// ✅ تحديث فوري بدون استهلاك رام
$this->repository->eachLazy(function ($course) {
    $course->update(['price' => $course->price * 1.15]);
    echo $course->id . " updated\n";
});
```

### 🔍 مثال 3: بحث في مليون record

```php
$keyword = 'laravel';

$results = $this->repository->lazy(['id', 'title'])
    ->filter(fn($course) => stripos($course->title, $keyword) !== false)
    ->take(50);  // الـ 50 نتيجة الأولى فقط

return response()->json($results->toArray());
```

### 💾 مثال 4: Warm Cache في 30 ثانية

```php
// بدلاً من أن يأخذ دقائق
$titles = $this->repository->lazyActiveTitles();
$titles->each(fn($title) => 
    Cache::put("title:{$title->id}", $title, now()->addDays(7))
);
```

### 📈 مثال 5: احسب الإحصائيات

```php
$courses = $this->repository->lazyByCategory(5);

$stats = [
    'count' => $courses->count(),
    'avg_price' => $courses->pluck('price')->avg(),
    'min_price' => $courses->pluck('price')->min(),
    'max_price' => $courses->pluck('price')->max(),
];

return response()->json($stats);
```

---

## Best Practices

### ✅ DO: استخدم Chunks المناسبة

```php
// الـ default = 1000
// للبيانات الخفيفة (IDs only)
$this->repository->setChunkSize(5000)->lazy(['id', 'title']);

// للبيانات الثقيلة (relations)
$this->repository->setChunkSize(100)->lazy();
```

### ✅ DO: استخدم Callbacks للمعالجة الفورية

```php
$this->repository->lazyMap(function ($course) {
    return [
        'id' => $course->id,
        'title' => $course->title
    ];
})->each(function ($item) {
    // معالجة فورية لكل item
});
```

### ❌ DON'T: تجنب toArray() على البيانات الضخمة

```php
// ❌ هذا يُحمّل كل شيء في الذاكرة!
$data = $this->repository->lazy()->toArray();

// ✅ معالجة streaming بدلاً من toArray()
$this->repository->lazyExport()->each(function ($course) {
    sendToFile($course);
});
```

### ❌ DON'T: تجنب Multiple Iterations

```php
// ❌ سيء - يُنفذ Query مرتين
$lazy = $this->repository->lazy();
$lazy->count();  // Query 1
$lazy->each(fn($c) => ...);  // Query 2

// ✅ جيد - حفظ الـ result
$lazy = $this->repository->lazy();
$count = $lazy->count();
$lazy->each(fn($c) => ...);
```

### ✅ DO: استخدم Transactions للتحديثات

```php
DB::transaction(function () {
    $this->repository->eachLazy(function ($course) {
        $course->update(['status' => 'archived']);
    });
});
```

---

## Performance Tips

### 🚀 Tip 1: استخدم Select للأعمدة المحتاجة فقط

```php
// ✅ سريع - 5 أعمدة فقط
$this->repository->lazy(['id', 'title', 'price', 'status', 'category_id']);

// ❌ بطيء - تحميل كل الأعمدة
$this->repository->lazy();
```

### 🚀 Tip 2: أضف Database Index

```sql
-- للبحث السريع
ALTER TABLE courses ADD INDEX status_idx (status);
ALTER TABLE courses ADD INDEX instructor_id_idx (instructor_id);
ALTER TABLE courses ADD INDEX created_at_idx (created_at);
```

### 🚀 Tip 3: استخدم Streaming للـ Large Files

```php
// بدلاً من:
$data = $this->repository->lazy()->toArray();
return response()->json($data);  // قد تحتاج دقائق!

// استخدم:
return response()->streamDownload(function () {
    $this->repository->lazyExport()->each(function ($course) {
        echo json_encode($course) . "\n";
    });
}, 'courses.jsonl');
```

### 🚀 Tip 4: استخدم Memory Limit المناسب

```php
// في .env أو قبل الـ migration
MEMORY_LIMIT=512M  // للـ lazy processing normal sized

// للـ bulk operations الكبيرة جداً
MEMORY_LIMIT=2048M  // 2GB
```

### 🚀 Tip 5: استخدم Queue للـ Bulk Operations

```php
// بدلاً من معالجة مباشرة:
Bus::batch([
    new UpdateCoursePricesJob($start, $end),
    new UpdateCoursePricesJob($end + 1, $end + $chunkSize),
    // ...
])->dispatch();
```

---

## مقارنة الأداء

| Operation | get() | lazy() | الفرق |
|-----------|-------|--------|--------|
| 100K records | 50MB RAM | 5MB RAM | 10x أقل |
| 1M records | 500MB RAM | 5MB RAM | 100x أقل |
| Time (export) | 2 دقيقة | 30 ثانية | 4x أسرع |
| CSV generation | يتوقف | streaming | ✅ |
| Bulk update | OOM error | ✅ يعمل | حل النسيان |

---

## استخدام في الـ API

### تحديث البيانات الديناميكية
```php
POST /api/v1/courses/bulk-update-prices
{
    "multiplier": 1.1,
    "status": "active"
}
```

### تصدير البيانات
```bash
GET /api/v1/courses/export/json     # JSON
GET /api/v1/courses/export/csv      # CSV Stream
```

### البحث السريع
```bash
GET /api/v1/courses/search-lazy?q=laravel
```

### الإحصائيات
```bash
GET /api/v1/courses/category/5/stats
```

### تحضير الـ Cache
```bash
POST /api/v1/courses/warm-cache
```

---

## الخلاصة

```
LazyCollections = الحل الأمثل للبيانات الضخمة
✅ استهلاك رام قليل جداً
✅ معالجة فورية بدون تأخير
✅ لا توقف للـ server
✅ مناسب للـ Shared Hosting
```

استخدمها في:
- التصدير (CSV, JSON, Excel)
- الـ Bulk Operations (update, delete)
- البحث في مليون record
- معالجة الـ Reports
- Caching الضخم
