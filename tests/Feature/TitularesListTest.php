<?php

declare(strict_types=1);

use App\Filament\Resources\Titulares\Pages\ListTitulares;
use App\Models\Certificado;
use App\Models\Titular;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

test('titulares list page loads and displays table records', function (): void {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $titular = Titular::query()->create([
        'dni' => '87654321',
        'nombre_completo' => 'Jane Doe',
    ]);

    Certificado::query()->create([
        'titular_id' => $titular->getKey(),
        'codigo_certificado' => 'CERT-77777',
    ]);

    Livewire::test(ListTitulares::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$titular])
        ->assertTableColumnExists('certificados_count');
});
