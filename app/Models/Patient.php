<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'paciente_id';
    protected $fillable = ['nombre', 'telefono', 'email', 'birth_date', 'tipo_de_sangre', 'alergias', 'enfermedades_cronicas'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'paciente_id');
    }
}