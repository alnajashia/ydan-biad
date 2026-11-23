# شرح الكود سطراً بسطر — ميزات Users و Customers و Lookups

> هذا الملف يشرح كل ملف في ميزات **Users** و **Customers** و **Lookups** سطراً بسطر، بالترتيب الذي يمشي فيه الطلب داخل التطبيق.
> المشروع: Laravel 13 بمعمارية Clean Architecture صارمة. كل ميزة مجلد مستقل داخل `app/Features`، ومقسّمة أربع طبقات:
> **Domain** (القواعد والعقود) ← **Application** (منطق الشغل) ← **Infrastructure** (التنفيذ الفعلي وقاعدة البيانات) ← **Presentation** (واجهة الـ API).
> القاعدة الذهبية: الطبقات العليا تعتمد على واجهات (Interfaces) لا على التنفيذ، والتنفيذ يُربط بها في الـ Provider.

---

## 0. خريطة الملفات

```
app/Features/Users/
├── Domain/
│   ├── Enums/            Gender, IdentityType
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

app/Features/Lookups/
├── Domain/Repositories/          StatusRepositoryInterface, RoleRepositoryInterface
├── Application/Services/         StatusService, RoleService
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Models/       Status, Role
│   │   └── Repositories/ EloquentStatusRepository, EloquentRoleRepository
│   └── Providers/        LookupsServiceProvider
└── Presentation/Http/
    ├── Controllers/      StatusController, RoleController
    ├── Requests/         StoreStatusRequest, UpdateStatusRequest, StoreRoleRequest, UpdateRoleRequest
    └── Resources/        StatusResource, RoleResource

routes/api.php                  كل روابط الـ API
bootstrap/providers.php         تسجيل الـ Providers
database/migrations/            جداول statuses و roles و users و customers
database/factories/             بيانات وهمية للاختبار
database/seeders/               بيانات أولية
tests/Feature/UsersApiTest.php    اختبارات المستخدمين والعملاء
tests/Feature/LookupsApiTest.php  اختبارات الحالات والأدوار الديناميكية
```

---

## 1. نقطة الدخول: `routes/api.php`

```php
<?php

use App\Features\Customers\Presentation\Http\Controllers\CustomerController;
use App\Features\Lookups\Presentation\Http\Controllers\RoleController;
use App\Features\Lookups\Presentation\Http\Controllers\StatusController;
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
    Route::apiResource('statuses', StatusController::class);
    Route::apiResource('roles', RoleController::class);
});
```

- سطر 3–9: استيراد الكنترولرات الخمسة (كل واحد من ميزته) + كلاسَي `Request` و `Route`.
- سطر 11: رابط `POST /api/register` مفتوح للكل — يروح لدالة `register` في `AuthController`.
- سطر 12: رابط `POST /api/login` مفتوح للكل — يروح لدالة `login`.
- سطر 14: بداية مجموعة محمية بوسيط `auth:sanctum` — أي طلب داخلها بدون توكن صالح يرجع `401 Unauthorized`.
- سطر 15–17: رابط `GET /api/user` الأصلي من Laravel، يرجّع بيانات المستخدم الحالي كما هي.
- سطر 18: `GET /api/me` — بيانات المستخدم الحالي مع ملف العميل والحالة والدور، منسّقة بالـ Resource.
- سطر 19: `POST /api/logout` — حذف التوكن الحالي.
- سطر 20: `apiResource('users', ...)` يولّد 5 روابط دفعة واحدة: GET (قائمة)، POST (إنشاء)، GET/{id} (عرض)، PUT/PATCH/{id} (تعديل)، DELETE/{id} (حذف).
- سطر 21: نفس الشيء للعملاء.
- سطر 22–23: نفس الشيء للحالات (`statuses`) والأدوار (`roles`) من ميزة Lookups. المجموع الكلي 25 route.

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

### `IdentityType.php`
```php
enum IdentityType: string
{
    case NationalId = 'national_id';
    case Passport = 'passport';
}
```
- نوع الهوية: بطاقة وطنية أو جواز سفر فقط — بقرار صريح من صاحب المشروع.

