<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3 – Integrante #3 (Base de Datos y Modelos)
 *
 * Agrega a las citas las columnas necesarias para guardar el archivo que se
 * sube (receta, resultado de laboratorio, orden médica, etc.).
 * En la base de datos solo se guarda la RUTA del archivo; el archivo físico
 * queda en storage/app/public/citas (disco "public").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Ruta relativa dentro del disco, ej: "citas/7f3a...c1.pdf"
            $table->string('archivo_ruta', 255)->nullable()->after('observaciones');
            // Nombre con el que el usuario subió el archivo, ej: "receta_octubre.pdf"
            $table->string('archivo_nombre_original', 255)->nullable()->after('archivo_ruta');
            // Tipo MIME, ej: "application/pdf", "image/jpeg"
            $table->string('archivo_mime', 100)->nullable()->after('archivo_nombre_original');
            // Tamaño en bytes
            $table->unsignedInteger('archivo_tamano')->nullable()->after('archivo_mime');
            // Fecha en que se subió el archivo
            $table->timestamp('archivo_subido_en')->nullable()->after('archivo_tamano');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'archivo_ruta',
                'archivo_nombre_original',
                'archivo_mime',
                'archivo_tamano',
                'archivo_subido_en',
            ]);
        });
    }
};
