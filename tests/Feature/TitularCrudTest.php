<?php

declare(strict_types=1);

use App\Models\Titular;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('a user can create a titular', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $titular = Titular::query()->create([
        'dni' => '12345678',
        'nombre_completo' => 'Maria Lopez',
    ]);

    expect(Titular::query()->count())->toBe(1);
    expect($titular->dni)->toBe('12345678');
    expect($titular->nombre_completo)->toBe('Maria Lopez');
});

test('dni must be unique', function (): void {
    Titular::query()->create([
        'dni' => '87654321',
        'nombre_completo' => 'Carlos Perez',
    ]);

    $this->expectException(UniqueConstraintViolationException::class);

    Titular::query()->create([
        'dni' => '87654321',
        'nombre_completo' => 'Carlos Rodriguez',
    ]);
});

test('a user can update a titular', function (): void {
    $titular = Titular::query()->create([
        'dni' => '11112222',
        'nombre_completo' => 'Juan Perez',
    ]);

    $titular->update([
        'nombre_completo' => 'Juan Gabriel Perez',
    ]);

    expect($titular->fresh()->nombre_completo)->toBe('Juan Gabriel Perez');
});

test('a user can delete a titular', function (): void {
    $titular = Titular::query()->create([
        'dni' => '33334444',
        'nombre_completo' => 'Pedro Garcia',
    ]);

    expect(Titular::query()->count())->toBe(1);

    $titular->delete();

    expect(Titular::query()->count())->toBe(0);
});
