@extends('layouts.medicontrol')
@section('title', 'Mis citas')

@section('content')
<div class="mc-page-head">
    <div>
        <h1 class="mc-page-title">Mis citas</h1>
        <p class="mc-muted mb-0">Consulta el estado de tus citas médicas.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('citas.create') }}">+ Solicitar cita</a>
</div>

@if(!$paciente)
    <div class="alert alert-warning" role="alert">
        Tu cuenta todavía no está vinculada a una ficha de paciente. Comunícate con recepción para poder solicitar citas.
    </div>
@endif

<div class="alert alert-info d-flex flex-wrap justify-content-between gap-2" role="status"><span>El estado se comprueba automáticamente cada 25 segundos.</span><span id="mc-estado-actualizado" class="small"></span></div>
<div id="mc-patient-status-feed" data-url="{{ route('paciente.estados') }}"></div>
<div class="mc-card">
    <div class="mc-card-head d-flex justify-content-between align-items-center flex-wrap gap-2"><h2 class="mc-small-title mb-0">Historial de citas</h2><span class="small mc-muted">Consultas de tu cuenta</span></div>
    <div class="mc-card-body pt-2">
        {{-- Las citas ya vienen filtradas por el usuario en AppointmentController::misCitas. --}}
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th scope="col">Fecha</th><th scope="col">Hora</th><th scope="col">Doctora</th><th scope="col">Motivo</th><th scope="col">Estado</th><th scope="col">Detalle</th></tr></thead>
                <tbody>
                    @forelse($citas as $cita)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</td>
                            <td>{{ substr($cita->hora, 0, 5) }}</td>
                            <td>{{ $cita->doctor?->nombre ?? 'Sin asignar' }}</td>
                            <td>{{ $cita->razon }}</td>
                            <td data-status-id="{{ $cita->appointment_id }}">@include('partials.estado', ['estado' => $cita->status])</td>
                            <td><a href="{{ route('citas.show', $cita->appointment_id) }}" class="btn btn-sm btn-outline-primary">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="mc-empty">Todavía no tienes citas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
