<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

test('users list page loads and displays user records for admins', function (): void {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $operator = User::factory()->create([
        'name' => 'Operator User',
        'email' => 'operator@example.com',
    ]);

    Livewire::test(ListUsers::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$admin, $operator]);
});
