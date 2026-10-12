<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'appointment_id';
    protected $fillable = ['paciente_id', 'doctor_id', 'fecha', 'hora', 'razon', 'status', 'observaciones'];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'paciente_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id')->withTrashed();
    }
}