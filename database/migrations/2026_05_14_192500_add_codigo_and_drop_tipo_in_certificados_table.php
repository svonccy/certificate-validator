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
            if (! Schema::hasColumn('certificados', 'codigo_certificado')) {
                $table->string('codigo_certificado')->nullable()->after('dni_titular');
            }

            if (Schema::hasColumn('certificados', 'tipo_certificado')) {
                $table->dropColumn('tipo_certificado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificados', function (Blueprint $table) {
            if (! Schema::hasColumn('certificados', 'tipo_certificado')) {
                $table->string('tipo_certificado')->nullable()->after('dni_titular');
            }

            if (Schema::hasColumn('certificados', 'codigo_certificado')) {
                $table->dropColumn('codigo_certificado');
            }
        });
    }
};
