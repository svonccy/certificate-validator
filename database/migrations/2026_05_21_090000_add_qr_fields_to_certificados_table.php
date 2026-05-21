<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificados', function (Blueprint $table) {
            $table->json('datos_qr')->nullable()->after('ruta_pdf_borrador');
            $table->unsignedSmallInteger('qr_pagina')->default(1)->after('datos_qr');
        });
    }

    public function down(): void
    {
        Schema::table('certificados', function (Blueprint $table) {
            $table->dropColumn(['datos_qr', 'qr_pagina']);
        });
    }
};