> **قاعدة التصنيفات:** أي تصنيف له **أكثر من خيارين** يعيش في قاعدة البيانات (ديناميكي)، وأي تصنيف له **خياران فقط** يبقى hard-coded كـ Enum. لهذا بقي `Gender` و `IdentityType` (خياران لكلٍّ منهما) في الكود، وانتقلت **الحالة** و**الدور** (أكثر من خيارين) إلى جدولي `statuses` و `roles` في ميزة Lookups — انظر القسمين 5 و 10. ملفا `UserStatus.php` و `UserRole.php` انحذفا نهائياً.

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

### `StatusRepositoryInterface.php` و `RoleRepositoryInterface.php` (في Lookups)
نفس النمط للحالات والأدوار: `create / update / delete / findById / paginate`، وزيادة دالة `findByName(string $name)` — تبحث بالاسم (مثل `active` أو `customer`) وتستخدمها الخدمات لتطبيق القيم الافتراضية وفحص الدخول.

---

## 4. طبقة Application — الخدمات (Services)

الخدمة هي مكان "منطق الشغل": ماذا يحدث، وبأي ترتيب، وبأي شروط. تستلم الواجهة بالحقن (Dependency Injection) فما تعرف شيئاً عن قاعدة البيانات مباشرة.

### `AuthService.php` — سطراً بسطر
- سطر 5–10: الاستيرادات — واجهتا `StatusRepositoryInterface` و `RoleRepositoryInterface` من Lookups، واجهة مستودع المستخدمين، موديل User، واجهة التشفير `Hash`، واستثناء التحقق `ValidationException`.
- سطر 14–18: المنشئ يستلم ثلاث واجهات ويحفظها خصائص للقراءة فقط (`private readonly`). Laravel يمرّر التنفيذ الحقيقي تلقائياً بفضل الربط في الـ Providers.
- سطر 24–40 `register(array $data)`: لو ما انرسل `status_id` يبحث عن الحالة باسم `active` ويستخدم معرفها، ولو ما انرسل `role_id` يبحث عن الدور `customer` — ثم ينشئ المستخدم عبر الـ Repository، ويرجّع مصفوفة فيها المستخدم وتوكن Sanctum جديد باسم `api` — و `plainTextToken` هي النسخة المقروءة الوحيدة من التوكن (تُعرض مرة واحدة).
- سطر 47–69 `login(string $phone, string $password)`:
  - سطر 49: البحث عن المستخدم برقم الهاتف (مع حالته ودوره محمّلين مسبقاً).
  - سطر 51: لو ما وُجد، أو كلمة المرور ما طابقت المشفّرة (`Hash::check`) — يرمي `ValidationException` برسالة عامة "بيانات الدخول غير صحيحة" (عمداً لا يقول أيهما غلط، لأمان الحسابات).
  - سطر 57–63: فحص الحالة الديناميكي — الدخول مسموح فقط إذا حالة الحساب موجودة **واسمها `active` وقيمتها `value = true`** معاً؛ غير كذا يرفض برسالة "الحساب غير نشط".
  - سطر 65–68: النجاح — يرجّع المستخدم + توكن جديد.
- سطر 71–74 `logout(User $user)`: يحذف التوكن المستخدم في هذا الطلب فقط (`currentAccessToken()?->delete()`) — علامة `?->` تعني "لو فيه توكن أصلاً".

### `UserService.php` — سطراً بسطر
- سطر 14–18: المنشئ يستلم ثلاث واجهات: مستودع المستخدمين + مستودعا الحالات والأدوار.
- سطر 20–23 `paginate`: يمرّر الطلب للـ Repository كما هو.
- سطر 25–28 `find`: بحث برقم المعرف.
- سطر 33–36 `create`: قبل الإنشاء يمرّر البيانات على `applyDefaults` (القيم الافتراضية) ثم `storeIdentityImages` لحفظ الصور.
- سطر 41–44 `update`: نفس الشيء عند التعديل (بدون قيم افتراضية).
- سطر 46–49 `delete`: حذف عبر الـ Repository.
- سطر 55–65 `applyDefaults` (دالة خاصة): لو `status_id` فارغ يجلبه من الحالة `active`، ولو `role_id` فارغ يجلبه من الدور `customer` — بحثاً بالاسم عبر واجهتَي Lookups.
- سطر 72–81 `storeIdentityImages` (دالة خاصة):
  - سطر 74: حلقة على حقلي الصورتين: الأمامية والخلفية.
  - سطر 75: لو الحقل موجود وهو ملف مرفوع فعلياً (`instanceof UploadedFile`)...
  - سطر 76: ...احفظه في قرص `public` داخل مجلد `identity`، واستبدل قيمة الحقل بمسار الملف المحفوظ (هذا المسار هو اللي ينحفظ في الجدول).
  - سطر 80: ارجع بالمصفوفة المعدّلة.

