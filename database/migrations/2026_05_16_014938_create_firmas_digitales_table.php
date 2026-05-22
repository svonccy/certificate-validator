<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firmas_digitales', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('certificado_id')->constrained('certificados')->cascadeOnDelete();

            $table->boolean('es_valida')->default(false);
            $table->timestamp('fecha_firma')->nullable();
            $table->string('serial')->nullable()->index();
            $table->string('algoritmo')->nullable();
            $table->string('hash_documento', 255);

            $table->string('notario_nombre')->nullable();
            $table->string('notario_documento')->nullable();
            $table->json('metadatos_completos')->nullable();
            $table->timestamp('fecha_validacion_local')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('firmas_digitales');
    }
};
