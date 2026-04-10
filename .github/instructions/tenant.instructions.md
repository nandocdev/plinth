---
description: Describe when these instructions should be loaded by the agent based on task context
# applyTo: 'Describe when these instructions should be loaded by the agent based on task context' # when provided, instructions will automatically be added to the request context when the pattern matches an attached file
---

# GitHub Copilot Instructions — SaaS-Kit-2026

# Laravel 12+ · PHP 8.3+ · Multi-Tenancy · Modular Architecture

> **These rules are non-negotiable.** When in doubt, ask before generating.
> If a request violates these rules, say so and propose the correct approach.

---

## 1. Project Context

**Stack:** Laravel 12, PHP 8.3, PostgreSQL 16, Redis 7, Livewire 4, Alpine.js, Tailwind CSS 3, Vite.  
**Multi-Tenancy:** `stancl/tenancy` ^4.x — database-per-tenant via subdomain.  
**Auth:** `laravel/fortify`  
**RBAC:** `spatie/laravel-permission` ^7 (teams enabled)  
**Billing:** `laravel/cashier` ^15 (Stripe)  
**Feature Flags:** `laravel/pennant`  
**Queues:** `laravel/horizon`  
**Observability:** `laravel/pulse`  
**Testing:** `pestphp/pest` + `pestphp/pest-plugin-laravel`

---

## 2. Directory Structure — The Law

Every feature lives in a **Bounded Context → Module**. No exceptions.

```
app/
├── Central/
│   ├── AuthenticationModule/
│   ├── TenantProvisioningModule/
│   ├── BillingModule/
│   └── AdminDashboardModule/
├── Tenant/
│   ├── WorkspaceModule/
│   ├── AuthorizationModule/
│   ├── FeatureFlagsModule/
│   └── ActivityLogModule/
└── Shared/
    ├── DTOs/
    ├── Contracts/
    ├── Support/
    └── Infrastructure/
```

**Every module** replicates the full Laravel `app/` structure internally:

```
SomeModule/
├── Actions/
├── DTOs/
├── Events/
├── Listeners/
├── Jobs/
├── Models/
├── Observers/
├── Policies/
├── Livewire/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Services/
├── Providers/
│   └── SomeModuleServiceProvider.php
├── Resources/
│   └── Views/
└── Routes/
    └── web.php
```

**Rules:**

- Central modules → `app.com` only (no tenant context).
- Tenant modules → `*.app.com` only (always assume tenant is initialized).
- Shared → pure contracts, DTOs, value objects. Zero framework coupling.
- **No** models in `app/Models/`. All models live inside their module.
- **No** routes in `routes/web.php` or `routes/api.php`. Each module owns its routes, registered by its `ServiceProvider`.

---

## 3. Namespace Conventions

```php
// Central module
namespace App\Central\BillingModule\Actions;
namespace App\Central\BillingModule\Models;
namespace App\Central\BillingModule\Jobs;

// Tenant module
namespace App\Tenant\AuthorizationModule\Services;
namespace App\Tenant\WorkspaceModule\Models;

// Shared
namespace App\Shared\DTOs;
namespace App\Shared\Contracts;
```

---

## 4. Code Patterns — Apply These, Nothing Else

### 4.1 Actions (single-responsibility operations)

```php
<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Actions;

use App\Central\TenantProvisioningModule\DTOs\CreateTenantDTO;
use App\Central\TenantProvisioningModule\Jobs\ProvisionTenantJob;
use App\Central\TenantProvisioningModule\Models\Tenant;

final class CreateTenantAction
{
    public function execute(CreateTenantDTO $dto): Tenant
    {
        $tenant = Tenant::create([
            'name'     => $dto->name,
            'owner_id' => $dto->ownerId,
            'status'   => 'provisioning',
        ]);

        ProvisionTenantJob::dispatch($tenant)->onQueue('provisioning');

        return $tenant;
    }
}
```

**Rules:**

- One public method: `execute()` or `handle()`.
- No `static` methods — always inject via constructor or resolve via `app()`.
- Actions call other Actions or dispatch Jobs. Never call Artisan directly from a controller.
- Return a typed value or void. Never return `array` when a DTO exists.