### `CustomerService.php`
نفس نمط `UserService` بدون صور: `paginate / find / create / update / delete` تمرّر للـ Repository مباشرة.

### `StatusService.php` و `RoleService.php` (في Lookups)
نفس النمط الخفيف: `paginate / find / create / update / delete` تمرّر لواجهة الـ Repository الخاصة بكلٍّ منهما — بدون أي منطق إضافي، لأن الحالات والأدوار بيانات مرجعية بسيطة.

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
- سطر 6–7: استيراد موديلَي `Role` و `Status` من ميزة Lookups — ربط المستخدم بالحالة والدور الديناميكيين.
- سطر 8–9: استيراد الـ Enumين الباقيين: `Gender` و `IdentityType`.
- سطر 10: استيراد الـ Factory الخاص بالموديل.
- سطر 11–18: استيرادات Laravel: سمات `Fillable/Hidden`، سمة المصانع، نوعا العلاقات `BelongsTo` و `HasOne`، كلاس المصادقة الأساسي، الإشعارات، وسمة توكنات Sanctum.
- سطر 20–33: سمة `#[Fillable([...])]` — قائمة الحقول المسموح تعبئتها جماعياً من الطلبات: الاسم الكامل، الهاتف، كلمة المرور، الجنس، `status_id`، نوع الهوية ورقمها، صورتا الهوية، تاريخ انتهاء الهوية، تاريخ توثيق الحساب، و `role_id`. أي حقل خارج القائمة يُتجاهل — حماية من حقن حقول غير مقصودة.
- سطر 34: `#[Hidden(['password', 'remember_token'])]` — هذان الحقلان ما يظهران أبداً في أي رد JSON.
- سطر 35: الكلاس يرث `Authenticatable` — يعني هذا الموديل هو "المستخدم" اللي يسجّل دخوله في Laravel.
- سطر 38: السمات: `HasApiTokens` (توكنات Sanctum)، `HasFactory` (بيانات وهمية)، `Notifiable` (إشعارات).
- سطر 43–46: `newFactory()` يخبر Laravel بمكان الـ Factory — ضروري لأن الموديل مو في مكانه الافتراضي `app/Models`.
- سطر 51–54: علاقة `customer()` — كل مستخدم له ملف عميل واحد (`HasOne`)، والمفتاح الخارجي `user_id` في جدول العملاء.
- سطر 59–62: علاقة `status()` — كل مستخدم ينتمي لحالة واحدة (`BelongsTo`) عبر `status_id`.
- سطر 67–70: علاقة `role()` — كل مستخدم ينتمي لدور واحد (`BelongsTo`) عبر `role_id`.
- سطر 77–85: `casts()` — تحويلات تلقائية عند القراءة/الكتابة: الجنس ونوع الهوية يرجعان كائنَي Enum، تاريخ انتهاء الهوية كائن تاريخ، تاريخ التوثيق كائن تاريخ ووقت، وكلمة المرور تُشفّر تلقائياً عند الحفظ (`hashed`). لاحظ: لا casts للحالة والدور — صارا علاقتين لا قيماً نصية.

### `Status.php` و `Role.php` (في Lookups) — سطراً بسطر
الموديلان متطابقان في البنية (نأخذ `Status` مثالاً):
- سطر 5: استيراد موديل `User` — للعلاقة العكسية.
- سطر 12–15: `#[Fillable(['name', 'value'])]` — الحقلان الوحيدان القابلان للتعبئة.
- سطر 16: يرث `Model` العادي.
- سطر 23: `public $timestamps = false;` — الجدول بلا `created_at/updated_at`، ثلاثة أعمدة فقط كما هو متفق عليه.
- سطر 31–34: `newFactory()` لمكان الـ Factory.
- سطر 39–42: علاقة `users()` — كل حالة/دور لها مستخدمون كثيرون (`HasMany`).
- سطر 49–54: `casts()` — `value` يرجع Boolean حقيقي (`true/false`) لا 0/1.

