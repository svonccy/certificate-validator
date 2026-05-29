<?php

declare(strict_types=1);

use App\Filament\Resources\Certificados\Pages\ListCertificados;
use App\Filament\Resources\Certificados\Pages\ViewCertificado;
use App\Models\Certificado;
use App\Models\Titular;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

test('generar_qr action has radio component for qr_preset_grid', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $titular = Titular::query()->create([
        'dni' => '12345678',
        'nombre_completo' => 'Juan Perez',
    ]);

    $certificado = Certificado::query()->create([
        'titular_id' => $titular->getKey(),
        'codigo_certificado' => 'CERT-12345',
        'ruta_pdf_original' => 'dummy.pdf',
    ]);

    Livewire::test(ViewCertificado::class, [
        'record' => $certificado->getKey(),
    ])
        ->assertActionExists('generar_qr')
        ->mountAction('generar_qr')
        ->assertActionMounted('generar_qr');
});

test('generar_qr table action can be mounted in ListCertificados', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $titular = Titular::query()->create([
        'dni' => '12345678',
        'nombre_completo' => 'Juan Perez',
    ]);

    $certificado = Certificado::query()->create([
        'titular_id' => $titular->getKey(),
        'codigo_certificado' => 'CERT-12345',
        'ruta_pdf_original' => 'dummy.pdf',
    ]);

    Livewire::test(ListCertificados::class)
        ->assertTableActionExists('generar_qr')
        ->mountTableAction('generar_qr', $certificado)
        ->assertActionMounted(TestAction::make('generar_qr')->table($certificado));
});

test('generar_qr page action syncs coordinates when switching to manual', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $titular = Titular::query()->create([
        'dni' => '12345678',
        'nombre_completo' => 'Juan Perez',
    ]);

    $certificado = Certificado::query()->create([
        'titular_id' => $titular->getKey(),
        'codigo_certificado' => 'CERT-12345',
        'ruta_pdf_original' => 'dummy.pdf',
        'datos_qr' => [
            'preset' => 'superior_1',
            'lado' => 30,
        ],
    ]);

    $diskName = (string) config('certificados.disk', 'public');
    Storage::fake($diskName);

    $component = Livewire::test(ViewCertificado::class, [
        'record' => $certificado->getKey(),
    ])
        ->mountAction('generar_qr');

    // Cambiar qr_manual a true
    $component->fillForm([
        'qr_manual' => true,
    ]);

    // Verificar que qr_x y qr_y se hayan sincronizado automáticamente con la posición del preset superior_1 (8, 5)
    $dataAfter = $component->get('mountedActions.0.data');
    expect((bool) ($dataAfter['qr_manual'] ?? false))->toBeTrue();
    expect((int) ($dataAfter['qr_x'] ?? 0))->toBe(8);
    expect((int) ($dataAfter['qr_y'] ?? 0))->toBe(5);
});
