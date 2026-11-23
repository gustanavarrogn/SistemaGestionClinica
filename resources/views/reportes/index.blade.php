@extends('layouts.medicontrol')
@section('title', 'Reportes y estadísticas')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/medicontrol-fase3.css') }}">
@endpush

@section('content')
<div class="mc-page-head d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <span class="mc-eyebrow">Administración / Análisis</span>
        <h1 class="mc-page-title mb-1">Reportes y estadísticas</h1>
        <p class="mc-muted mb-0">Análisis de citas médicas según el rango de fechas seleccionado.</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Volver al panel</a>
</div>

<section class="mc-card mc-f3-card mb-4" aria-labelledby="filtros-titulo">
    <div class="mc-card-head"><h2 class="mc-small-title mb-0" id="filtros-titulo">Filtros del reporte</h2></div>
    <div class="mc-card-body">
        <form action="{{ route('reportes.index') }}" method="GET" class="row g-3 align-items-end" id="form-filtros-reportes">
            <div class="col-sm-6 col-lg-4">
                <label for="desde" class="form-label fw-semibold">Fecha inicial</label>
                <input type="date" name="desde" id="desde" class="form-control @error('desde') is-invalid @enderror" value="{{ old('desde', $desde) }}" required>
                @error('desde') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-sm-6 col-lg-4">
                <label for="hasta" class="form-label fw-semibold">Fecha final</label>
                <input type="date" name="hasta" id="hasta" class="form-control @error('hasta') is-invalid @enderror" value="{{ old('hasta', $hasta) }}" required>
                @error('hasta') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-lg-4 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Aplicar filtros</button>
                <a class="btn btn-outline-secondary" href="{{ route('reportes.index') }}">Restablecer</a>
            </div>
            <div class="col-12"><p class="small mc-muted mb-0">Periodo: <strong>{{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }}</strong> al <strong>{{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</strong>. La exportación conserva este filtro.</p></div>
        </form>
    </div>
</section>

<div class="d-flex flex-wrap justify-content-end gap-2 mb-4">
    <a href="{{ route('reportes.pdf', ['desde' => $desde, 'hasta' => $hasta]) }}" class="btn btn-danger">Exportar a PDF</a>
    <a href="{{ route('reportes.excel', ['desde' => $desde, 'hasta' => $hasta]) }}" class="btn btn-success">Exportar a Excel (CSV)</a>
</div>

<div class="row g-3 mb-4" aria-label="Indicadores del reporte">
    <div class="col-md-4"><div class="mc-f3-stat"><span class="mc-f3-stat-icon" aria-hidden="true">01</span><p>Total de citas</p><strong>{{ number_format($totalCitas, 0, ',', '.') }}</strong><small>Durante el periodo</small></div></div>
    <div class="col-md-4"><div class="mc-f3-stat"><span class="mc-f3-stat-icon mc-f3-stat-warn" aria-hidden="true">02</span><p>Sin asistencia</p><strong>{{ number_format($noAsistio, 0, ',', '.') }}</strong><small>Citas no atendidas</small></div></div>
    <div class="col-md-4"><div class="mc-f3-stat"><span class="mc-f3-stat-icon mc-f3-stat-good" aria-hidden="true">03</span><p>Tasa de inasistencia</p><strong>{{ number_format($tasaInasistencia, 1, ',', '.') }} %</strong><small>Sobre el total de citas</small></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <section class="mc-card mc-f3-card h-100" aria-labelledby="estados-titulo">
            <div class="mc-card-head"><h2 id="estados-titulo" class="mc-small-title mb-0">Citas por estado</h2></div>
            <div class="mc-card-body"><div class="mc-f3-chart"><canvas id="grafico-estados" role="img" aria-label="Gráfico circular de citas agrupadas por estado"></canvas></div></div>
        </section>
    </div>
    <div class="col-lg-6">
        <section class="mc-card mc-f3-card h-100" aria-labelledby="doctores-titulo">
            <div class="mc-card-head"><h2 id="doctores-titulo" class="mc-small-title mb-0">Citas por doctora</h2></div>
            <div class="mc-card-body"><div class="mc-f3-chart"><canvas id="grafico-doctoras" role="img" aria-label="Gráfico de barras de citas agrupadas por doctora"></canvas></div></div>
        </section>
    </div>
    <div class="col-12">
        <section class="mc-card mc-f3-card" aria-labelledby="meses-titulo">
            <div class="mc-card-head"><h2 id="meses-titulo" class="mc-small-title mb-0">Evolución mensual de citas</h2></div>
            <div class="mc-card-body"><div class="mc-f3-chart mc-f3-chart-wide"><canvas id="grafico-meses" role="img" aria-label="Gráfico de línea de citas registradas por mes"></canvas></div></div>
        </section>
    </div>
</div>

<section class="mc-card mc-f3-card mb-4" aria-labelledby="tablas-reporte-titulo">
    <div class="mc-card-head d-flex align-items-center justify-content-between flex-wrap gap-2"><h2 class="mc-small-title mb-0" id="tablas-reporte-titulo">Detalle de resultados</h2><span class="mc-f3-count">Datos reales del sistema</span></div>
    <div class="mc-card-body">
        <div class="row g-4">
            <div class="col-lg-4"><h3 class="h6 fw-semibold">Por estado</h3><div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>Estado</th><th class="text-end">Citas</th></tr></thead><tbody>@forelse($porEstado as $fila)<tr><td>{{ $fila->status }}</td><td class="text-end">{{ $fila->total }}</td></tr>@empty<tr><td colspan="2" class="mc-muted">Sin registros</td></tr>@endforelse</tbody></table></div></div>
            <div class="col-lg-4"><h3 class="h6 fw-semibold">Por doctora</h3><div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>Doctora</th><th class="text-end">Citas</th></tr></thead><tbody>@forelse($porDoctora as $fila)<tr><td>{{ $fila->nombre }}</td><td class="text-end">{{ $fila->total }}</td></tr>@empty<tr><td colspan="2" class="mc-muted">Sin registros</td></tr>@endforelse</tbody></table></div></div>
            <div class="col-lg-4"><h3 class="h6 fw-semibold">Por mes</h3><div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>Mes</th><th class="text-end">Citas</th></tr></thead><tbody>@forelse($porMes as $fila)<tr><td>{{ $fila->mes }}</td><td class="text-end">{{ $fila->total }}</td></tr>@empty<tr><td colspan="2" class="mc-muted">Sin registros</td></tr>@endforelse</tbody></table></div></div>
        </div>
    </div>
</section>

@if($totalCitas === 0)
<div class="alert alert-info" role="status">No hay citas dentro de este rango de fechas. Podés ampliar el periodo para visualizar estadísticas.</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<script>
(() => {
    'use strict';
    const estados = {
        etiquetas: @json($porEstado->pluck('status')->values()->all()),
        valores: @json($porEstado->pluck('total')->values()->all())
    };
    const doctoras = {
        etiquetas: @json($porDoctora->pluck('nombre')->values()->all()),
        valores: @json($porDoctora->pluck('total')->values()->all())
    };
    const meses = {
        etiquetas: @json($porMes->pluck('mes')->values()->all()),
        valores: @json($porMes->pluck('total')->values()->all())
    };
    if (typeof Chart === 'undefined') {
        document.querySelectorAll('.mc-f3-chart').forEach(container => {
            container.innerHTML = '<p class="mc-muted text-center p-4">No se pudo cargar el gráfico. Los datos están disponibles en las tablas de abajo.</p>';
        });
        return;
    }
    const crear = (id, tipo, titulo, grupo, colores) => {
        const lienzo = document.getElementById(id);
        if (!lienzo) return;
        if (!grupo.valores.length) {
            lienzo.parentElement.innerHTML = '<p class="mc-muted text-center p-4">Sin datos en este periodo.</p>';
            return;
        }
        new Chart(lienzo, {
            type: tipo,
            data: {
                labels: grupo.etiquetas,
                datasets: [{ label: titulo, data: grupo.valores, backgroundColor: colores, borderColor: tipo === 'line' ? '#2563eb' : '#ffffff', borderWidth: tipo === 'line' ? 3 : 1, tension: 0.28, fill: tipo === 'line' }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: tipo === 'doughnut', position: 'bottom' }, tooltip: { enabled: true } },
                scales: tipo === 'doughnut' ? {} : { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    };
    crear('grafico-estados', 'doughnut', 'Citas', estados, ['#2563eb', '#0d9488', '#f59e0b', '#e11d48', '#7c3aed', '#64748b']);
    crear('grafico-doctoras', 'bar', 'Citas', doctoras, '#2563eb');
    crear('grafico-meses', 'line', 'Citas', meses, 'rgba(37,99,235,.12)');
})();
</script>
@endpush
