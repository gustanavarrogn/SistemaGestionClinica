<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Documento;
use App\Models\Patient;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Fase 3 – Integrante #3 (Base de Datos y Modelos)
 *
 * Consultas (Query Builder y Eloquent) que calculan los totales y
 * agrupaciones usados en los reportes de MediControl.
 *
 * Todos los métodos aceptan filtros opcionales:
 *   $desde    'Y-m-d' o null
 *   $hasta    'Y-m-d' o null
 *   $doctorId id del doctor o null (todos)
 *
 * Uso desde un controlador:
 *   public function index(Request $request, ReporteService $reportes) {
 *       $resumen = $reportes->resumenGeneral($request->desde, $request->hasta);
 *       $porEstado = $reportes->citasPorEstado($request->desde, $request->hasta);
 *       ...
 *   }
 *
 * Las consultas con Query Builder ignoran las citas eliminadas (soft delete)
 * igual que lo hace Eloquent.
 */
class ReporteService
{
    /** Estados en el orden en que se muestran en los reportes. */
    public const ESTADOS = ['Pendiente', 'Confirmado', 'En proceso', 'Completada', 'Cancelada', 'No-show'];

    private const DIAS = [1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles', 5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'];

    /* =====================================================================
     *  Base: citas activas filtradas (Query Builder)
     * ===================================================================== */

    private function citasBase(?string $desde = null, ?string $hasta = null, $doctorId = null): Builder
    {
        return DB::table('appointments')
            ->whereNull('appointments.deleted_at')
            ->when($desde, fn ($q) => $q->where('appointments.fecha', '>=', $desde))
            ->when($hasta, fn ($q) => $q->where('appointments.fecha', '<=', $hasta))
            ->when($doctorId, fn ($q) => $q->where('appointments.doctor_id', $doctorId));
    }

    /* =====================================================================
     *  1. Resumen general (tarjetas del dashboard de reportes)
     * ===================================================================== */

    public function resumenGeneral(?string $desde = null, ?string $hasta = null, $doctorId = null): array
    {
        // Una sola consulta con SUM(CASE ...) para todos los contadores.
        $fila = $this->citasBase($desde, $hasta, $doctorId)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN status = 'Pendiente'  THEN 1 ELSE 0 END) AS pendientes")
            ->selectRaw("SUM(CASE WHEN status = 'Confirmado' THEN 1 ELSE 0 END) AS confirmadas")
            ->selectRaw("SUM(CASE WHEN status = 'En proceso' THEN 1 ELSE 0 END) AS en_proceso")
            ->selectRaw("SUM(CASE WHEN status = 'Completada' THEN 1 ELSE 0 END) AS completadas")
            ->selectRaw("SUM(CASE WHEN status = 'Cancelada'  THEN 1 ELSE 0 END) AS canceladas")
            ->selectRaw("SUM(CASE WHEN status = 'No-show'    THEN 1 ELSE 0 END) AS no_show")
            ->selectRaw('SUM(CASE WHEN archivo_ruta IS NOT NULL THEN 1 ELSE 0 END) AS con_archivo')
            ->selectRaw('COUNT(DISTINCT paciente_id) AS pacientes_atendidos')
            ->first();

        $total = (int) $fila->total;
        $pct = fn ($n) => $total > 0 ? round(((int) $n / $total) * 100, 2) : 0.0;

