# شرح الكود سطراً بسطر — ميزتا Users و Customers

> هذا الملف يشرح كل ملف في ميزتي **Users** و **Customers** سطراً بسطر، بالترتيب الذي يمشي فيه الطلب داخل التطبيق.
> المشروع: Laravel 13 بمعمارية Clean Architecture صارمة. كل ميزة مجلد مستقل داخل `app/Features`، ومقسّمة أربع طبقات:
> **Domain** (القواعد والعقود) ← **Application** (منطق الشغل) ← **Infrastructure** (التنفيذ الفعلي وقاعدة البيانات) ← **Presentation** (واجهة الـ API).
> القاعدة الذهبية: الطبقات العليا تعتمد على واجهات (Interfaces) لا على التنفيذ، والتنفيذ يُربط بها في الـ Provider.

---

## 0. خريطة الملفات

```
app/Features/Users/
├── Domain/
│   ├── Enums/            Gender, UserStatus, IdentityType, UserRole
│   └── Repositories/     UserRepositoryInterface
├── Application/
│   ├── DTOs/             RegisterUserDTO
│   └── Services/         AuthService, UserService
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Models/       User
│   │   └── Repositories/ EloquentUserRepository
│   └── Providers/        UsersServiceProvider
└── Presentation/Http/
    ├── Controllers/      AuthController, UserController
    ├── Requests/         RegisterRequest, LoginRequest, StoreUserRequest, UpdateUserRequest
    └── Resources/        UserResource

app/Features/Customers/
├── Domain/Repositories/          CustomerRepositoryInterface
├── Application/Services/         CustomerService
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Models/       Customer
│   │   └── Repositories/ EloquentCustomerRepository
│   └── Providers/        CustomersServiceProvider
└── Presentation/Http/
    ├── Controllers/      CustomerController
    ├── Requests/         StoreCustomerRequest, UpdateCustomerRequest
    └── Resources/        CustomerResource

routes/api.php                  كل روابط الـ API
bootstrap/providers.php         تسجيل الـ Providers
database/migrations/            جداول users و customers
database/factories/             بيانات وهمية للاختبار
database/seeders/               بيانات أولية
tests/Feature/UsersApiTest.php اختبارات الـ API
```

---

## 1. نقطة الدخول: `routes/api.php`

```php
<?php

use App\Features\Customers\Presentation\Http\Controllers\CustomerController;
use App\Features\Users\Presentation\Http\Controllers\AuthController;
use App\Features\Users\Presentation\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('customers', CustomerController::class);
});
```

- سطر 3–7: استيراد الكنترولرات الثلاثة (كل واحد من ميزته) + كلاسَي `Request` و `Route`.
- سطر 9: رابط `POST /api/register` مفتوح للكل — يروح لدالة `register` في `AuthController`.
- سطر 10: رابط `POST /api/login` مفتوح للكل — يروح لدالة `login`.
- سطر 12: بداية مجموعة محمية بوسيط `auth:sanctum` — أي طلب داخلها بدون توكن صالح يرجع `401 Unauthorized`.
- سطر 13–15: رابط `GET /api/user` الأصلي من Laravel، يرجّع بيانات المستخدم الحالي كما هي.
- سطر 16: `GET /api/me` — بيانات المستخدم الحالي مع ملف العميل، منسّقة بالـ Resource.
- سطر 17: `POST /api/logout` — حذف التوكن الحالي.
- سطر 18: `apiResource('users', ...)` يولّد 5 روابط دفعة واحدة: GET (قائمة)، POST (إنشاء)، GET/{id} (عرض)، PUT/PATCH/{id} (تعديل)، DELETE/{id} (حذف).
- سطر 19: نفس الشيء للعملاء. المجموع الكلي 15 route.

---

## 2. طبقة Domain — الـ Enums (في Users)

الـ Enum في PHP كلاس يقيّد القيم المسموحة لمجموعة ثابتة. أي قيمة خارجها مرفوضة.