### 4.2 DTOs (immutable data carriers)

```php
<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\DTOs;

final readonly class CreateTenantDTO
{
    public function __construct(
        public string $name,
        public int    $ownerId,
        public string $subdomain,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name:      $data['name'],
            ownerId:   $data['owner_id'],
            subdomain: $data['subdomain'],
        );
    }
}
```

**Rules:**

- `final readonly` — always.
- No setters, no mutation.
- Static factory methods for common sources (`fromRequest`, `fromModel`, `fromArray`).

### 4.3 Jobs (async, tenant-aware)

```php
<?php

declare(strict_types=1);

namespace App\Central\TenantProvisioningModule\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProvisionTenantJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 600;

    public function __construct(
        private readonly Tenant $tenant
    ) {
        $this->onQueue('provisioning');
    }

    public function handle(): void
    {
        // implementation
    }

    public function failed(\Throwable $e): void
    {
        $this->tenant->update(['status' => 'failed']);
        \Log::error("Provisioning failed [{$this->tenant->id}]", ['error' => $e->getMessage()]);
    }
}
```

**Rules:**

- `ShouldBeUnique` on idempotent jobs (provisioning, billing webhooks).
- Always implement `failed()`.
- Always set `$tries` and `$timeout` explicitly.
- Jobs that run inside a tenant context must use `TenantAware` trait from `stancl/tenancy`.

### 4.4 Services (orchestration only)

Use a Service when you need to coordinate multiple Actions or external APIs with shared state. If you only call one Action, skip the Service — it's overhead.

```php
final class BillingService
{
    public function __construct(
        private readonly CreateSubscriptionAction  $createSubscription,
        private readonly ActivatePlanFeaturesAction $activateFeatures,
    ) {}

    public function subscribeTenant(Tenant $tenant, string $priceId, string $paymentMethod): void
    {
        $this->createSubscription->execute($tenant, $priceId, $paymentMethod);
        $this->activateFeatures->execute($tenant);
    }
}
```

### 4.5 Module Service Provider (required for every module)

```php
<?php

declare(strict_types=1);

namespace App\Central\BillingModule\Providers;

use Illuminate\Support\ServiceProvider;

final class BillingModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind contracts
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'billing');
        $this->loadMigrationsFrom(database_path('migrations/central'));

        // Register Livewire components
        // Register Policies
        // Register Event Listeners
    }
}
```

Register in `AppServiceProvider::register()`:

```php
$this->app->register(BillingModuleServiceProvider::class);
```

---

## 5. Multi-Tenancy Rules

- **Never** access tenant data from a Central context. Ever.
- **Never** access central-only resources from Tenant context without explicit cross-DB query.
- All Tenant models must assume `tenancy()->initialized() === true`.
- Jobs touching tenant data: use `TenantAware` trait.
- Cache keys in tenant context: always prefixed with `tenant_{id}_`.
- No `DB::table()` raw queries without explicit connection: `DB::connection('pgsql')` for central, default for tenant.

```php
// Correct — central connection explicit
DB::connection('pgsql')->table('plans')->get();

// Correct — tenant context (connection already switched)
User::query()->where('active', true)->get();
```

---

## 6. Eloquent & Database Rules

- **No** `$fillable = ['*']` or unguarded models.
- **Always** cast types: `protected $casts = ['settings' => 'array', 'trial_ends_at' => 'datetime']`.
- **No** `->get()` without column selection when a subset suffices: `->get(['id', 'email'])`.
- Detect and eliminate N+1: use `->with(['relation'])` in any query inside a loop.
- **No** raw SQL unless Eloquent cannot express it. Use `whereRaw` only as last resort.
- Migrations in `database/migrations/central/` (global) or `database/migrations/tenant/` (per-tenant). Never mix.
- Every migration: explicit `up()` and `down()`.
- Index foreign keys and frequently filtered columns from day one.

---

## 7. HTTP Layer Rules

**Controllers are thin:**