### `Customer.php` — سطراً بسطر
- سطر 5: استيراد موديل `User` من ميزة Users — الاتجاه العكسي للربط.
- سطر 12–23: الحقول المسموحة: `user_id`، كود تحقق الهاتف، تواريخ التحقق (الطلب/الإرسال/الانتهاء)، المدينة، الحي، العنوان الحالي، خط العرض، خط الطول.
- سطر 24: يرث `Model` العادي (مو Authenticatable — العميل ما يسجّل دخولاً بنفسه).
- سطر 40–43: علاقة `user()` — كل ملف عميل ينتمي لمستخدم واحد (`BelongsTo`).
- سطر 50–59: الـ casts — التواريخ الثلاثة كائنات وقت، والإحداثيات أعداد عشرية بـ 7 خانات.

---

## 6. طبقة Infrastructure — تنفيذ الـ Repositories

### `EloquentUserRepository.php`
- `create`: `User::query()->create($data)` ثم تحميل علاقتَي `status` و `role` على الناتج (`->load([...])`) عشان الرد يضمّنهما فوراً.
- `update`: `$user->update($data)` ثم `refresh()` لإرجاع أحدث نسخة من قاعدة البيانات، مع تحميل `status` و `role`.
- `delete`: حذف وترجيع نتيجته كـ bool.
- `findById`: بحث بالمعرف مع تحميل العميل والحالة والدور مسبقاً (`with(['customer', 'status', 'role'])`).
- `findByPhone`: بحث برقم الهاتف مع الحالة والدور — يستخدمه تسجيل الدخول لفحص الحالة.
- `paginate`: الأحدث أولاً (`latest()`)، مع العميل والحالة والدور، مقسّم صفحات.

### `EloquentCustomerRepository.php`
نفس النمط: إنشاء/تعديل/حذف/بحث بالمعرف (مع المستخدم `with('user')`)/ترقيم صفحات بالأحدث.

### `EloquentStatusRepository.php` و `EloquentRoleRepository.php` (في Lookups)
نفس النمط، مع دالتين مميزتين:
- `findByName`: بحث بالاسم (`where('name', $name)->first()`) — تستخدمها الخدمات للقيم الافتراضية.
- `paginate`: ترتيب تصاعدي بالمعرف (`orderBy('id')`) بدل الأحدث — لأن الجدول بلا توقيتات، ولأن ترتيب البذرة (active أولاً، admin أولاً) هو الترتيب الطبيعي للعرض.

### الـ Providers
- `UsersServiceProvider::register()` سطر واحد جوهري: `$this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class)` — "متى ما أحد طلب الواجهة، أعطه هذا التنفيذ".
- `CustomersServiceProvider` نفس الشيء لواجهة العملاء.
- `LookupsServiceProvider` يربط واجهتين: `StatusRepositoryInterface` و `RoleRepositoryInterface` بتنفيذيهما.
- `bootstrap/providers.php` يسجّل الثلاثة مع `AppServiceProvider` عشان Laravel يعرفها عند الإقلاع.

---

## 7. طبقة Presentation — الكنترولرات

### `AuthController.php`
- المنشئ يحقن خدمتين: `AuthService` و `UserService`.
- `register(RegisterRequest $request)`: يستلم طلباً **متحققاً منه أصلاً** (التحقق يصير قبل دخول الدالة)، ينشئ المستخدم عبر `UserService` (عشان الصور تُحفظ والقيم الافتراضية تُطبّق)، يرجّع `201` مع بيانات المستخدم منسّقة + توكن.
- `login(LoginRequest $request)`: يمرّر الهاتف وكلمة المرور للخدمة، يرجّع `200` مع المستخدم والتوكن.
- `me(Request $request)`: بيانات المستخدم صاحب التوكن مع ملف عميله وحالته ودوره (`load(['customer', 'status', 'role'])`).
- `logout(Request $request)`: حذف التوكن الحالي ورسالة تأكيد.

### `UserController.php` و `CustomerController.php` و `StatusController.php` و `RoleController.php`
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
- `gender / identity_type`: اختيارية، ولازم تكون من قيم الـ Enum المقابل (`new Enum(...)`) — هذان فقط بقيا hard-coded.
- `status_id`: اختياري، عدد صحيح، **موجود في جدول statuses** (`Rule::exists`) — لو ما انرسل، الخدمة تطبّق الحالة `active`.
- `role_id`: اختياري، عدد صحيح، **موجود في جدول roles** — لو ما انرسل، الخدمة تطبّق الدور `customer`.
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

