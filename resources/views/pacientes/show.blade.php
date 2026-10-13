@extends('layouts.medicontrol')
@section('title', 'Ficha del paciente')
@section('content')
<div class="mc-page-head"><div><span class="mc-eyebrow">Pacientes / Ficha #{{ $paciente->paciente_id }}</span><h1 class="mc-page-title">{{ $paciente->nombre }}</h1><p class="mc-muted mb-0">Información de identificación y contacto.</p></div>
<div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-secondary" href="{{ route('pacientes.index') }}">Volver al listado</a><a class="btn btn-primary" href="{{ route('pacientes.edit', $paciente->paciente_id) }}">Editar ficha</a></div></div>
<div class="mc-card mb-4"><div class="mc-card-head"><h2 class="mc-small-title mb-0">Datos personales</h2></div><div class="mc-card-body"><dl class="row g-3 mb-0">
    <div class="col-md-6"><div class="mc-info-tile"><dt>Nombre completo</dt><dd>{{ $paciente->nombre }}</dd></div></div>
    <div class="col-md-6"><div class="mc-info-tile"><dt>Teléfono</dt><dd>{{ $paciente->telefono ?: 'No registrado' }}</dd></div></div>
    <div class="col-md-6"><div class="mc-info-tile"><dt>Correo electrónico</dt><dd>{{ $paciente->email ?: 'No registrado' }}</dd></div></div>
    <div class="col-md-6"><div class="mc-info-tile"><dt>Fecha de nacimiento</dt><dd>{{ $paciente->birth_date ? \Carbon\Carbon::parse($paciente->birth_date)->format('d/m/Y') : 'No registrada' }}</dd></div></div>
</dl></div></div>
@if($verClinico)
    {{-- La información clínica solo se muestra al administrador autorizado. --}}
    <div class="mc-card mb-4"><div class="mc-card-head"><h2 class="mc-small-title mb-0">Información clínica</h2></div><div class="mc-card-body"><div class="row g-3">
        <div class="col-md-3"><div class="mc-info-tile"><dt>Tipo de sangre</dt><dd>{{ $paciente->tipo_de_sangre ?: 'No registrado' }}</dd></div></div>
        <div class="col-md-9"><div class="mc-clinical-alert"><strong>Alergias</strong><p class="mb-0 mt-1">{{ $paciente->alergias ?: 'Sin información registrada' }}</p></div></div>
        <div class="col-12"><div class="mc-clinical-alert"><strong>Enfermedades crónicas</strong><p class="mb-0 mt-1">{{ $paciente->enfermedades_cronicas ?: 'Sin información registrada' }}</p></div></div>
    </div></div></div>
    <div class="mc-card"><div class="mc-card-head"><h2 class="mc-small-title mb-0">Historial de citas</h2></div><div class="mc-card-body"><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Fecha</th><th>Doctora</th><th>Motivo</th><th>Estado</th><th>Detalle</th></tr></thead><tbody>
    @forelse($paciente->appointments as $cita)
        <tr><td>{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</td><td>{{ $cita->doctor?->nombre ?? 'Sin asignar' }}</td><td>{{ $cita->razon }}</td><td>@include('partials.estado',['estado'=>$cita->status])</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('citas.show',$cita->appointment_id) }}">Ver</a></td></tr>
    @empty <tr><td class="mc-empty" colspan="5">No hay citas registradas para este paciente.</td></tr> @endforelse
    </tbody></table></div></div></div>
@else
    <div class="alert alert-info">Por privacidad, recepción solo puede ver los datos personales y de contacto.</div>
@endif
@endsection
