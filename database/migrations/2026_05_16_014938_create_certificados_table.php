<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificados', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('titular_id')->constrained('titulares')->restrictOnDelete();

            $table->string('codigo_certificado')->unique();

            $table->string('estado', 20)->default('PDF_NO_ENCONTRADO')->index();
            $table->timestamp('fecha_firma')->nullable();

            $table->string('ruta_pdf_original')->nullable();
            $table->string('ruta_pdf_borrador')->nullable();
            $table->string('token_borrador', 64)->nullable()->unique();
            $table->string('ruta_pdf_firmado')->nullable();

            $table->json('datos_qr')->nullable();
            $table->unsignedSmallInteger('qr_pagina')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificados');
    }
};
