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
 * Lo usa DocumentController (subir, listar, descargar y eliminar).
 */
class Documento extends Model
{
    use SoftDeletes;

    protected $table = 'documentos';
    protected $primaryKey = 'documento_id';

    /** Tipos permitidos (mismos valores que el enum y que DocumentController). */
    public const TIPOS = ['receta', 'examen', 'otro'];

    /** Disco privado donde DocumentController guarda los archivos. */
    public const DISCO = 'local';

    protected $fillable = [
        'paciente_id', 'appointment_id', 'subido_por',
        'tipo', 'nombre_original', 'ruta', 'mime', 'peso_kb',
    ];

    protected function casts(): array
    {
        return [
            'peso_kb' => 'integer',
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

    /** Usuario que subió el documento (DocumentController usa with('usuario')). */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    /* ---------------- Atributos de ayuda ---------------- */

    /** Enlace de descarga (pasa por DocumentController@descargar, que valida permisos). */
    public function getUrlDescargaAttribute(): string
    {
        return route('documentos.descargar', $this->documento_id);
    }

    /** Tamaño legible: "245 KB", "1.3 MB". */
    public function getTamanoLegibleAttribute(): string
    {
        $kb = (int) $this->peso_kb;
        return $kb >= 1024 ? round($kb / 1024, 1).' MB' : $kb.' KB';
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
}
