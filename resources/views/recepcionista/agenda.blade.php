@extends('layouts.medicontrol')
@section('title', 'Agenda del día')

@section('content')
<div class="mc-page-head">
    <div>
        <h1 class="mc-page-title">Agenda del día</h1>
        <p class="mc-muted mb-0">Revisa las consultas programadas y actualiza su estado.</p>
    </div>
    <a href="{{ route('citas.create') }}" class="btn btn-primary">+ Nueva cita</a>
</div>

<div class="mc-card">
    <div class="mc-card-body">
        {{-- El formulario GET cambia el día sin modificar registros. --}}
        <form method="GET" action="{{ route('recepcionista.agenda') }}" class="row g-2 align-items-end mb-4">
            <div class="col-sm-5 col-md-4">
                <label for="fecha" class="form-label">Fecha de consulta</label>
                <input type="date" id="fecha" name="fecha" value="{{ $fecha }}" class="form-control" required>
            </div>
            <div class="col-sm-auto"><button class="btn btn-primary" type="submit">Ver agenda</button></div>
            <div class="col-sm-auto"><a class="btn btn-outline-secondary" href="{{ route('recepcionista.agenda') }}">Hoy</a></div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th scope="col">Hora</th><th scope="col">Paciente</th><th scope="col">Doctora</th><th scope="col">Motivo</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead>
                <tbody>
                    @forelse($citas as $cita)
                        <tr>
                            <td class="fw-semibold">{{ substr($cita->hora, 0, 5) }}</td>
                            <td>
                                <a href="{{ route('pacientes.show', $cita->paciente_id) }}">{{ $cita->patient?->nombre ?? 'Paciente no disponible' }}</a>
                            </td>
                            <td>{{ $cita->doctor?->nombre ?? 'No asignada' }}</td>
                            <td>{{ $cita->razon }}</td>
                            <td>@include('partials.estado', ['estado' => $cita->status])</td>
                            <td>
                                {{-- Solo ofrecemos cambios que el controlador acepta desde el estado actual. --}}
                                @php
                                    $transiciones = [
                                        'Pendiente' => ['Confirmado' => 'Confirmar', 'Cancelada' => 'Cancelar'],
                                        'Confirmado' => ['En proceso' => 'Iniciar atención', 'No-show' => 'No asistió', 'Cancelada' => 'Cancelar'],
                                        'En proceso' => ['Completada' => 'Completar atención'],
                                    ];
                                    $acciones = $transiciones[$cita->status] ?? [];
                                @endphp
                                @if($acciones)
                                    <form method="POST" action="{{ route('citas.estado', $cita->appointment_id) }}" class="d-flex gap-2 flex-wrap">
                                        @csrf
                                        @method('PUT')
                                        <label for="estado-{{ $cita->appointment_id }}" class="visually-hidden">Cambiar estado de {{ $cita->patient?->nombre }}</label>
                                        <select id="estado-{{ $cita->appointment_id }}" name="status" class="form-select form-select-sm" required style="max-width:175px">
                                            <option value="">Selecciona...</option>
                                            @foreach($acciones as $valor => $etiqueta)
                                                <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Guardar</button>
                                    </form>
                                @else
                                    <span class="mc-muted small">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="mc-empty">No hay citas registradas para esta fecha.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
