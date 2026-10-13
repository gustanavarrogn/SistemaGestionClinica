@extends('layouts.medicontrol')
@section('title','Panel del administrador')
@section('content')
<div class="mc-hero"><div><span class="mc-eyebrow">Panel de administración</span><h1>Bienvenido a MediControl</h1><p>Hola, {{ auth()->user()->name }}. Tenés un resumen actualizado de la clínica.</p><div class="d-flex gap-2 flex-wrap"><a href="{{ route('citas.create') }}" class="btn btn-light">+ Nueva cita</a><a href="{{ route('recepcionista.agenda') }}" class="btn btn-outline-light">Ver agenda del día</a></div></div><div class="mc-hero-mark" aria-hidden="true">+</div></div>
<div class="row g-3 g-md-4 mb-4">
    <div class="col-sm-6 col-lg-4"><div class="mc-stat"><div class="mc-stat-icon mc-stat-blue">P</div><span>Pacientes</span><strong>{{ $totalPacientes }}</strong><a href="{{ route('pacientes.index') }}">Ver pacientes →</a></div></div>
    <div class="col-sm-6 col-lg-4"><div class="mc-stat"><div class="mc-stat-icon mc-stat-green">D</div><span>Profesionales</span><strong>{{ $totalDoctores }}</strong><a href="{{ route('doctores.index') }}">Ver doctores →</a></div></div>
    <div class="col-sm-6 col-lg-4"><div class="mc-stat"><div class="mc-stat-icon mc-stat-amber">C</div><span>Citas de hoy</span><strong>{{ $citasHoy }}</strong><a href="{{ route('citas.index') }}">Ver citas →</a></div></div>
</div>
<div class="row g-4">
    <div class="col-lg-8"><div class="mc-card h-100"><div class="mc-card-head d-flex justify-content-between align-items-center flex-wrap gap-2"><h2 class="mc-small-title mb-0">Próximas citas</h2><a class="btn btn-sm btn-outline-primary" href="{{ route('citas.index') }}">Todas las citas</a></div><div class="mc-card-body pt-2"><div class="table-responsive"><table class="table"><thead><tr><th>Fecha</th><th>Paciente</th><th>Doctora</th><th>Estado</th></tr></thead><tbody>
    @forelse($proximas as $cita)
        <tr><td><strong>{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</strong><div class="small mc-muted">{{ substr($cita->hora,0,5) }}</div></td><td>{{ $cita->patient?->nombre ?? 'Sin registro' }}</td><td>{{ $cita->doctor?->nombre ?? 'No asignada' }}</td><td>@include('partials.estado',['estado'=>$cita->status])</td></tr>
    @empty <tr><td colspan="4" class="mc-empty">No hay próximas citas registradas.</td></tr> @endforelse
</tbody></table></div></div></div></div>
    <div class="col-lg-4"><div class="mc-card h-100"><div class="mc-card-head"><h2 class="mc-small-title mb-0">Accesos rápidos</h2></div><div class="mc-card-body mc-shortcuts"><a href="{{ route('pacientes.create') }}"><span>+</span><div><strong>Registrar paciente</strong><small>Crear una ficha nueva</small></div>→</a><a href="{{ route('doctores.create') }}"><span>+</span><div><strong>Registrar doctora</strong><small>Ampliar el equipo médico</small></div>→</a><a href="{{ route('usuarios.index') }}"><span>+</span><div><strong>Usuarios y roles</strong><small>Gestionar accesos</small></div>→</a><a href="{{ route('citas.create') }}"><span>+</span><div><strong>Agendar cita</strong><small>Elegir paciente y horario</small></div>→</a></div></div></div>
</div>
@endsection
