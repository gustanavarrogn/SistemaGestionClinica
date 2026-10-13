@extends('layouts.medicontrol')
@section('title','Registrar doctora')
@section('content')
<div class="mc-page-head"><div><span class="mc-eyebrow">Equipo médico / Nueva</span><h1 class="mc-page-title">Registrar doctora</h1><p class="mc-muted mb-0">Agrega a una profesional para asignarle citas.</p></div><a class="btn btn-outline-secondary" href="{{ route('doctores.index') }}">Volver</a></div>
<div class="mc-card"><div class="mc-card-body"><form method="POST" action="{{ route('doctores.store') }}" data-validar>@csrf @include('partials.doctor-form')<div class="mc-form-actions"><a class="btn btn-outline-secondary" href="{{ route('doctores.index') }}">Cancelar</a><button type="submit" class="btn btn-primary">Guardar doctora</button></div></form></div></div>
@endsection
