<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Separador de columnas del CSV.
    // Excel en espanol espera punto y coma; con coma mete todo en una sola columna.
    const SEPARADOR_CSV = ';';

    public function index(Request $request)
    {
        $datos = $this->reunirDatos($request);

        return view('reportes.index', $datos);
    }

    public function exportarPdf(Request $request)
    {
        $datos = $this->reunirDatos($request);

        $pdf = Pdf::loadView('reportes.pdf', $datos);

        return $pdf->download('reporte_medicontrol_'.now()->format('Y-m-d').'.pdf');
    }

    public function exportarExcel(Request $request)
    {
        $datos = $this->reunirDatos($request);

        $nombre = 'reporte_medicontrol_'.now()->format('Y-m-d').'.csv';

        $cabeceras = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$nombre.'"',
        ];

        $salida = function () use ($datos) {
            $archivo = fopen('php://output', 'w');
            $sep     = self::SEPARADOR_CSV;

            fwrite($archivo, "\xEF\xBB\xBF");

            fputcsv($archivo, ['Reporte MediControl'], $sep);
            fputcsv($archivo, ['Periodo', $datos['desde'].' al '.$datos['hasta']], $sep);

            fputcsv($archivo, [], $sep);
            fputcsv($archivo, ['Resumen'], $sep);
            fputcsv($archivo, ['Total de citas', $datos['totalCitas']], $sep);
            fputcsv($archivo, ['Citas sin asistencia', $datos['noAsistio']], $sep);
            fputcsv($archivo, ['Tasa de inasistencia', $datos['tasaInasistencia'].'%'], $sep);

            fputcsv($archivo, [], $sep);
            fputcsv($archivo, ['Citas por estado'], $sep);
            fputcsv($archivo, ['Estado', 'Cantidad'], $sep);
            foreach ($datos['porEstado'] as $fila) {
                fputcsv($archivo, [$fila->status, $fila->total], $sep);
            }

            fputcsv($archivo, [], $sep);
            fputcsv($archivo, ['Citas por doctora'], $sep);
            fputcsv($archivo, ['Doctora', 'Cantidad'], $sep);
            foreach ($datos['porDoctora'] as $fila) {
                fputcsv($archivo, [$fila->nombre, $fila->total], $sep);
            }

            fputcsv($archivo, [], $sep);
            fputcsv($archivo, ['Citas por mes'], $sep);
            fputcsv($archivo, ['Mes', 'Cantidad'], $sep);
            foreach ($datos['porMes'] as $fila) {
                fputcsv($archivo, [$fila->mes, $fila->total], $sep);
            }

            fclose($archivo);
        };

        return response()->stream($salida, 200, $cabeceras);
    }

    private function reunirDatos(Request $request): array
    {
        $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ], [
            'hasta.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ]);

        $desde = $request->input('desde', Carbon::today()->startOfMonth()->toDateString());
        $hasta = $request->input('hasta', Carbon::today()->endOfMonth()->toDateString());

        $porEstado = Appointment::select('status', DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha', [$desde, $hasta])
            ->groupBy('status')
            ->get();

        $porDoctora = Appointment::join('doctors', 'appointments.doctor_id', '=', 'doctors.doctor_id')
            ->select('doctors.nombre', DB::raw('COUNT(*) as total'))
            ->whereBetween('appointments.fecha', [$desde, $hasta])
            ->groupBy('doctors.nombre')
            ->get();

        $porMes = Appointment::select(
                DB::raw("DATE_FORMAT(fecha, '%Y-%m') as mes"),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('fecha', [$desde, $hasta])
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $totalCitas = Appointment::whereBetween('fecha', [$desde, $hasta])->count();
        $noAsistio  = Appointment::whereBetween('fecha', [$desde, $hasta])
            ->where('status', 'No-show')->count();

        $tasaInasistencia = $totalCitas > 0
            ? round(($noAsistio / $totalCitas) * 100, 1)
            : 0;

        return compact(
            'desde', 'hasta', 'porEstado', 'porDoctora',
            'porMes', 'totalCitas', 'noAsistio', 'tasaInasistencia'
        );
    }
}