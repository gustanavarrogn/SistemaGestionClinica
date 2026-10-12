@extends('layouts.medicontrol')
@section('title', 'Editar paciente')
@section('content')
<div class="mc-page-head"><div><span class="mc-eyebrow">Pacientes / Editar ficha</span><h1 class="mc-page-title">Editar a {{ $paciente->nombre }}</h1><p class="mc-muted mb-0">Actualiza los datos registrados de este paciente.</p></div><a class="btn btn-outline-secondary" href="{{ route('pacientes.show', $paciente->paciente_id) }}">Ver ficha</a></div>
<div class="mc-card"><div class="mc-card-body">
    <form method="POST" action="{{ route('pacientes.update', $paciente->paciente_id) }}" data-validar>
        @csrf @method('PUT')
        @include('partials.paciente-form')
        <div class="mc-form-actions"><a class="btn btn-outline-secondary" href="{{ route('pacientes.index') }}">Cancelar</a><button class="btn btn-primary" type="submit">Guardar cambios</button></div>
    </form>
</div></div>
@endsection