### `Gender.php`
```php
enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
}
```
- `: string` يعني كل حالة لها قيمة نصية تُحفظ في قاعدة البيانات.
- الحالتان فقط: `male` و `female`.

### `UserStatus.php`
```php
enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
}
```
- حالة الحساب: نشط / غير نشط / موقوف. الدخول ممنوع إلا للنشط (انظر AuthService).

### `IdentityType.php`
```php
enum IdentityType: string
{
    case NationalId = 'national_id';
    case Passport = 'passport';
}
```
- نوع الهوية: بطاقة وطنية أو جواز سفر فقط — بقرار صريح من صاحب المشروع.

### `UserRole.php`
```php
enum UserRole: string
{
    case Admin = 'admin';
    case Employee = 'employee';
    case Customer = 'customer';
}
```
- دور المستخدم: مدير / موظف / عميل.

> ملاحظة: هذي القيم حالياً **ثابتة في الكود (hard-coded)**. تحويلها إلى قيم ديناميكية من قاعدة البيانات خطوة قادمة متفق عليها.

---

## 3. طبقة Domain — واجهات الـ Repository

### `UserRepositoryInterface.php`
```php
namespace App\Features\Users\Domain\Repositories;

use App\Features\Users\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /** @param array<string, mixed> $data */
    public function create(array $data): User;

    /** @param array<string, mixed> $data */
    public function update(User $user, array $data): User;

    public function delete(User $user): bool;

    public function findById(int $id): ?User;

    public function findByPhone(string $phone): ?User;

    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
```
- الواجهة "عقد": تعدّد العمليات المطلوبة بدون أي تنفيذ.
- `create` تستلم مصفوفة بيانات وترجّع كائن `User`.
- `update` تستلم المستخدم وبياناته الجديدة وترجّع النسخة المحدّثة.
- `delete` ترجّع `true/false` نجاح الحذف.
- `findById` و `findByPhone` ترجّعان المستخدم أو `null` (علامة `?` قبل النوع تعني "قد يكون null").
- `paginate` ترجّع قائمة مقسّمة صفحات، 15 في الصفحة افتراضياً.
- التعليقات `/** @param ... */` توثيق PHPDoc يصف أنواع المصفوفات لأدوات التحليل.

### `CustomerRepositoryInterface.php`
نفس الفكرة بالضبط لملف العميل: `create / update / delete / findById / paginate` — بدون `findByPhone` لأن العميل ما عنده دخول مستقل.

---

## 4. طبقة Application — الخدمات (Services)

الخدمة هي مكان "منطق الشغل": ماذا يحدث، وبأي ترتيب، وبأي شروط. تستلم الواجهة بالحقن (Dependency Injection) فما تعرف شيئاً عن قاعدة البيانات مباشرة.

### `AuthService.php` — سطراً بسطر
- سطر 5–9: الاستيرادات — `UserStatus` لفحص حالة الحساب، واجهة الـ Repository، موديل User، واجهة التشفير `Hash`، واستثناء التحقق `ValidationException`.
- سطر 13–15: المنشئ يستلم `UserRepositoryInterface` ويحفظها خاصية للقراءة فقط (`private readonly`). Laravel يمرّر التنفيذ الحقيقي تلقائياً بفضل الربط في الـ Provider.
- سطر 21–29 `register(array $data)`: ينشئ المستخدم عبر الـ Repository، ثم يرجّع مصفوفة فيها المستخدم وتوكن Sanctum جديد باسم `api` — و `plainTextToken` هي النسخة المقروءة الوحيدة من التوكن (تُعرض مرة واحدة).
- سطر 36–56 `login(string $phone, string $password)`:
  - سطر 38: البحث عن المستخدم برقم الهاتف.
  - سطر 40: لو ما وُجد، أو كلمة المرور ما طابقت المشفّرة (`Hash::check`) — يرمي `ValidationException` برسالة عامة "بيانات الدخول غير صحيحة" (عمداً لا يقول أيهما غلط، لأمان الحسابات).
  - سطر 46: لو حالة الحساب مو `Active` — يرفض الدخول برسالة "الحساب غير نشط".
  - سطر 52–55: النجاح — يرجّع المستخدم + توكن جديد.
