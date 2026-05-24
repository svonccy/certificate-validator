<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Certificados\EditorPdfContract;
use App\Services\Certificados\EditorPdfFpdi;
use App\Services\Certificados\NormalizadorPdfCli;
use App\Services\Certificados\NormalizadorPdfContract;
use App\Services\Certificados\ValidadorFirmaContract;
use App\Services\Certificados\ValidadorFirmaPdf;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ValidadorFirmaContract::class, ValidadorFirmaPdf::class);
        $this->app->bind(EditorPdfContract::class, EditorPdfFpdi::class);
        $this->app->bind(NormalizadorPdfContract::class, NormalizadorPdfCli::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