```php
final class TenantController extends Controller
{
    public function store(CreateTenantRequest $request, CreateTenantAction $action): JsonResponse
    {
        $tenant = $action->execute(CreateTenantDTO::fromRequest($request->validated()));

        return response()->json(['tenant_id' => $tenant->id], 201);
    }
}
```

- Zero business logic in controllers.
- One public method per action (RESTful) or one method per Livewire component interaction.
- All input via `FormRequest`. Never `$request->input()` directly in controller.
- Always `$request->validated()`.

**FormRequests:**

```php
final class CreateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Tenant::class);
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:100'],
            'subdomain' => ['required', 'string', 'max:63', 'unique:tenants,subdomain', 'regex:/^[a-z0-9\-]+$/'],
        ];
    }
}
```

---

## 8. Livewire Components

```php
<?php

declare(strict_types=1);

namespace App\Tenant\WorkspaceModule\Livewire;

use Livewire\Component;

final class TeamList extends Component
{
    public function mount(): void
    {
        $this->authorize('viewAny', Team::class);
    }

    public function render(): \Illuminate\View\View
    {
        return view('workspace::livewire.team-list', [
            'teams' => Team::query()->with('members')->paginate(20),
        ]);
    }
}
```

**Rules:**

- `$this->authorize()` in **every** `mount()` and every public action method.
- No business logic in `render()`. Delegate to Actions or Services.
- Namespace views with module prefix: `billing::livewire.subscription-form`.

---

## 9. Testing Rules (Pest)

**File structure mirrors source:**

```
tests/
├── Feature/
│   ├── Central/TenantProvisioningModule/
│   └── Tenant/AuthorizationModule/
└── Unit/
    └── Shared/DTOs/
```

**Mandatory test types per feature:**

1. Happy path
2. Tenant isolation (no data leaks between tenants)
3. Auth/permission boundary (403 on unauthorized)
4. Job failure scenario

```php
it('provisions tenant and isolates data', function () {
    $tenant = Tenant::factory()->create();

    ProvisionTenantJob::dispatchSync($tenant);

    expect($tenant->fresh()->status)->toBe('active');

    tenancy()->initialize($tenant);
    expect(User::count())->toBe(1);
    tenancy()->end();
});

it('does not leak data between tenants', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    tenancy()->initialize($tenantA);
    User::factory()->create();
    tenancy()->end();

    tenancy()->initialize($tenantB);
    expect(User::count())->toBe(0);
    tenancy()->end();
});
```

**Rules:**

- `RefreshDatabase` on every test file.
- `tenancy()->end()` after every tenant initialization in tests.
- Factories for every model — no manual `Model::create()` in tests.
- No `$this->assertTrue()` — use Pest's `expect()` chain exclusively.

---

## 10. Security Rules

- **No** `dd()`, `dump()`, `var_dump()` committed.
- **No** secrets in code — always `env()` or `config()`.
- Authorization check on **every** controller method, Livewire mount, and Job that modifies data.
- Webhook endpoints: always verify signature before processing.
- Rate limit registration, invite, and API endpoints from day one.
- RBAC: use `spatie/laravel-permission` with `teams` enabled. Never skip team_id scoping.

---

## 11. Prohibited Patterns

| Anti-Pattern                                      | Reason                     | Correct Alternative                                            |
| ------------------------------------------------- | -------------------------- | -------------------------------------------------------------- |
| Fat controller                                    | Untestable, violates SRP   | Thin controller → Action                                       |
| Logic in Blade/View                               | Untestable, mixed concerns | Computed property or ViewComposer                              |
| `Model::all()`                                    | Never in production        | `->paginate()` or `->limit()`                                  |
| Sync heavy ops in request                         | Blocks UX, no retry        | Queue + Job                                                    |
| Magic strings for permissions                     | Typo-prone                 | Constants or Enum                                              |
| `\App\User` global import in Tenant               | Wrong model context        | `\App\Tenant\IdentityContext\AuthenticationModule\Models\User` |
| `env()` outside `config()` files                  | Breaks config cache        | Always wrap in `config()`                                      |
| `schema-less` migration rollbacks                 | Data loss                  | Always implement `down()`                                      |
| `public $property` in Livewire without validation | Exposes all data           | Use `#[Validate]` attribute or `validate()`                    |

