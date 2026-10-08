<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $primaryKey = 'appointment_id';
    protected $fillable = ['paciente_id', 'doctor_id', 'fecha', 'hora', 'razon', 'status', 'observaciones'];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'paciente_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}