### `StoreStatusRequest` و `StoreRoleRequest` (في Lookups)
- `name`: مطلوب، نص، حد 100، **فريد في جدوله** (`statuses` أو `roles`).
- `value`: مطلوب، لازم يكون Boolean (`true/false`).

### `UpdateStatusRequest` و `UpdateRoleRequest` (في Lookups)
- الحقلان `sometimes` (تعديل جزئي مسموح)، وفرادة `name` تتجاهل السجل الحالي (`->ignore(...)`).

---

## 9. طبقة Presentation — الـ Resources (تنسيق الرد)

### `UserResource`
يبني مصفوفة الرد حقلاً حقلاً:
- الحقول الأساسية كما هي (`id`, `full_name`, `phone`, `identity_number`, مسارا الصورتين).
- الحقول المصنّفة الـ hard-coded تُرجع قيمتها النصية: `$this->gender?->value` — علامة `?->` تعني "لو القيمة null لا تنهار".
- `status` و `role`: يرجعان **ككائنات كاملة** (`id` + `name` + `value`) عبر `StatusResource` و `RoleResource`، أو `null` لو ما فيه حالة/دور مرتبطة.
- التواريخ بصيغ قياسية: تاريخ الانتهاء `toDateString()`، والباقي `toISOString()`.
- `customer`: يضمّن ملف العميل **بس لو كان محمّلاً** (`whenLoaded`) — كذا ما تصير استعلامات زائدة.

### `CustomerResource`
نفس النمط، ويضمّن `user` لو محمّل.

### `StatusResource` و `RoleResource` (في Lookups)
أبسط Resource ممكن: يرجّع `id` و `name` و `value` — مع تحويل `value` صراحة إلى bool (`(bool) $this->value`) عشان يظهر في JSON كـ `true/false` لا 0/1.

---

## 10. قاعدة البيانات — الـ Migrations

### جدولا `statuses` و `roles` (الملف `0001_01_01_000000_create_statuses_and_roles_tables.php`)
- كل جدول **ثلاثة أعمدة فقط**: `id` مفتاح أساسي تلقائي، `name` نص **unique**، `value` Boolean بقيمة افتراضية `false`.
- بدون `created_at/updated_at` — جداول مرجعية عامة، تُستخدم في أي مكان بالتطبيق مو للمستخدمين بس.
- اسم الملف يبدأ بنفس طابع جدول المستخدمين لكنه يسبقه أبجدياً (`statuses_and_roles` قبل `users`) — فتنشأ الجداول المرجعية أولاً، وبعدها يقدر جدول `users` يربطها بمفاتيح خارجية.

### جدول `users` (الملف `0001_01_01_000000_create_users_table.php`)
- `id` مفتاح أساسي تلقائي.
- `full_name` نص مطلوب.
- `phone` نص **unique** — هذا هو المعرف الفريد بدل الإيميل.
- `password` نص (يُحفظ مشفّراً عبر الـ cast).
- `gender`, `identity_type`, `identity_number` (unique), صورتا الهوية: نصية اختيارية (nullable).
- `status_id` مفتاح خارجي اختياري مربوط بـ `statuses.id` مع `nullOnDelete` — حذف حالة يصفّر حقل مستخدميها بدل حذفهم. نفس الشيء `role_id` مربوط بـ `roles.id`.
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

- `StatusFactory` و `RoleFactory`: يولّدان اسماً فريداً عشوائياً وقيمة `true`.
- `UserFactory`: يولّد اسماً عشوائياً، هاتفاً فريداً بصيغة `7########`، كلمة مرور `password` مشفّرة، قيماً عشوائية من الـ Enumين، ورقم هوية فريداً. حقلا `status_id` و `role_id` عبارة عن دالتين (Closures) تستخدمان `firstOrCreate` — تجلب الحالة `active` والدور `customer` إن وُجدا أو تنشئهما، عشان ما تتكرر الأسماء الفريدة. خاصية `$model` صريحة لأن الموديل خارج مكانه الافتراضي.
- `CustomerFactory`: ينشئ مستخدماً مرتبطاً تلقائياً + مدينة وحي وعنوان وإحداثيات عشوائية.
- `StatusSeeder`: يزرع الحالات الأربع بـ `updateOrCreate` (آمن لو انشغّل مرتين): `active` بقيمة true، و `inactive` و `suspend` و `prohibited` بقيمة false.
- `RoleSeeder`: يزرع الأدوار الثلاثة: `admin` و `employee` و `customer` — كلها true.
- `DatabaseSeeder`: ينادي البذرتين أولاً، ثم ينشئ المستخدم التجريبي `777000000 / password` مع ملف عميل — هذا اللي تسجّل دخولك به في Insomnia/Postman.

