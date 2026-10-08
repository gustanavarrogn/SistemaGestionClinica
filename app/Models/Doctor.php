<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $primaryKey = 'doctor_id';
    protected $fillable = ['nombre', 'email', 'especialidad'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }
}