<?php

declare(strict_types=1);

use App\Models\Certificado;
use App\Models\FirmaConfianza;
use App\Models\Titular;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('administrators have full access to all resources', function () {
    $admin = User::factory()->admin()->create();

    // Check Titular permissions
    expect($admin->can('viewAny', Titular::class))->toBeTrue();
    expect($admin->can('view', new Titular))->toBeTrue();
    expect($admin->can('create', Titular::class))->toBeTrue();
    expect($admin->can('update', new Titular))->toBeTrue();
    expect($admin->can('delete', new Titular))->toBeTrue();

    // Check Certificado permissions
    expect($admin->can('viewAny', Certificado::class))->toBeTrue();
    expect($admin->can('view', new Certificado))->toBeTrue();
    expect($admin->can('create', Certificado::class))->toBeTrue();
    expect($admin->can('update', new Certificado))->toBeTrue();
    expect($admin->can('delete', new Certificado))->toBeTrue();

    // Check FirmaConfianza permissions
    expect($admin->can('viewAny', FirmaConfianza::class))->toBeTrue();
    expect($admin->can('view', new FirmaConfianza))->toBeTrue();
    expect($admin->can('create', FirmaConfianza::class))->toBeTrue();
    expect($admin->can('update', new FirmaConfianza))->toBeTrue();
    expect($admin->can('delete', new FirmaConfianza))->toBeTrue();

    // Check User permissions
    expect($admin->can('viewAny', User::class))->toBeTrue();
    expect($admin->can('view', new User))->toBeTrue();
    expect($admin->can('create', User::class))->toBeTrue();
    expect($admin->can('update', new User))->toBeTrue();
    expect($admin->can('delete', User::factory()->create()))->toBeTrue();
    expect($admin->can('delete', $admin))->toBeFalse(); // Cannot delete self
});

test('operators have access to certificados and titulares but cannot delete them', function () {
    $operator = User::factory()->create(); // default role is operador

    // Check Titular permissions
    expect($operator->can('viewAny', Titular::class))->toBeTrue();
    expect($operator->can('view', new Titular))->toBeTrue();
    expect($operator->can('create', Titular::class))->toBeTrue();
    expect($operator->can('update', new Titular))->toBeTrue();
    expect($operator->can('delete', new Titular))->toBeFalse();

    // Check Certificado permissions
    expect($operator->can('viewAny', Certificado::class))->toBeTrue();
    expect($operator->can('view', new Certificado))->toBeTrue();
    expect($operator->can('create', Certificado::class))->toBeTrue();
    expect($operator->can('update', new Certificado))->toBeTrue();
    expect($operator->can('delete', new Certificado))->toBeFalse();
});

test('operators cannot access or manage firmaconfianza', function () {
    $operator = User::factory()->create(); // default role is operador

    // Check FirmaConfianza permissions
    expect($operator->can('viewAny', FirmaConfianza::class))->toBeFalse();
    expect($operator->can('view', new FirmaConfianza))->toBeFalse();
    expect($operator->can('create', FirmaConfianza::class))->toBeFalse();
    expect($operator->can('update', new FirmaConfianza))->toBeFalse();
    expect($operator->can('delete', new FirmaConfianza))->toBeFalse();
});

test('operators cannot access or manage users', function () {
    $operator = User::factory()->create(); // default role is operador

    // Check User permissions
    expect($operator->can('viewAny', User::class))->toBeFalse();
    expect($operator->can('view', new User))->toBeFalse();
    expect($operator->can('create', User::class))->toBeFalse();
    expect($operator->can('update', new User))->toBeFalse();
    expect($operator->can('delete', new User))->toBeFalse();
});

test('make:filament-user command creates a user with admin role', function () {
    $_SERVER['argv'] = ['artisan', 'make:filament-user'];

    $this->artisan('make:filament-user', [
        '--name' => 'Admin User',
        '--email' => 'admin@test.com',
        '--password' => 'password123',
    ])->assertExitCode(0);

    $user = User::where('email', 'admin@test.com')->first();
    expect($user)->not->toBeNull();
    expect($user->role->value)->toBe('admin');
});