---

## 12. الاختبارات

كلا الملفين يبدآن بـ `beforeEach` يزرع بذرتَي الحالات والأدوار — عشان القيم الافتراضية (`active`/`customer`) تكون موجودة مثل بيئة التشغيل الحقيقية. المجموع الكلي مع اختباري المشروع الأصليين: **19 passed (87 assertions)**.

### `tests/Feature/UsersApiTest.php`
ثمانية اختبارات:
1. تسجيل ناجح بالهاتف يرجع توكن وينحفظ في الجدول — ويتحقق أن الحالة الافتراضية `active` بقيمة true والدور `customer`.
2. تسجيل بهاتف مكرر يُرفض `422`.
3. نوع هوية غير مدعوم (`driving_license`) يُرفض `422`.
4. دخول صحيح بالهاتف وكلمة المرور.
5. دخول بكلمة مرور خاطئة يُرفض.
6. روابط المستخدمين والعملاء بدون توكن ترجع `401`.
7. CRUD كامل للميزتين بمستخدم موثّق (إنشاء، عرض، تعديل، حذف، والتحقق من غيابهما من الجدول) — والإنشاء يتحقق من الحالة والدور الافتراضيين في الرد.
8. ملف عميل ثانٍ لنفس المستخدم يُرفض (فرادة `user_id`).

### `tests/Feature/LookupsApiTest.php`
تسعة اختبارات:
1. روابط الحالات والأدوار بدون توكن ترجع `401`.
2. CRUD كامل للحالات (إنشاء `pending`، قائمة تبدأ بـ `active`، عرض، تعديل `value`، حذف، غياب من الجدول).
3. إنشاء حالة باسم مكرر (`active`) يُرفض `422`.
4. CRUD كامل للأدوار (إنشاء `manager`، قائمة تبدأ بـ `admin`، عرض، تعديل الاسم، حذف).
5. إنشاء دور باسم مكرر (`admin`) يُرفض `422`.
6. مستخدم حالته `inactive` ما يقدر يدخل.
7. مستخدم حالته `active` لكن قيمتها `value = false` ما يقدر يدخل — هذا هو الشرط الثاني لفحص الدخول.
8. تعيين حالة ودور لمستخدم بالمعرف (`status_id`/`role_id`) ينحفظ ويظهر في الرد.
9. تعيين معرف حالة/دور غير موجود يُرفض `422`.

---

## 13. رحلة طلب كاملة — مثال عملي

طلب `POST /api/customers` مع التوكن:
1. `routes/api.php` يطابق الرابط ويمرّره على وسيط `auth:sanctum` — يتحقق من التوكن.
2. `StoreCustomerRequest` يتحقق من البيانات — فشل؟ `422` فوراً.
3. `CustomerController::store` يستلم البيانات النظيفة وينادي `CustomerService::create`.
4. الخدمة تنادي `CustomerRepositoryInterface::create` — هي ما تعرف التنفيذ.
5. `EloquentCustomerRepository` ينفّذ `Customer::query()->create(...)` فينحفظ السجل.
6. الكنترولر يلفّ الناتج بـ `CustomerResource` ويرجّع `201`.

ونفس الرحلة بالضبط تنطبق على `POST /api/statuses` — بس بملفات Lookups: `StoreStatusRequest` ← `StatusController` ← `StatusService` ← `EloquentStatusRepository` ← `StatusResource`.

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

*آخر تحديث: يطابق فرع `feature/dynamic-lookups` (PR #4) — ثلاث ميزات: Users و Customers و Lookups (الحالات والأدوار ديناميكية من قاعدة البيانات). بعد دمج الـ PR يطابق الكود على `main`.*
