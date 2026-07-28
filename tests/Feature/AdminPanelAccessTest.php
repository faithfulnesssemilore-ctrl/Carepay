<?php

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

it('prevents guests from accessing the filament admin panel', function () {
    $this->get('/admin')
        ->assertRedirect('/admin/login');
});

it('admin users can see the admin login', function () {
    // Verify the admin login page is accessible
    $this->get('/admin/login')
        ->assertStatus(200);
});

it('allows configured admin emails to access the panel even before their role is promoted', function () {
    putenv('ADMIN_EMAIL=render-admin@example.com');

    $user = User::factory()->create([
        'email' => 'render-admin@example.com',
        'role' => 0,
        'status' => 'active',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/admin');

    expect($response->getStatusCode())->not->toBe(403);
});

it('allows configured admin emails to access the panel even before role promotion', function () {
    config(['app.admin_email' => 'render-admin@example.com']);

    $user = User::factory()->create([
        'email' => 'render-admin@example.com',
        'role' => 0,
        'status' => 'active',
        'email_verified_at' => now(),
    ]);

    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new RoleMiddleware;
    $response = $middleware->handle($request, function (Request $request): Response {
        return new Response('ok');
    }, 'admin');

    expect($response->getContent())->toBe('ok');
    $user->refresh();
    expect($user->role)->toBe(1);
});
