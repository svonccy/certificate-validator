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

            $table->string('estado', 20)->default('PENDIENTE')->index();
            $table->timestamp('fecha_emision')->nullable();

            $table->string('ruta_pdf_original')->nullable();
            $table->string('ruta_pdf_borrador')->nullable();

            $table->string('token_borrador', 64)->nullable()->unique();
            $table->string('ruta_pdf_firmado')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificados');
    }
};
