<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte MediControl</title>
    <style>
        @page { margin: 30px 38px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #17304d; }
        h1 { font-size: 22px; margin: 0 0 4px; color: #174c83; }
        h2 { font-size: 13px; margin: 22px 0 7px; color: #174c83; }
        p { margin: 0 0 8px; }
        .sub { color: #5c7186; }
        .resumen { width: 100%; border-spacing: 8px; margin: 16px -8px; }
        .resumen td { background: #eef4fb; border: 1px solid #d5e3f4; padding: 12px; width: 33%; }
        .resumen strong { display: block; font-size: 17px; color: #174c83; margin-top: 6px; }
        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.datos th { text-align: left; background: #174c83; color: #fff; padding: 8px; }
        table.datos td { padding: 8px; border-bottom: 1px solid #dbe6f1; }
        .pie { margin-top: 25px; border-top: 1px solid #dbe6f1; padding-top: 8px; color: #7c91a5; }
    </style>
</head>
<body>
    <h1>MediControl</h1>
    <p class="sub">Reporte de citas médicas · {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</p>
    <table class="resumen"><tr>
        <td>Total de citas<strong>{{ $totalCitas }}</strong></td>
        <td>Sin asistencia<strong>{{ $noAsistio }}</strong></td>
        <td>Tasa de inasistencia<strong>{{ number_format($tasaInasistencia, 1, ',', '.') }} %</strong></td>
    </tr></table>
    <h2>Citas por estado</h2>
    <table class="datos"><thead><tr><th>Estado</th><th>Cantidad</th></tr></thead><tbody>
    @forelse($porEstado as $fila)
        <tr><td>{{ $fila->status }}</td><td>{{ $fila->total }}</td></tr>
    @empty
        <tr><td colspan="2">Sin registros en este periodo</td></tr>
    @endforelse
    </tbody></table>
    <h2>Citas por doctora</h2>
    <table class="datos"><thead><tr><th>Doctora</th><th>Cantidad</th></tr></thead><tbody>
    @forelse($porDoctora as $fila)
        <tr><td>{{ $fila->nombre }}</td><td>{{ $fila->total }}</td></tr>
    @empty
        <tr><td colspan="2">Sin registros en este periodo</td></tr>
    @endforelse
    </tbody></table>
    <h2>Citas por mes</h2>
    <table class="datos"><thead><tr><th>Mes</th><th>Cantidad</th></tr></thead><tbody>
    @forelse($porMes as $fila)
        <tr><td>{{ $fila->mes }}</td><td>{{ $fila->total }}</td></tr>
    @empty
        <tr><td colspan="2">Sin registros en este periodo</td></tr>
    @endforelse
    </tbody></table>
    <p class="pie">Reporte generado el {{ now()->format('d/m/Y H:i') }}. Datos del sistema MediControl.</p>
</body>
</html>
