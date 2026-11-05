<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3 – Integrante #3 (Base de Datos y Modelos)
 *
 * Tabla de documentos subidos al sistema (recetas, exámenes, otros).
 * Cada documento pertenece a un paciente y, de forma opcional, a una cita.
 * Un paciente o una cita pueden tener varios documentos.
 *
 * Las columnas coinciden con lo que guarda DocumentController@store.
 * En la base de datos solo se guarda la RUTA; el archivo físico queda en
 * storage/app/private/documentos (disco "local", privado). Se descarga
 * por la ruta documentos.descargar, que valida los permisos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id('documento_id');

            // A quién pertenece el documento
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('appointment_id')->nullable();
            // Usuario que lo subió (si se borra el usuario, el documento se conserva)
            $table->unsignedBigInteger('subido_por')->nullable();

            // Tipo de documento (mismos valores que valida DocumentController)
            $table->enum('tipo', ['receta', 'examen', 'otro'])->default('otro');

            // Datos del archivo
            $table->string('nombre_original', 255);      // ej: "resultado_hemograma.pdf"
            $table->string('ruta', 255);                 // ej: "documentos/doc_3_1730000000.pdf"
            $table->string('mime', 100)->nullable();     // ej: "application/pdf"
            $table->unsignedInteger('peso_kb')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Llaves foráneas
            $table->foreign('paciente_id')->references('paciente_id')->on('patients')->cascadeOnDelete();
            $table->foreign('appointment_id')->references('appointment_id')->on('appointments')->nullOnDelete();
            $table->foreign('subido_por')->references('id')->on('users')->nullOnDelete();

            // Índices para listados y reportes
            $table->index('tipo', 'documentos_tipo_index');
            $table->index('created_at', 'documentos_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
