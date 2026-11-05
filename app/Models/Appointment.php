<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Appointment extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'appointment_id';
    protected $fillable = [
        'paciente_id', 'doctor_id', 'fecha', 'hora', 'razon', 'status', 'observaciones',
        // Fase 3: archivo adjunto de la cita
        'archivo_ruta', 'archivo_nombre_original', 'archivo_mime', 'archivo_tamano', 'archivo_subido_en',
    ];

    protected function casts(): array
    {
        return [
            'archivo_tamano'    => 'integer',
            'archivo_subido_en' => 'datetime',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'paciente_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id')->withTrashed();
    }

    // Fase 3: documentos asociados a esta cita
    public function documentos()
    {
        return $this->hasMany(Documento::class, 'appointment_id');
    }

    /* ---------------- Fase 3: archivo adjunto ---------------- */

    public function tieneArchivo(): bool
    {
        return ! empty($this->archivo_ruta);
    }

    /** URL pública del archivo (requiere `php artisan storage:link`). */
    public function getArchivoUrlAttribute(): ?string
    {
        return $this->tieneArchivo() ? Storage::disk('public')->url($this->archivo_ruta) : null;
    }

    /* ---------------- Fase 3: scopes para reportes ---------------- */

    /** Citas entre dos fechas (cualquiera puede ser null). */
    public function scopeEntreFechas(Builder $query, ?string $desde, ?string $hasta): Builder
    {
        return $query
            ->when($desde, fn ($q) => $q->where('fecha', '>=', $desde))
            ->when($hasta, fn ($q) => $q->where('fecha', '<=', $hasta));
    }

    public function scopeDelDoctor(Builder $query, $doctorId): Builder
    {
        return $query->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId));
    }

    public function scopeConEstado(Builder $query, ?string $estado): Builder
    {
        return $query->when($estado, fn ($q) => $q->where('status', $estado));
    }
}