- سطر 58–61 `logout(User $user)`: يحذف التوكن المستخدم في هذا الطلب فقط (`currentAccessToken()?->delete()`) — علامة `?->` تعني "لو فيه توكن أصلاً".

### `UserService.php` — سطراً بسطر
- سطر 16–19 `paginate`: يمرّر الطلب للـ Repository كما هو.
- سطر 21–24 `find`: بحث برقم المعرف.
- سطر 29–32 `create`: قبل الإنشاء يمرّر البيانات على `storeIdentityImages` لحفظ الصور.
- سطر 37–40 `update`: نفس الشيء عند التعديل.
- سطر 42–45 `delete`: حذف عبر الـ Repository.
- سطر 51–60 `storeIdentityImages` (دالة خاصة):
  - سطر 53: حلقة على حقلي الصورتين: الأمامية والخلفية.
  - سطر 54: لو الحقل موجود وهو ملف مرفوع فعلياً (`instanceof UploadedFile`)...
  - سطر 55: ...احفظه في قرص `public` داخل مجلد `identity`، واستبدل قيمة الحقل بمسار الملف المحفوظ (هذا المسار هو اللي ينحفظ في الجدول).
  - سطر 59: ارجع بالمصفوفة المعدّلة.

### `CustomerService.php`
نفس نمط `UserService` بدون صور: `paginate / find / create / update / delete` تمرّر للـ Repository مباشرة.

### `RegisterUserDTO.php`
```php
readonly class RegisterUserDTO
{
    /** @param array<string, mixed> $attributes */
    public function __construct(
        public array $attributes,
    ) {}
}
```
- DTO (كائن نقل بيانات): حاوية بسيطة تحمل بيانات التسجيل. `readonly` تعني خصائصه ما تتغيّر بعد الإنشاء.

---

## 5. طبقة Infrastructure — الموديلات

### `User.php` — سطراً بسطر
- سطر 3: الـ namespace يحدد مكان الكلاس في بنية الميزات.
- سطر 5: استيراد موديل `Customer` من ميزة Customers — هذا هو الربط بين الميزتين.
- سطر 6–9: استيراد الـ Enums الأربعة.
- سطر 10: استيراد الـ Factory الخاص بالموديل.
- سطر 11–17: استيرادات Laravel: سمات `Fillable/Hidden`، سمة المصانع، نوع العلاقة `HasOne`، كلاس المصادقة الأساسي، الإشعارات، وسمة توكنات Sanctum.
- سطر 19–32: سمة `#[Fillable([...])]` — قائمة الحقول المسموح تعبئتها جماعياً من الطلبات: الاسم الكامل، الهاتف، كلمة المرور، الجنس، الحالة، نوع الهوية ورقمها، صورتا الهوية، تاريخ انتهاء الهوية، تاريخ توثيق الحساب، والدور. أي حقل خارج القائمة يُتجاهل — حماية من حقن حقول غير مقصودة.
- سطر 33: `#[Hidden(['password', 'remember_token'])]` — هذان الحقلان ما يظهران أبداً في أي رد JSON.
- سطر 34: الكلاس يرث `Authenticatable` — يعني هذا الموديل هو "المستخدم" اللي يسجّل دخوله في Laravel.
- سطر 37: السمات: `HasApiTokens` (توكنات Sanctum)، `HasFactory` (بيانات وهمية)، `Notifiable` (إشعارات).
- سطر 42–45: `newFactory()` يخبر Laravel بمكان الـ Factory — ضروري لأن الموديل مو في مكانه الافتراضي `app/Models`.
- سطر 50–53: علاقة `customer()` — كل مستخدم له ملف عميل واحد (`HasOne`)، والمفتاح الخارجي `user_id` في جدول العملاء.
- سطر 60–71: `casts()` — تحويلات تلقائية عند القراءة/الكتابة: الحقول المصنّفة ترجع كائنات Enum، تاريخ انتهاء الهوية كائن تاريخ، تاريخ التوثيق كائن تاريخ ووقت، وكلمة المرور تُشفّر تلقائياً عند الحفظ (`hashed`).

