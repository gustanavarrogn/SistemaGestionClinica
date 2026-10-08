<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id('paciente_id');
            $table->string('nombre', 120);
            $table->string('telefono', 30)->nullable();
            $table->string('email', 255)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('tipo_de_sangre', 3)->nullable();
            $table->text('alergias')->nullable();
            $table->text('enfermedades_cronicas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};