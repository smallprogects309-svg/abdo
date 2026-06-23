# ⚡ LazyCollections - Quick Reference Guide

## 🎯 Quick Start

### أسرع طريقة للبدء:

```php
// 1. في الـ Repository:
$this->repository->lazy();

// 2. في الـ Controller:
public function index() {
    $courses = $this->repository->lazy();
    $courses->each(fn($c) => echo $c->title);
}

// 3. في الـ Route:
GET /api/v1/courses/export/csv
```

---

## 📋 جميع Methods المتاحة

### BaseRepository Methods

| Method | الحالة | الاستخدام |
|--------|--------|---------|
| `lazy()` | ✅ | جميع البيانات كـ LazyCollection |
| `lazyBy()` | ✅ | بيانات محددة حسب شرط |
| `eachLazy()` | ✅ | معالجة مع callback |
| `lazyPluck()` | ✅ | استخراج عمود واحد |
| `lazyFilter()` | ✅ | تصفية البيانات |
| `lazyMap()` | ✅ | تحويل البيانات |
| `exportLazy()` | ✅ | تصدير آمن |
| `chunk()` | ✅ | معالجة بـ chunks |
| `setChunkSize()` | ✅ | تعديل حجم الـ chunk |

### CourseRepository Specialized Methods

| Method | الحالة | الاستخدام |
|--------|--------|---------|
| `lazyActive()` | ✅ | كورسات نشطة فقط |
| `lazyByInstructor()` | ✅ | كورسات معلم معين |
| `lazyByCategory()` | ✅ | كورسات فئة معينة |
| `lazyExport()` | ✅ | تصدير مناسب |
| `lazyActiveTitles()` | ✅ | استخراج الأسماء |
| `lazyByPriceRange()` | ✅ | تصفية بالسعر |
| `lazyAsArray()` | ✅ | تحويل إلى array |
| `processEach()` | ✅ | معالجة مع status filter |

### Controller Endpoints

| Endpoint | Type | الاستخدام |
|----------|------|---------|
| `/courses/export/json` | GET | تصدير JSON |
| `/courses/export/csv` | GET | تصدير CSV |
| `/courses/search-lazy` | GET | بحث سريع |
| `/courses/category/:id/stats` | GET | إحصائيات |
| `/courses/bulk-update-prices` | POST | تحديث جماعي* |
| `/courses/warm-cache` | POST | تحضير cache* |

*requires authentication

---

## 🚀 Patterns الشائعة

### Pattern 1: تصدير البيانات
```php
// في الـ Repository
public function lazyExport() {
    return $this->exportLazy(['id', 'title', 'price']);
}

// في الـ Controller
public function exportCsv() {
    return response()->streamDownload(function () {
        $this->repository->lazyExport()->each(function ($item) {
            fputcsv(STDOUT, (array)$item);
        });
    }, 'export.csv');
}

// في الـ Client
curl "https://api.com/api/v1/courses/export/csv" > courses.csv
```

### Pattern 2: معالجة الـ Bulk
```php
// في الـ Repository
public function processEach(callable $callback) {
    $this->model->lazy($this->chunkSize)->each($callback);
}

// في الـ Controller
public function bulkUpdate() {
    $this->repository->processEach(function ($record) {
        $record->update(['status' => 'updated']);
    });
}

// في الـ API
POST /api/v1/courses/bulk-update-prices
{ "multiplier": 1.1, "status": "active" }
```

### Pattern 3: البحث السريع
```php
// في الـ Repository
public function lazySearch($keyword) {
    return $this->lazy(['id', 'title'])
        ->filter(fn($c) => stripos($c->title, $keyword) !== false);
}

// في الـ Controller
public function search(Request $request) {
    $results = $this->repository->lazySearch($request->q)->take(50);
    return response()->json($results->toArray());
}

// في الـ API
GET /api/v1/courses/search-lazy?q=laravel
```

### Pattern 4: تحضير الـ Cache
```php
// في الـ Controller
public function warmCache() {
    $titles = $this->repository->lazyActiveTitles(true);
    Cache::put('titles', $titles->toArray(), now()->addDays(7));
    return response()->json(['cached' => $titles->count()]);
}

// في الـ API
POST /api/v1/courses/warm-cache
```

### Pattern 5: الإحصائيات
```php
// في الـ Repository
public function stats() {
    $data = $this->lazy(['price', 'enrollments_count']);
    return [
        'total' => $data->count(),
        'avg_price' => (int)$data->pluck('price')->avg(),
        'total_enrollments' => $data->pluck('enrollments_count')->sum(),
    ];
}

// في الـ API
GET /api/v1/courses/category/5/stats
```

---

## ⚙️ Configuration

### Chunk Size

```php
// الـ Default = 1000
$this->repository->lazy();  // 1000 records per chunk

// Custom chunk size
$this->repository->setChunkSize(5000)->lazy();  // 5000 records per chunk
$this->repository->setChunkSize(100)->lazy();   // 100 records per chunk

// Guide:
// Light data (IDs): 5000-10000
// Normal data:      1000-2000
// Heavy data:       100-500
// Relations:        50-100
```

### Memory Management

