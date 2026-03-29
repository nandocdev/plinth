<?php

use App\Central\AuthenticationModule\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->withTwoFactor()->create();
    $this->actingAs($user, 'central');

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('central.dashboard'));

    $this->get(route('central.dashboard'))->assertOk();
});

test('authenticated users without 2fa are redirected to security settings', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'central');

    $this->get(route('dashboard'))
        ->assertRedirect(route('security.edit'));

    $this->get(route('central.dashboard'))
        ->assertRedirect(route('security.edit'));
});
