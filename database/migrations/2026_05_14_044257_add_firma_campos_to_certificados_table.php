<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('certificados', function (Blueprint $table) {
            $table->boolean('firma_valida')->default(false)->after('ruta_pdf_firmado');
            $table->timestamp('firma_fecha')->nullable()->after('firma_valida');
            $table->string('firma_serial')->nullable()->after('firma_fecha');
            $table->string('firma_algoritmo')->nullable()->after('firma_serial');
            $table->string('hash_pdf_firmado', 64)->nullable()->after('firma_algoritmo');
            $table->string('firma_notario_nombre')->nullable()->after('hash_pdf_firmado');
            $table->string('firma_notario_documento')->nullable()->after('firma_notario_nombre');
            $table->json('metadatos_firma')->nullable()->after('firma_notario_documento');
            $table->timestamp('validado_en')->nullable()->after('metadatos_firma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificados', function (Blueprint $table) {
            $table->dropColumn([
                'firma_valida',
                'firma_fecha',
                'firma_serial',
                'firma_algoritmo',
                'hash_pdf_firmado',
                'firma_notario_nombre',
                'firma_notario_documento',
                'metadatos_firma',
                'validado_en',
            ]);
        });
    }
};