---

## 12. Naming Conventions

| Type      | Convention               | Example                                        |
| --------- | ------------------------ | ---------------------------------------------- |
| Action    | `VerbNounAction`         | `CreateTenantAction`, `SendWelcomeEmailAction` |
| Job       | `VerbNounJob`            | `ProvisionTenantJob`, `ProcessWebhookJob`      |
| DTO       | `NounDTO`                | `CreateTenantDTO`, `PlanFeaturesDTO`           |
| Service   | `NounService`            | `BillingService`, `FeatureService`             |
| Event     | `NounVerbed` (past)      | `TenantProvisioned`, `SubscriptionCancelled`   |
| Listener  | `VerbNounOnEvent`        | `ActivateFeaturesOnSubscription`               |
| Request   | `VerbNounRequest`        | `CreateTenantRequest`, `UpdateTeamRequest`     |
| Policy    | `NounPolicy`             | `TenantPolicy`, `TeamPolicy`                   |
| Livewire  | `NounContext`            | `TeamList`, `SubscriptionForm`                 |
| Migration | `snake_case_description` | `create_teams_table`, `add_status_to_tenants`  |

---

## 13. Git Workflow

**Branch naming:**

```
feat/module-name/short-description     # New feature
fix/module-name/short-description      # Bug fix
refactor/module-name/short-description # Refactor
test/module-name/short-description     # Tests only
chore/short-description                # Tooling, deps
```

**Commit format (Conventional Commits):**

```
feat(billing): add subscription swap action
fix(provisioning): handle artisan migrate failure in job
test(tenancy): add cross-tenant isolation assertions
refactor(auth): extract login logic to RegisterUserAction
chore(deps): upgrade cashier to 15.2
```

**PR Rules:**

- No PR merges without passing CI (lint + tests + Larastan).
- PR description must include: What changed, Why, How to test.
- Max 400 lines changed per PR. Larger changes = split into smaller PRs.
- Tests required for every PR touching `Actions/`, `Jobs/`, `Services/`.

---

## 14. Step-by-Step Workflow for Every Copilot Request

When generating code for this project, follow this sequence:

```
1. IDENTIFY CONTEXT
   └── Central / Tenant / Shared?
   └── Which module owns this feature?

2. DEFINE DATA SHAPE
   └── Create DTO first if input/output is complex.

3. WRITE THE ACTION / SERVICE
   └── Single responsibility. Inject dependencies via constructor.

4. WRITE THE JOB (if async)
   └── ShouldBeUnique + failed() + explicit queue name.

5. WRITE THE CONTROLLER / LIVEWIRE
   └── Thin. Delegates immediately to Action.
   └── Authorization in mount() or constructor.

6. WRITE THE TEST
   └── Happy path + isolation + boundary (403).

7. REGISTER IN MODULE SERVICE PROVIDER
   └── Routes, views, event listeners, policies.

8. CHECK THE LIST
   └── No fat controller?
   └── No N+1?
   └── No sync heavy operation in HTTP request?
   └── Tenant-aware? (correct connection, TenantAware trait if Job)
   └── Authorization enforced?
   └── Test covers isolation?
```

---

## 15. Copilot Behavior Rules

- **Do not** generate code outside the module structure above.
- **Do not** suggest packages without checking they are in the approved dependency list (`01_Dependencias_Requeridas.md`).
- **Do not** generate migrations that go in `database/migrations/` root — always `central/` or `tenant/`.
- **Do not** add abstractions (Repository, Presenter, Transformer) unless explicitly asked and justified.
- **Do not** generate tests using `PHPUnit` syntax — always Pest.
- **Do** flag if a generated piece of code would break tenant isolation.
- **Do** prefer `final` classes unless inheritance is explicitly required.
- **Do** prefer `readonly` properties in DTOs and Value Objects.
- **Do** use PHP 8.3 syntax: constructor property promotion, readonly, enums, match expressions, named arguments.
