# Guía de Implementación para Programadores (The Plinth Way)

Esta guía define los estándares técnicos para construir o reconstruir cualquier módulo en Plinth. Todo programador debe seguir este patrón para garantizar consistencia y seguridad.

## 1. El Patrón "Action"

En Plinth, los controladores y componentes Livewire **no contienen lógica de negocio**. Toda la lógica reside en `Actions`.

### A. El DTO (Data Transfer Object)
Antes de ejecutar una acción, los datos deben estar tipados.
**Ubicación:** `app/{Context}/{Module}/DTOs/{ActionName}Data.php`

```php
namespace App\Tenant\IdentityContext\UserManagementModule\DTOs;

final readonly class CreateTenantUserData {
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public \App\Tenant\IdentityContext\UserManagementModule\Enums\TenantRole $role,
    ) {}
}
```

### B. La Clase Action
Debe tener un único método público `execute()`.
**Ubicación:** `app/{Context}/{Module}/Actions/{ActionName}Action.php`

```php
namespace App\Tenant\IdentityContext\UserManagementModule\Actions;

final class CreateTenantUserAction {
    public function execute(CreateTenantUserData $data): User {
        return \DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => \Hash::make($data->password),
                'tenant_id' => tenant('id'), // Solo si no se usa BelongsToTenant auto
            ]);

            $user->assignRole($data->role->value);
            
            event(new TenantUserCreated($user));
            return $user;
        });
    }
}
```

## 2. Estándares de Base de Datos (Single-DB)

### Reglas Críticas para Migraciones:
1. **Columna Tenant:** Todo registro en el contexto Tenant **DEBE** tener `tenant_id`.
2. **Llaves Foráneas:** Las FK deben ser del mismo tipo que la PK del tenant (normalmente `string` o `uuid`).
3. **Índices Compuestos:** Para consultas rápidas, usa índices que incluyan el `tenant_id`.

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id'); // Identificador del cliente
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
    
    $table->string('order_number');
    $table->decimal('total', 15, 2);
    $table->timestamps();

    // Índice crítico para rendimiento en Single-DB
    $table->index(['tenant_id', 'order_number']);
});
```

## 3. Seguridad y Autorización

### Uso de Policies
No uses `auth()->user()->can()`. Usa la integración nativa de Laravel con Policies en Actions o Controllers.

```php
public function update(User $user, Order $order) {
    // La Policy ya sabe que debe filtrar por tenant_id gracias al Global Scope
    return $user->id === $order->user_id; 
}
```

## 4. Frontend con Livewire & Flux

Plinth usa **Flux** para componentes UI consistentes.
1. **Formularios:** Usa clases `Livewire\Form` para manejar el estado del formulario.
2. **Validación:** Valida siempre en el Form Object o en la Action, nunca dejes pasar datos crudos a la DB.

## 5. Checklist para crear un nuevo Módulo
1. [ ] Crear Migración con `tenant_id`.
2. [ ] Crear Modelo con trait `BelongsToTenant`.
3. [ ] Definir Enums para estados/roles.
4. [ ] Crear DTOs para las entradas de datos.
5. [ ] Implementar Actions (Lógica).
6. [ ] Crear Policy de acceso.
7. [ ] Desarrollar UI en Livewire usando Flux.
8. [ ] Registrar el ModuleServiceProvider.