### `Customer.php` — سطراً بسطر
- سطر 5: استيراد موديل `User` من ميزة Users — الاتجاه العكسي للربط.
- سطر 12–23: الحقول المسموحة: `user_id`، كود تحقق الهاتف، تواريخ التحقق (الطلب/الإرسال/الانتهاء)، المدينة، الحي، العنوان الحالي، خط العرض، خط الطول.
- سطر 24: يرث `Model` العادي (مو Authenticatable — العميل ما يسجّل دخولاً بنفسه).
- سطر 40–43: علاقة `user()` — كل ملف عميل ينتمي لمستخدم واحد (`BelongsTo`).
- سطر 50–59: الـ casts — التواريخ الثلاثة كائنات وقت، والإحداثيات أعداد عشرية بـ 7 خانات.

---

## 6. طبقة Infrastructure — تنفيذ الـ Repositories

### `EloquentUserRepository.php`
- `create`: `User::query()->create($data)` — إنشاء سجل جديد.
- `update`: `$user->update($data)` ثم `refresh()` لإرجاع أحدث نسخة من قاعدة البيانات.
- `delete`: حذف وترجيع نتيجته كـ bool.
- `findById`: بحث بالمعرف مع تحميل علاقة العميل مسبقاً (`with('customer')`).
- `findByPhone`: بحث برقم الهاتف — يستخدمه تسجيل الدخول.
- `paginate`: الأحدث أولاً (`latest()`)، مع العميل، مقسّم صفحات.

### `EloquentCustomerRepository.php`
نفس النمط: إنشاء/تعديل/حذف/بحث بالمعرف (مع المستخدم `with('user')`)/ترقيم صفحات بالأحدث.

### الـ Providers
- `UsersServiceProvider::register()` سطر واحد جوهري: `$this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class)` — "متى ما أحد طلب الواجهة، أعطه هذا التنفيذ".
- `CustomersServiceProvider` نفس الشيء لواجهة العملاء.
- `bootstrap/providers.php` يسجّل الاثنين مع `AppServiceProvider` عشان Laravel يعرفهما عند الإقلاع.

---

## 7. طبقة Presentation — الكنترولرات

### `AuthController.php`
- المنشئ يحقن خدمتين: `AuthService` و `UserService`.
- `register(RegisterRequest $request)`: يستلم طلباً **متحققاً منه أصلاً** (التحقق يصير قبل دخول الدالة)، ينشئ المستخدم عبر `UserService` (عشان الصور تُحفظ)، يرجّع `201` مع بيانات المستخدم منسّقة + توكن.
- `login(LoginRequest $request)`: يمرّر الهاتف وكلمة المرور للخدمة، يرجّع `200` مع المستخدم والتوكن.
- `me(Request $request)`: بيانات المستخدم صاحب التوكن مع ملف عميله.
- `logout(Request $request)`: حذف التوكن الحالي ورسالة تأكيد.

### `UserController.php` و `CustomerController.php`
نمط CRUD موحّد في الاثنين:
- `index`: قائمة مقسّمة صفحات، وحجم الصفحة من باراميتر `per_page` (افتراضي 15).
- `store`: إنشاء من بيانات متحقق منها، رد `201`.
- `show`: عرض واحد — لاحظ أن الباراميتر موديل مباشرة (`User $user`)، Laravel يجلب السجل تلقائياً من الـ id في الرابط (Route Model Binding)، ولو ما وُجد يرجع `404`.
- `update`: تعديل ورد `200` بالنسخة الجديدة.
- `destroy`: حذف ورسالة تأكيد.

