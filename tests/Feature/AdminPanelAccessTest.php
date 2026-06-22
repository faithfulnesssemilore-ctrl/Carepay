<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
