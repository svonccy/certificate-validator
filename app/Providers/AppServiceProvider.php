<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Certificado;
use App\Observers\CertificadoObserver;
use App\Services\Certificados\SignatureValidatorContract;
use App\Services\Certificados\ValidadorFirmaPdf;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SignatureValidatorContract::class, ValidadorFirmaPdf::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Certificado::observe(CertificadoObserver::class);
    }
}