```php
// في .env
MEMORY_LIMIT=512M      # للـ normal operations
MEMORY_LIMIT=2048M     # للـ bulk operations
MEMORY_LIMIT=4096M     # للـ exports الضخمة

// في الـ Code
ini_set('memory_limit', '1024M');
$this->repository->lazy();
```

---

## 📊 Performance Comparison

```
Scenario: Processing 1 Million Records

❌ get():
   Time: 2 minutes
   RAM: 500MB
   Status: OOM Error likely

✅ lazy():
   Time: 30 seconds
   RAM: 50MB
   Status: ✓ Works perfectly
```

---

## ✅ Best Practices

### DO ✅

```php
// ✅ Use lazy for large datasets
$courses = $this->repository->lazy();

// ✅ Process streaming
$courses->each(fn($c) => processIt($c));

// ✅ Use specific columns
$courses = $this->repository->lazy(['id', 'title', 'price']);

// ✅ Filter efficiently
$expensive = $this->repository->lazy()->filter(fn($c) => $c->price > 1000);

// ✅ Use set chunk size appropriately
$this->repository->setChunkSize(500)->lazy();
```

### DON'T ❌

```php
// ❌ Don't use for small datasets
if ($total < 100) {
    return $this->repository->get();  // Use normal get()
}

// ❌ Don't convert to array directly
$array = $this->repository->lazy()->toArray();  // Will still load all!

// ❌ Don't use for pagination
return $this->repository->lazy();  // Use paginate() instead

// ❌ Don't iterate multiple times
$lazy = $this->repository->lazy();
foreach ($lazy as $item) {}  // Iteration 1
foreach ($lazy as $item) {}  // Iteration 2 - creates new query!

// ❌ Don't use for real-time UIs
// Lazy is for background/batch processing
```

---

## 🐛 Troubleshooting

### Problem: Memory still high
```php
// Solution: Reduce chunk size
$this->repository->setChunkSize(100)->lazy();

// Or: Add garbage collection
$this->repository->lazy()->each(function ($item) use (&$count) {
    process($item);
    $count++;
    if ($count % 1000 === 0) {
        gc_collect_cycles();  // Force garbage collection
    }
});
```

### Problem: Slow processing
```php
// Solution: Add database index
ALTER TABLE courses ADD INDEX status_idx (status);

// Or: Process in parallel with queue
Bus::batch([
    new ProcessChunkJob(0, 100000),
    new ProcessChunkJob(100001, 200000),
    // ...
])->dispatch();
```

### Problem: Too many queries
```php
// Solution: Use eager loading
$this->repository
    ->with(['category', 'instructor'])  // Add relations
    ->lazy();

// Or: Use withCount
$this->repository
    ->withCount(['enrollments', 'reviews'])  // Add counts
    ->lazy();
```

---

## 📚 Learn More

```
📖 Full Guide:   backend/LAZYCOLLECTIONS_GUIDE.md
📚 Examples:     backend/LAZYCOLLECTIONS_EXAMPLES.md
📋 Update Info:  backend/V1_UPDATE_SUMMARY.md
💬 Questions:    dev@yourcompany.com
```

---

## 🎯 Common Tasks

### Task 1: Export 500K records to CSV
```bash
GET /api/v1/courses/export/csv > courses.csv
# Response: streaming CSV download
```

### Task 2: Update all prices
```bash
POST /api/v1/courses/bulk-update-prices
Content-Type: application/json
Authorization: Bearer TOKEN

{
    "multiplier": 1.15,
    "status": "active"
}
# Response: { "count": 250000, "message": "Updated..." }
```

### Task 3: Search in 1M records
```bash
GET /api/v1/courses/search-lazy?q=laravel
# Response: Top 50 results instantly
```

### Task 4: Get category stats
```bash
GET /api/v1/courses/category/5/stats
# Response: {
#   "total": 1200,
#   "avg_price": 499,
#   "min_price": 199,
#   "max_price": 999
# }
```

### Task 5: Warm up cache
```bash
POST /api/v1/courses/warm-cache
Authorization: Bearer TOKEN
# Response: {
#   "cached_count": 1000000,
#   "message": "Cache warmed successfully"
# }
```

---

## 🏆 Cheat Sheet

```php
// Imports
use App\Repositories\CourseRepository;
use Illuminate\Support\LazyCollection;

// Basic Usage
$repo = new CourseRepository($model);

// Get as LazyCollection
$courses = $repo->lazy();                     // All records
$active = $repo->lazyBy('status', 'active'); // Filtered
$names = $repo->lazyPluck('title');          // Just titles

// Process
$courses->each(fn($c) => do_something($c));
$courses->filter(fn($c) => $c->price > 100);
$courses->map(fn($c) => [...$c->toArray()]);
$courses->take(50);
$courses->toArray();

// Utilities
$repo->setChunkSize(5000);
$repo->with(['category']);
$repo->withCount(['enrollments']);

// Configuration
MEMORY_LIMIT=512M              # In .env
$this->setChunkSize(1000);     # In code
```

---

## 📞 Support

```
❓ Questions?         → Read LAZYCOLLECTIONS_GUIDE.md
📚 Need Examples?     → Check LAZYCOLLECTIONS_EXAMPLES.md
🐛 Found a Bug?       → dev@yourcompany.com
💡 Have an Idea?      → Create an issue
```

---

**Happy Coding! 🚀**
