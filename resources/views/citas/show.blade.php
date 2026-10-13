@extends('layouts.medicontrol')
@section('title', 'Detalle de cita')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4"><h1 class="mc-page-title mb-0">Detalle de la cita</h1><a class="btn btn-outline-secondary" href="{{ route('citas.index') }}">Volver al listado</a></div>
<div class="mc-card"><div class="mc-card-body">
    <dl class="row g-3 mb-0">
        <div class="col-md-6"><div class="mc-info-tile"><dt>Fecha</dt><dd>{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</dd></div></div>
        <div class="col-md-6"><div class="mc-info-tile"><dt>Hora</dt><dd>{{ substr($cita->hora, 0, 5) }}</dd></div></div>
        @if(auth()->user()->rol !== 'paciente')<div class="col-md-6"><div class="mc-info-tile"><dt>Paciente</dt><dd>{{ $cita->patient?->nombre ?? 'No disponible' }}</dd></div></div>@endif
        <div class="col-md-6"><div class="mc-info-tile"><dt>Doctora</dt><dd>{{ $cita->doctor?->nombre ?? 'No asignada' }}</dd></div></div>
        <div class="col-md-6"><div class="mc-info-tile"><dt>Motivo</dt><dd>{{ $cita->razon }}</dd></div></div>
        <div class="col-md-6"><div class="mc-info-tile"><dt>Estado</dt><dd>@include('partials.estado', ['estado' => $cita->status])</dd></div></div>
        {{-- Las observaciones se reservan para quien tiene funciones clínicas. --}}
        @if(auth()->user()->isManagementAdmin() && $cita->observaciones)
            <div class="col-12"><div class="mc-clinical-alert"><strong>Observaciones clínicas:</strong> {{ $cita->observaciones }}</div></div>
        @endif
    </dl>
    @if(!in_array($cita->status, ['Completada', 'Cancelada', 'No-show'], true))
        {{-- Cancelar siempre es una petición PUT protegida por CSRF. El plazo lo valida el servidor. --}}
        <form method="POST" action="{{ route('citas.cancelar', $cita->appointment_id) }}" class="mt-4" onsubmit="return confirm('¿Estás seguro de cancelar esta cita?');">
            @csrf
            @method('PUT')
            <button class="btn btn-outline-danger" type="submit">Cancelar cita</button>
        </form>
    @endif
</div></div>
@endsection
