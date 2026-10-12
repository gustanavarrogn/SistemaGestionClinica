<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'rol', 'patient_id', 'is_active'];
    protected $hidden = ['password', 'remember_token'];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'paciente_id');
    }

    // Compatibilidad con versiones anteriores: las cuentas vinculadas a médicos
    // ya no tienen acceso en la Fase 2, pero conservamos su referencia histórica.
    public function doctor()
    {
        return $this->hasOne(Doctor::class, 'user_id')->withTrashed();
    }

    public function isDoctor(): bool
    {
        return $this->doctor()->exists();
    }

    public function isManagementAdmin(): bool
    {
        return $this->rol === 'admin' && ! $this->isDoctor();
    }

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean'];
    }
}
