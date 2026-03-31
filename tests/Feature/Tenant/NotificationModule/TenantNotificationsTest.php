<?php

declare(strict_types=1);

use App\Central\TenantProvisioningModule\Models\Domain;
use App\Central\TenantProvisioningModule\Models\Tenant;
use App\Tenant\AuthenticationModule\Models\User as TenantUser;
use App\Tenant\FeatureFlagsModule\Http\Middleware\EnforcePlanUsageLimits;
use App\Tenant\NotificationModule\Livewire\TenantNotificationsCenter;
use App\Tenant\NotificationModule\Notifications\TenantMailDatabaseNotification;
use App\Tenant\UserManagementModule\Actions\SeedDefaultRolesAction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;

function createNotificationTenant(string $id): Tenant {
   /** @var Tenant $tenant */
   $tenant = Tenant::withoutEvents(fn() => Tenant::query()->create([
      'id' => $id,
      'name' => 'Tenant ' . $id,
      'status' => 'active',
      'region' => 'us-east-1',
      'tenancy_db_name' => 'tenant_' . str_replace('-', '_', $id),
   ]));

   Domain::query()->create([
      'tenant_id' => $tenant->id,
      'domain' => $id . '.localhost',
      'verified_at' => now(),
   ]);

   return $tenant;
}

beforeEach(function (): void {
   config()->set('tenancy.bootstrappers', [CacheTenancyBootstrapper::class]);
   $this->withoutMiddleware(EnforcePlanUsageLimits::class);

   if (! Schema::hasTable('notifications')) {
      Schema::create('notifications', function (Blueprint $table): void {
         $table->uuid('id')->primary();
         $table->string('type');
         $table->morphs('notifiable');
         $table->text('data');
         $table->timestamp('read_at')->nullable();
         $table->timestamps();
      });
   }
});

test('guest es redirigido al login en notifications tenant', function (): void {
   createNotificationTenant('notif-guest');

   $this->get('http://notif-guest.localhost/notifications')
      ->assertRedirect('http://notif-guest.localhost/login');
});

test('admin envia notificacion por mail y database', function (): void {
   Notification::fake();

   $tenant = createNotificationTenant('notif-admin');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $admin = TenantUser::factory()->create(['email_verified_at' => now()]);
      $admin->assignRole('admin');

      $member = TenantUser::factory()->create(['email_verified_at' => now()]);
      $member->assignRole('member');

      Livewire::actingAs($admin, 'tenant')
         ->test(TenantNotificationsCenter::class)
         ->set('form.subject', 'Mantenimiento')
         ->set('form.message', 'Habra mantenimiento esta noche.')
         ->set('form.targetRole', 'member')
         ->call('send')
         ->assertHasNoErrors()
         ->assertSet('successMessage', 'Notificación enviada a 1 usuario(s).');

      Notification::assertSentTo($member, TenantMailDatabaseNotification::class, function (
         TenantMailDatabaseNotification $notification,
      ) use ($member): bool {
         $channels = $notification->via($member);

         return in_array('mail', $channels, true) && in_array('database', $channels, true);
      });
   } finally {
      tenancy()->end();
   }
});

test('member puede ver inbox y marcar como leida su notificacion', function (): void {
   $tenant = createNotificationTenant('notif-member');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();
      app(PermissionRegistrar::class)->forgetCachedPermissions();

      $admin = TenantUser::factory()->create(['email_verified_at' => now()]);
      $admin->assignRole('admin');

      $member = TenantUser::factory()->create(['email_verified_at' => now()]);
      $member->assignRole('member');

      $admin->notify(new TenantMailDatabaseNotification(
         subject: 'Aviso interno',
         message: 'Mensaje para member',
         sentBy: 'Sistema',
      ));

      $member->notify(new TenantMailDatabaseNotification(
         subject: 'Tu aviso',
         message: 'Solo tu debes verlo',
         sentBy: 'Admin',
      ));

      $notificationId = (string) $member->notifications()->latest('created_at')->value('id');

      Livewire::actingAs($member, 'tenant')
         ->test(TenantNotificationsCenter::class)
         ->assertSee('Tu aviso')
         ->assertDontSee('Aviso interno')
         ->call('markAsRead', $notificationId)
         ->assertHasNoErrors();

      $readAt = $member->notifications()->where('id', $notificationId)->value('read_at');
      expect($readAt)->not->toBeNull();
   } finally {
      tenancy()->end();
   }
});

test('usuario member no puede enviar notificaciones', function (): void {
   $tenant = createNotificationTenant('notif-forbidden');

   tenancy()->initialize($tenant);

   try {
      app(SeedDefaultRolesAction::class)->execute();

      $member = TenantUser::factory()->create(['email_verified_at' => now()]);
      $member->assignRole('member');

      Livewire::actingAs($member, 'tenant')
         ->test(TenantNotificationsCenter::class)
         ->set('form.subject', 'Intento')
         ->set('form.message', 'No deberia enviar')
         ->set('form.targetRole', 'all')
         ->call('send')
         ->assertForbidden();
   } finally {
      tenancy()->end();
   }
});
