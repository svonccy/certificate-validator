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
        Schema::create('certificados', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('dni_titular', 8);
            $table->string('nombre_titular');
            $table->string('tipo_certificado');
            $table->string('estado')->default('PENDIENTE');
            $table->string('ruta_pdf_original')->nullable();
            $table->string('ruta_pdf_borrador')->nullable();
            $table->string('ruta_pdf_firmado')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificados');
    }
};
