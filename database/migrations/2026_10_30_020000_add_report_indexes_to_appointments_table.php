<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3 – Integrante #3 (Base de Datos y Modelos)
 *
 * Índices para que las consultas de los reportes (filtrar por rango de
 * fechas y agrupar por estado / doctor) sean rápidas aunque haya muchas citas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->index('fecha', 'appointments_fecha_index');
            $table->index('status', 'appointments_status_index');
            $table->index(['doctor_id', 'fecha'], 'appointments_doctor_fecha_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasIndex('appointments', 'appointments_doctor_id_foreign')) {
            Schema::table('appointments', function (Blueprint $table) {
                // MySQL usa el índice (doctor_id, fecha) para la llave foránea de doctor_id
                // y elimina el índice propio de la FK. Antes de quitarlo se vuelve a crear
                // un índice solo en doctor_id; si no, MySQL no permite el rollback.
                $table->index('doctor_id', 'appointments_doctor_id_foreign');
            });
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_fecha_index');
            $table->dropIndex('appointments_status_index');
            $table->dropIndex('appointments_doctor_fecha_index');
        });
    }
};
