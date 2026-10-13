@extends('layouts.medicontrol')
@section('title', 'Citas')
@section('content')
<div class="mc-page-head"><div><h1 class="mc-page-title">Citas médicas</h1><p class="mc-muted mb-0">Listado de citas registradas en MediControl.</p></div><a href="{{ route('citas.create') }}" class="btn btn-primary">+ Nueva cita</a></div>
<div class="mc-card"><div class="mc-card-body">
    {{-- El listado se limita a las citas propias cuando la sesión pertenece a un paciente. --}}
    <div class="table-responsive"><table class="table table-hover">
        <thead><tr><th scope="col">Fecha</th><th scope="col">Hora</th>@if(auth()->user()->rol !== 'paciente')<th scope="col">Paciente</th>@endif<th scope="col">Doctora</th><th scope="col">Motivo</th><th scope="col">Estado</th><th scope="col">Ver</th></tr></thead>
        <tbody>
            @forelse($citas as $cita)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</td>
                    <td>{{ substr($cita->hora, 0, 5) }}</td>
                    @if(auth()->user()->rol !== 'paciente')<td>{{ $cita->patient?->nombre ?? 'No disponible' }}</td>@endif
                    <td>{{ $cita->doctor?->nombre ?? 'No asignada' }}</td>
                    <td>{{ $cita->razon }}</td>
                    <td>@include('partials.estado', ['estado' => $cita->status])</td>
                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('citas.show', $cita->appointment_id) }}">Detalle</a></td>
                </tr>
            @empty
                <tr><td class="mc-empty" colspan="{{ auth()->user()->rol === 'paciente' ? 6 : 7 }}">No hay citas registradas.</td></tr>
            @endforelse
        </tbody>
    </table></div>
    @if(method_exists($citas, 'links'))
        {{ $citas->links('pagination::bootstrap-5') }}
    @endif
</div></div>
@endsection
