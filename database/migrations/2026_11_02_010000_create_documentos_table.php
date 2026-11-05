<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3 – Integrante #3 (Base de Datos y Modelos)
 *
 * Tabla de documentos subidos al sistema (recetas, resultados de laboratorio,
 * órdenes médicas, imágenes, etc.). Cada documento pertenece a un paciente y,
 * de forma opcional, a una cita. Un paciente o una cita pueden tener varios.
 *
 * En la base de datos solo se guarda la RUTA; el archivo físico queda en
 * storage/app/public/documentos (disco "public").
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

            // Datos descriptivos
            $table->enum('tipo', ['Receta', 'Resultado de laboratorio', 'Orden médica', 'Imagen médica', 'Constancia', 'Otro'])
                  ->default('Otro');
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();

            // Datos del archivo
            $table->string('ruta', 255);                 // ej: "documentos/7f3a...c1.pdf"
            $table->string('nombre_original', 255);      // ej: "resultado_hemograma.pdf"
            $table->string('mime', 100)->nullable();     // ej: "application/pdf"
            $table->unsignedInteger('tamano')->nullable(); // bytes

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
