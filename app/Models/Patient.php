<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $primaryKey = 'paciente_id';
    protected $fillable = ['nombre', 'telefono', 'email', 'birth_date', 'tipo_de_sangre', 'alergias', 'enfermedades_cronicas'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'paciente_id');
    }
}