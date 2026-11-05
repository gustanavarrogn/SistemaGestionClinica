<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Fase 3 – Integrante #3 (Base de Datos y Modelos)
 *
 * Documento subido al sistema. Pertenece a un paciente y opcionalmente a una cita.
 */
class Documento extends Model
{
    use SoftDeletes;

    protected $table = 'documentos';
    protected $primaryKey = 'documento_id';

    /** Tipos permitidos (mismos valores que el enum de la migración). */
    public const TIPOS = ['Receta', 'Resultado de laboratorio', 'Orden médica', 'Imagen médica', 'Constancia', 'Otro'];

    /** Disco donde se guardan los archivos. */
    public const DISCO = 'public';

    protected $fillable = [
        'paciente_id', 'appointment_id', 'subido_por',
        'tipo', 'titulo', 'descripcion',
        'ruta', 'nombre_original', 'mime', 'tamano',
    ];

    protected function casts(): array
    {
        return [
            'tamano' => 'integer',
        ];
    }

    /* ---------------- Relaciones ---------------- */

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'paciente_id')->withTrashed();
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id')->withTrashed();
    }

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    /* ---------------- Atributos de ayuda ---------------- */

    /** URL pública del archivo (requiere `php artisan storage:link`). */
    public function getUrlAttribute(): ?string
    {
        return $this->ruta ? Storage::disk(self::DISCO)->url($this->ruta) : null;
    }

    /** Tamaño legible: "245 KB", "1.3 MB". */
    public function getTamanoLegibleAttribute(): string
    {
        $bytes = (int) $this->tamano;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1).' MB';
        if ($bytes >= 1024)    return round($bytes / 1024).' KB';
        return $bytes.' B';
    }

    public function esPdf(): bool
    {
        return $this->mime === 'application/pdf';
    }

    public function esImagen(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function archivoExiste(): bool
    {
        return $this->ruta && Storage::disk(self::DISCO)->exists($this->ruta);
    }

    /* ---------------- Scopes (filtros) ---------------- */

    public function scopeDelPaciente(Builder $query, $pacienteId): Builder
    {
        return $query->when($pacienteId, fn ($q) => $q->where('paciente_id', $pacienteId));
    }

    public function scopeDeLaCita(Builder $query, $appointmentId): Builder
    {
        return $query->when($appointmentId, fn ($q) => $q->where('appointment_id', $appointmentId));
    }

    public function scopeDelTipo(Builder $query, ?string $tipo): Builder
    {
        return $query->when($tipo, fn ($q) => $q->where('tipo', $tipo));
    }

    /** Documentos subidos entre dos fechas 'Y-m-d' (cualquiera puede ser null). */
    public function scopeEntreFechas(Builder $query, ?string $desde, ?string $hasta): Builder
    {
        return $query
            ->when($desde, fn ($q) => $q->where('created_at', '>=', $desde.' 00:00:00'))
            ->when($hasta, fn ($q) => $q->where('created_at', '<=', $hasta.' 23:59:59'));
    }

    /* ---------------- Borrado del archivo físico ---------------- */

    protected static function booted(): void
    {
        // Con delete() normal (soft delete) el archivo se conserva.
        // Solo al eliminar definitivamente (forceDelete) se borra del disco.
        static::forceDeleted(function (Documento $documento) {
            if ($documento->ruta) {
                Storage::disk(self::DISCO)->delete($documento->ruta);
            }
        });
    }
}
