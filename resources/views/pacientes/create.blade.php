@extends('layouts.medicontrol')
@section('title', 'Registrar paciente')
@section('content')
<div class="mc-page-head"><div><span class="mc-eyebrow">Pacientes / Nuevo</span><h1 class="mc-page-title">Registrar paciente</h1><p class="mc-muted mb-0">Completa los datos para crear la ficha.</p></div><a class="btn btn-outline-secondary" href="{{ route('pacientes.index') }}">Volver</a></div>
<div class="mc-card"><div class="mc-card-body">
    <form method="POST" action="{{ route('pacientes.store') }}" data-validar>
        @csrf
        @include('partials.paciente-form')
        <div class="mc-form-actions"><a class="btn btn-outline-secondary" href="{{ route('pacientes.index') }}">Cancelar</a><button type="submit" class="btn btn-primary">Guardar paciente</button></div>
    </form>
</div></div>
@endsection