---

## 8. طبقة Presentation — طلبات التحقق (Requests)

كل Request فيه دالتان: `authorize()` (ترجع `true` — الصلاحيات تُدار بالتوكن حالياً) و `rules()` (قواعد التحقق). لو فشل التحقق، Laravel يرجّع `422` مع تفاصيل الأخطاء قبل ما يوصل الطلب للكنترولر.

### `RegisterRequest`
- `full_name`: مطلوب، نص، حد 255.
- `phone`: مطلوب، نص، حد 30، **فريد في جدول users**.
- `password`: مطلوب، 8 أحرف على الأقل، مع تأكيد (`confirmed` يعني لازم حقل `password_confirmation` مطابق).
- `gender / status / identity_type / role`: اختيارية، ولازم تكون من قيم الـ Enum المقابل (`new Enum(...)`).
- `identity_number`: اختياري، فريد.
- `identity_front_image / identity_back_image`: اختيارية، لازم تكون صورة، حد 5MB.
- `identity_expiry_date`: اختياري، تاريخ صحيح.

### `LoginRequest`
- `phone` و `password`: مطلوبان نصيان فقط — التحقق الحقيقي (المطابقة) في الخدمة.

### `StoreUserRequest`
نفس قواعد التسجيل تقريباً، بفرقين: كلمة المرور بدون تأكيد، ويقبل `account_verified_at`.

### `UpdateUserRequest`
- كل الحقول `sometimes` (اختيارية عند التعديل الجزئي).
- قاعدتا الفرادة تستخدمان `->ignore($userId)` — تجاهل سجل المستخدم نفسه، عشان يقدر يعدّل اسمه بدون ما يصطدم بهاتفه الحالي.

### `StoreCustomerRequest`
- `user_id`: مطلوب، عدد صحيح، **موجود في جدول users**، و**فريد في جدول customers** (ملف واحد لكل مستخدم).
- حقول التحقق والعنوان: اختيارية نصية/تاريخية.
- `latitude`: بين ‎-90 و 90، و `longitude`: بين ‎-180 و 180.

### `UpdateCustomerRequest`
نفس القواعد بصيغة `sometimes`، وفرادة `user_id` تتجاهل السجل الحالي.

---

## 9. طبقة Presentation — الـ Resources (تنسيق الرد)

### `UserResource`
يبني مصفوفة الرد حقلاً حقلاً:
- الحقول الأساسية كما هي (`id`, `full_name`, `phone`, `identity_number`, مسارا الصورتين).
- الحقول المصنّفة تُرجع قيمتها النصية: `$this->gender?->value` — علامة `?->` تعني "لو القيمة null لا تنهار".
- التواريخ بصيغ قياسية: تاريخ الانتهاء `toDateString()`، والباقي `toISOString()`.
- `customer`: يضمّن ملف العميل **بس لو كان محمّلاً** (`whenLoaded`) — كذا ما تصير استعلامات زائدة.

### `CustomerResource`
نفس النمط، ويضمّن `user` لو محمّل.

---

## 10. قاعدة البيانات — الـ Migrations

### جدول `users` (الملف `0001_01_01_000000_create_users_table.php`)
- `id` مفتاح أساسي تلقائي.
- `full_name` نص مطلوب.
- `phone` نص **unique** — هذا هو المعرف الفريد بدل الإيميل.
- `password` نص (يُحفظ مشفّراً عبر الـ cast).
- `gender`, `identity_type`, `identity_number` (unique), صورتا الهوية: نصية اختيارية (nullable).
- `status` نص بقيمة افتراضية `active`، و `role` افتراضياً `customer`.
- `identity_expiry_date` تاريخ اختياري، `account_verified_at` وقت اختياري.
- `remember_token` + `created_at/updated_at` من Laravel.
- نفس الملف ينشئ `password_reset_tokens` ومفتاحه الأساسي **phone** بدل email، وجدول `sessions` الافتراضي.