        return [
            'total_citas'          => $total,
            'pendientes'           => (int) $fila->pendientes,
            'confirmadas'          => (int) $fila->confirmadas,
            'en_proceso'           => (int) $fila->en_proceso,
            'completadas'          => (int) $fila->completadas,
            'canceladas'           => (int) $fila->canceladas,
            'no_show'              => (int) $fila->no_show,
            'con_archivo'          => (int) $fila->con_archivo,
            'pacientes_atendidos'  => (int) $fila->pacientes_atendidos,
            'porcentaje_completadas' => $pct($fila->completadas),
            'porcentaje_canceladas'  => $pct($fila->canceladas),
            'porcentaje_no_show'     => $pct($fila->no_show),
            // Totales del catálogo (Eloquent, ya excluye eliminados)
            'total_pacientes'      => Patient::count(),
            'total_doctores'       => Doctor::count(),
            'total_documentos'     => Documento::query()
                ->when($desde, fn ($q) => $q->where('created_at', '>=', $desde.' 00:00:00'))
                ->when($hasta, fn ($q) => $q->where('created_at', '<=', $hasta.' 23:59:59'))
                ->count(),
        ];
    }

    /* =====================================================================
     *  2. Citas agrupadas por estado
     * ===================================================================== */

    /** @return Collection<int, object{status:string,total:int}> incluye estados con 0 */
    public function citasPorEstado(?string $desde = null, ?string $hasta = null, $doctorId = null): Collection
    {
        $conteos = $this->citasBase($desde, $hasta, $doctorId)
            ->select('status', DB::raw('COUNT(*) AS total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(self::ESTADOS)->map(fn ($estado) => (object) [
            'status' => $estado,
            'total'  => (int) ($conteos[$estado] ?? 0),
        ]);
    }

    /* =====================================================================
     *  3. Citas por doctor (con desglose de estados)
     * ===================================================================== */

    public function citasPorDoctor(?string $desde = null, ?string $hasta = null): Collection
    {
        return $this->citasBase($desde, $hasta)
            ->join('doctors', 'doctors.doctor_id', '=', 'appointments.doctor_id')
            ->select('doctors.doctor_id', 'doctors.nombre', 'doctors.especialidad')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN appointments.status = 'Completada' THEN 1 ELSE 0 END) AS completadas")
            ->selectRaw("SUM(CASE WHEN appointments.status = 'Cancelada'  THEN 1 ELSE 0 END) AS canceladas")
            ->selectRaw("SUM(CASE WHEN appointments.status = 'No-show'    THEN 1 ELSE 0 END) AS no_show")
            ->selectRaw('COUNT(DISTINCT appointments.paciente_id) AS pacientes_distintos')
            ->groupBy('doctors.doctor_id', 'doctors.nombre', 'doctors.especialidad')
            ->orderByDesc('total')
            ->get()
            ->map(function ($fila) {
                foreach (['total', 'completadas', 'canceladas', 'no_show', 'pacientes_distintos'] as $c) {
                    $fila->$c = (int) $fila->$c;
                }
                $fila->porcentaje_completadas = $fila->total > 0
                    ? round($fila->completadas / $fila->total * 100, 2) : 0.0;
                return $fila;
            });
    }

    /* =====================================================================
     *  4. Citas por especialidad
     * ===================================================================== */

    public function citasPorEspecialidad(?string $desde = null, ?string $hasta = null): Collection
    {
        return $this->citasBase($desde, $hasta)
            ->join('doctors', 'doctors.doctor_id', '=', 'appointments.doctor_id')
            ->selectRaw("COALESCE(doctors.especialidad, 'Sin especialidad') AS especialidad")
            ->selectRaw('COUNT(*) AS total')
            ->groupByRaw("COALESCE(doctors.especialidad, 'Sin especialidad')")
            ->orderByDesc('total')
            ->get()
            ->map(fn ($f) => (object) ['especialidad' => $f->especialidad, 'total' => (int) $f->total]);
    }

    /* =====================================================================
     *  5. Citas por mes (tendencia)
     * ===================================================================== */

    /** @return Collection<int, object{mes:string,total:int,completadas:int,canceladas:int}> mes = 'YYYY-MM' */
    public function citasPorMes(?string $desde = null, ?string $hasta = null, $doctorId = null): Collection
    {
        $mes = $this->exprMes('appointments.fecha');

        return $this->citasBase($desde, $hasta, $doctorId)
            ->selectRaw("$mes AS mes")
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN status = 'Completada' THEN 1 ELSE 0 END) AS completadas")
            ->selectRaw("SUM(CASE WHEN status IN ('Cancelada', 'No-show') THEN 1 ELSE 0 END) AS canceladas")
            ->groupByRaw($mes)
            ->orderBy('mes')
            ->get()
            ->map(fn ($f) => (object) [
                'mes'         => $f->mes,
                'total'       => (int) $f->total,
                'completadas' => (int) $f->completadas,
                'canceladas'  => (int) $f->canceladas,
            ]);
    }

    /* =====================================================================
     *  6. Citas por día de la semana
     * ===================================================================== */

    public function citasPorDiaSemana(?string $desde = null, ?string $hasta = null, $doctorId = null): Collection
    {
        $dia = $this->exprDiaSemana('appointments.fecha');

        $conteos = $this->citasBase($desde, $hasta, $doctorId)
            ->selectRaw("$dia AS dia")
            ->selectRaw('COUNT(*) AS total')
            ->groupByRaw($dia)
            ->pluck('total', 'dia');

        return collect(self::DIAS)->map(fn ($nombre, $n) => (object) [
            'dia'   => $nombre,
            'total' => (int) ($conteos[$n] ?? 0),
        ])->values();
    }

    /* =====================================================================
     *  7. Horas con más citas
     * ===================================================================== */

    public function citasPorHora(?string $desde = null, ?string $hasta = null, $doctorId = null): Collection
    {
        $hora = $this->exprHora('appointments.hora');

        return $this->citasBase($desde, $hasta, $doctorId)
            ->selectRaw("$hora AS hora")
            ->selectRaw('COUNT(*) AS total')
            ->groupByRaw($hora)
            ->orderBy('hora')
            ->get()
            ->map(fn ($f) => (object) [
                'hora'  => sprintf('%02d:00', (int) $f->hora),
                'total' => (int) $f->total,
            ]);
    }

    /* =====================================================================
     *  8. Pacientes (Eloquent)
     * ===================================================================== */

    /** Pacientes con más citas en el periodo. */
    public function pacientesFrecuentes(?string $desde = null, ?string $hasta = null, int $limite = 10): Collection
    {
        $filtro = fn ($q) => $q->entreFechas($desde, $hasta);

        return Patient::query()
            ->select('paciente_id', 'nombre', 'telefono', 'email')
            ->whereHas('appointments', $filtro)
            ->withCount([
                'appointments as total_citas' => $filtro,
                'appointments as citas_completadas' => fn ($q) => $filtro($q)->where('status', 'Completada'),
                'appointments as citas_no_show' => fn ($q) => $filtro($q)->where('status', 'No-show'),
            ])
            ->orderByDesc('total_citas')
            ->limit($limite)
            ->get();
    }

    /** Pacientes registrados por mes (según created_at). */
    public function pacientesNuevosPorMes(?string $desde = null, ?string $hasta = null): Collection
    {
        $mes = $this->exprMes('created_at');

        return DB::table('patients')
            ->whereNull('deleted_at')
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->selectRaw("$mes AS mes")
            ->selectRaw('COUNT(*) AS total')
            ->groupByRaw($mes)
            ->orderBy('mes')
            ->get()
            ->map(fn ($f) => (object) ['mes' => $f->mes, 'total' => (int) $f->total]);
    }

    /** Pacientes agrupados por tipo de sangre. */
    public function pacientesPorTipoSangre(): Collection
    {
        return Patient::query()
            ->selectRaw("COALESCE(tipo_de_sangre, 'N/D') AS tipo_de_sangre, COUNT(*) AS total")
            ->groupByRaw("COALESCE(tipo_de_sangre, 'N/D')")
            ->orderByDesc('total')
            ->get()
            ->map(fn ($f) => (object) ['tipo_de_sangre' => $f->tipo_de_sangre, 'total' => (int) $f->total]);
    }

    /* =====================================================================
     *  9. Listados detallados (para tablas, PDF o Excel)
     * ===================================================================== */

    /** Listado completo de citas del periodo con paciente y doctor (Eloquent). */
    public function listadoCitas(?string $desde = null, ?string $hasta = null, $doctorId = null, ?string $estado = null)
    {
        return Appointment::with(['patient:paciente_id,nombre,telefono', 'doctor:doctor_id,nombre,especialidad'])
            ->entreFechas($desde, $hasta)
            ->delDoctor($doctorId)
            ->conEstado($estado)
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();
    }

    /** Citas que tienen archivo adjunto. */
    public function citasConArchivo(?string $desde = null, ?string $hasta = null, $doctorId = null)
    {
        return Appointment::with(['patient:paciente_id,nombre', 'doctor:doctor_id,nombre'])
            ->whereNotNull('archivo_ruta')
            ->entreFechas($desde, $hasta)
            ->delDoctor($doctorId)
            ->orderByDesc('archivo_subido_en')
            ->get();
    }

    /* =====================================================================
     *  10. Documentos
     * ===================================================================== */

    private function documentosBase(?string $desde = null, ?string $hasta = null): Builder
    {
        return DB::table('documentos')
            ->whereNull('documentos.deleted_at')
            ->when($desde, fn ($q) => $q->where('documentos.created_at', '>=', $desde.' 00:00:00'))
            ->when($hasta, fn ($q) => $q->where('documentos.created_at', '<=', $hasta.' 23:59:59'));
    }

    /** Documentos por tipo (incluye tipos con 0) y espacio que ocupan en KB. */
    public function documentosPorTipo(?string $desde = null, ?string $hasta = null): Collection
    {
        $filas = $this->documentosBase($desde, $hasta)
            ->select('tipo')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COALESCE(SUM(peso_kb), 0) AS peso_kb')
            ->groupBy('tipo')
            ->get()
            ->keyBy('tipo');

        return collect(Documento::TIPOS)->map(fn ($tipo) => (object) [
            'tipo'  => $tipo,
            'total' => (int) ($filas[$tipo]->total ?? 0),
            'peso_kb' => (int) ($filas[$tipo]->peso_kb ?? 0),
        ]);
    }

    /** Documentos subidos por mes 'YYYY-MM'. */
    public function documentosPorMes(?string $desde = null, ?string $hasta = null): Collection
    {
        $mes = $this->exprMes('documentos.created_at');

        return $this->documentosBase($desde, $hasta)
            ->selectRaw("$mes AS mes")
            ->selectRaw('COUNT(*) AS total')
            ->groupByRaw($mes)
            ->orderBy('mes')
            ->get()
            ->map(fn ($f) => (object) ['mes' => $f->mes, 'total' => (int) $f->total]);
    }

    /** Pacientes con más documentos. */
    public function documentosPorPaciente(?string $desde = null, ?string $hasta = null, int $limite = 10): Collection
    {
        return $this->documentosBase($desde, $hasta)
            ->join('patients', 'patients.paciente_id', '=', 'documentos.paciente_id')
            ->select('patients.paciente_id', 'patients.nombre')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('patients.paciente_id', 'patients.nombre')
            ->orderByDesc('total')
            ->limit($limite)
            ->get()
            ->map(function ($f) { $f->total = (int) $f->total; return $f; });
    }

    /** Listado de documentos con paciente, cita y quién lo subió (Eloquent). */
    public function listadoDocumentos(?string $desde = null, ?string $hasta = null, $pacienteId = null, ?string $tipo = null)
    {
        return Documento::with(['patient:paciente_id,nombre', 'appointment:appointment_id,fecha,hora', 'usuario:id,name'])
            ->entreFechas($desde, $hasta)
            ->delPaciente($pacienteId)
            ->delTipo($tipo)
            ->latest()
            ->get();
    }

    /* =====================================================================
     *  Expresiones de fecha según el motor (MySQL en Laragon; SQLite en pruebas)
     * ===================================================================== */

    private function driver(): string
    {
        return DB::connection()->getDriverName();
    }

    private function exprMes(string $columna): string
    {
        return match ($this->driver()) {
            'sqlite' => "strftime('%Y-%m', $columna)",
            'pgsql'  => "to_char($columna, 'YYYY-MM')",
            default  => "DATE_FORMAT($columna, '%Y-%m')", // mysql / mariadb
        };
    }

    /** 1 = domingo ... 7 = sábado (igual que DAYOFWEEK de MySQL). */
    private function exprDiaSemana(string $columna): string
    {
        return match ($this->driver()) {
            'sqlite' => "(CAST(strftime('%w', $columna) AS INTEGER) + 1)",
            'pgsql'  => "(EXTRACT(DOW FROM $columna)::int + 1)",
            default  => "DAYOFWEEK($columna)",
        };
    }

    private function exprHora(string $columna): string
    {
        return match ($this->driver()) {
            'sqlite' => "CAST(strftime('%H', $columna) AS INTEGER)",
            'pgsql'  => "EXTRACT(HOUR FROM $columna)::int",
            default  => "HOUR($columna)",
        };
    }
}
