@extends('layouts.medicontrol')
@section('title', 'Pacientes')
@section('content')
<div class="mc-page-head">
    <div><span class="mc-eyebrow">Administración / Pacientes</span><h1 class="mc-page-title">Directorio de pacientes</h1><p class="mc-muted mb-0">Busca fichas y gestiona la información necesaria para las citas.</p></div>
    <a class="btn btn-primary" href="{{ route('pacientes.create') }}">+ Nuevo paciente</a>
</div>
<div class="mc-card">
    <div class="mc-card-head d-flex flex-wrap justify-content-between gap-3 align-items-center"><div><h2 class="mc-small-title mb-1">Pacientes registrados</h2><p class="mc-muted small mb-0">{{ $pacientes->total() }} en total</p></div>
    <form class="mc-search" method="GET" action="{{ route('pacientes.index') }}" role="search">
        <label for="buscar" class="visually-hidden">Buscar por nombre, correo o teléfono</label>
        <input class="form-control" type="search" id="buscar" name="buscar" value="{{ $buscar }}" placeholder="Nombre, correo o teléfono">
        <button class="btn btn-outline-primary" type="submit">Buscar</button>
        @if($buscar)<a class="btn btn-link" href="{{ route('pacientes.index') }}">Limpiar</a>@endif
    </form></div>
    <div class="mc-card-body pt-2">
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Paciente</th><th>Correo</th><th>Teléfono</th><th class="text-end">Acciones</th></tr></thead><tbody>
        @forelse($pacientes as $paciente)
            <tr>
                <td><div class="mc-person"><span class="mc-avatar">{{ mb_strtoupper(mb_substr($paciente->nombre,0,1)) }}</span><div><strong>{{ $paciente->nombre }}</strong><small>Ficha #{{ $paciente->paciente_id }}</small></div></div></td>
                <td>{{ $paciente->email ?: 'No registrado' }}</td><td>{{ $paciente->telefono ?: 'No registrado' }}</td>
                <td class="text-end"><div class="d-inline-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('pacientes.show', $paciente->paciente_id) }}">Ver ficha</a><a class="btn btn-sm btn-outline-secondary" href="{{ route('pacientes.edit', $paciente->paciente_id) }}">Editar</a></div></td>
            </tr>
        @empty
            <tr><td colspan="4"><div class="mc-empty"><strong>No se encontraron pacientes</strong><p class="mb-2">Prueba otra búsqueda o agrega una ficha nueva.</p><a href="{{ route('pacientes.create') }}" class="btn btn-sm btn-primary">Nuevo paciente</a></div></td></tr>
        @endforelse
        </tbody></table></div>
        <div class="mt-3">{{ $pacientes->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
