<?php

declare(strict_types=1);

use App\Central\AuthenticationModule\Models\User;

test('bloquea login central tras demasiados intentos para una misma credencial e ip', function () {
   $user = User::factory()->create();
   $ipAddress = '10.10.10.10';

   foreach (range(1, 5) as $_) {
      $response = $this->withServerVariables(['REMOTE_ADDR' => $ipAddress])
         ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
         ]);

      $response->assertSessionHasErrors('email');
   }

   $response = $this->withServerVariables(['REMOTE_ADDR' => $ipAddress])
      ->post(route('login.store'), [
         'email' => $user->email,
         'password' => 'wrong-password',
      ]);

   $response->assertStatus(429);
   $this->assertGuest();
});

test('bloquea password spraying en login central por ip', function () {
   $ipAddress = '10.20.20.20';

   foreach (range(1, 20) as $index) {
      $response = $this->withServerVariables(['REMOTE_ADDR' => $ipAddress])
         ->post(route('login.store'), [
            'email' => "spray-{$index}@example.com",
            'password' => 'wrong-password',
         ]);

      $response->assertSessionHasErrors('email');
   }

   $response = $this->withServerVariables(['REMOTE_ADDR' => $ipAddress])
      ->post(route('login.store'), [
         'email' => 'spray-locked@example.com',
         'password' => 'wrong-password',
      ]);

   $response->assertStatus(429);
   $this->assertGuest();
});