### جدول `customers` (الملف `2026_11_22_182812_create_customers_table.php`)
- `user_id` مفتاح خارجي **unique** مربوط بـ `users.id` مع `cascadeOnDelete` — حذف المستخدم يحذف ملفه تلقائياً.
- `phone_verification_code` نص اختياري + ثلاثة تواريخ اختيارية.
- `city`, `district`, `current_address` نصية اختيارية.
- `latitude/longitude` نوع `decimal(10,7)` اختياري — دقة كافية لإحداثيات الخرائط.
- `created_at/updated_at`.

> ملاحظة: Migration المستخدمين عُدّلت في مكانها (مشروع تجريبي، قاعدة جديدة مفترضة) — محلياً تحتاج `migrate:fresh --seed` بعد السحب.

---

## 11. البيانات الوهمية والبذرة

- `UserFactory`: يولّد اسماً عشوائياً، هاتفاً فريداً بصيغة `7########`، كلمة مرور `password` مشفّرة، قيماً عشوائية من الـ Enums، ورقم هوية فريداً. خاصية `$model` صريحة لأن الموديل خارج مكانه الافتراضي.
- `CustomerFactory`: ينشئ مستخدماً مرتبطاً تلقائياً + مدينة وحي وعنوان وإحداثيات عشوائية.
- `DatabaseSeeder`: ينشئ المستخدم التجريبي `777000000 / password` مع ملف عميل — هذا اللي تسجّل دخولك به في Insomnia/Postman.

---

## 12. الاختبارات — `tests/Feature/UsersApiTest.php`

ثمانية اختبارات (مع اختباري المشروع الأصليين = **10 passed**):
1. تسجيل ناجح بالهاتف يرجع توكن وينحفظ في الجدول.
2. تسجيل بهاتف مكرر يُرفض `422`.
3. نوع هوية غير مدعوم (`driving_license`) يُرفض `422`.
4. دخول صحيح بالهاتف وكلمة المرور.
5. دخول بكلمة مرور خاطئة يُرفض.
6. روابط المستخدمين والعملاء بدون توكن ترجع `401`.
7. CRUD كامل للميزتين بمستخدم موثّق (إنشاء، عرض، تعديل، حذف، والتحقق من غيابهما من الجدول).
8. ملف عميل ثانٍ لنفس المستخدم يُرفض (فرادة `user_id`).

---

## 13. رحلة طلب كاملة — مثال عملي

طلب `POST /api/customers` مع التوكن:
1. `routes/api.php` يطابق الرابط ويمرّره على وسيط `auth:sanctum` — يتحقق من التوكن.
2. `StoreCustomerRequest` يتحقق من البيانات — فشل؟ `422` فوراً.
3. `CustomerController::store` يستلم البيانات النظيفة وينادي `CustomerService::create`.
4. الخدمة تنادي `CustomerRepositoryInterface::create` — هي ما تعرف التنفيذ.
5. `EloquentCustomerRepository` ينفّذ `Customer::query()->create(...)` فينحفظ السجل.
6. الكنترولر يلفّ الناتج بـ `CustomerResource` ويرجّع `201`.

---

## 14. كيف توسّع المشروع بنفس النمط

لإضافة ميزة جديدة (مثلاً Shipments):
1. انسخ بنية مجلدات أي ميزة موجودة وبدّل الاسم.
2. اكتب الـ Migration والموديل والعلاقات.
3. عرّف واجهة الـ Repository في Domain، ونفّذها بـ Eloquent في Infrastructure.
4. اكتب الخدمة في Application، والكنترولر والطلبات والـ Resource في Presentation.
5. سجّل الـ Provider في `bootstrap/providers.php` وأضف الرابط في `routes/api.php`.
6. اكتب اختبارات Pest وشغّل `php artisan test` و Pint.

---

*آخر تحديث: يطابق الكود المدموج على `main` (commit `02e4b05`) — ميزتا Users و Customers منفصلتان ومترابطتان بالعلاقات.*
