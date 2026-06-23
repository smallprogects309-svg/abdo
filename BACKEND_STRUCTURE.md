# هيكل Backend المنظم

## 📁 البنية الكاملة

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── Mobile/          # Controllers للجوال (React Native)
│   │   │   │   ├── Web/             # Controllers للويب
│   │   │   │   └── Shared/          # Controllers مشتركة
│   │   │   └── Web/                 # Controllers التقليدية (إن وجدت)
│   │   ├── Middleware/              # Middleware (Auth, Cors, etc)
│   │   ├── Requests/                # Form Validation Requests
│   │   └── Resources/               # API Resources (Response Formatting)
│   ├── Services/                    # Business Logic Layer
│   ├── Repositories/                # Database Abstraction Layer
│   ├── Models/                      # Eloquent Models
│   ├── Traits/                      # Reusable Traits
│   ├── Exceptions/                  # Custom Exceptions
│   ├── Observers/                   # Model Observers
│   ├── Providers/                   # Service Providers
│   └── Jobs/                        # Queued Jobs
├── routes/
│   ├── api/
│   │   └── v1/
│   │       ├── mobile.php           # Mobile API Routes
│   │       ├── web.php              # Web API Routes
│   │       └── shared.php           # Shared API Routes
│   ├── web.php                      # Web Routes (Traditional)
│   └── console.php                  # Console Commands
├── database/
│   ├── migrations/                  # Database Migrations
│   ├── seeders/                     # Database Seeders
│   └── factories/                   # Model Factories (Testing)
├── resources/
│   ├── views/                       # Blade Templates (if needed)
│   └── css/                         # Styles (if needed)
├── config/                          # Configuration Files
├── tests/                           # Unit & Feature Tests
└── storage/                         # Logs, Cache, etc
```

## 🎯 توزيع المسؤوليات

### Controllers Organization

- **Mobile Controllers**: معالجة requests من React Native
- **Web Controllers**: معالجة requests من موقع الويب
- **Shared Controllers**: endpoints مشتركة بين الاثنين

### Services Layer

- Business Logic
- Validation Logic
- Complex Operations

### Repositories Layer

- Database Queries
- Model Interactions
- Data Access

### Models

- Eloquent Models
- Relationships
- Scopes

## 🚀 الفوائد

✅ **Clear Separation of Concerns** - فصل واضح للمسؤوليات
✅ **Easy to Scale** - سهولة التطور والتوسع
✅ **Version Support** - دعم API Versioning
✅ **Mobile & Web** - دعم كلا المنصتين
✅ **Code Reusability** - إعادة استخدام الأكواد
✅ **Testing** - سهولة الاختبار

## 📝 التالي

- انقل الملفات الموجودة للفولدرات الجديدة
- أنشئ Base Controllers للمشاركة
- أضف Middleware اللازمة
