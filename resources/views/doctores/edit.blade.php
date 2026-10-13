@extends('layouts.medicontrol')
@section('title','Editar doctora')
@section('content')
<div class="mc-page-head"><div><span class="mc-eyebrow">Equipo médico / Editar</span><h1 class="mc-page-title">Editar doctora</h1><p class="mc-muted mb-0">Actualiza los datos de {{ $doctor->nombre }}.</p></div><a class="btn btn-outline-secondary" href="{{ route('doctores.index') }}">Volver</a></div>
<div class="mc-card"><div class="mc-card-body"><form method="POST" action="{{ route('doctores.update', $doctor->doctor_id) }}" data-validar>@csrf @method('PUT') @include('partials.doctor-form')<div class="mc-form-actions"><a class="btn btn-outline-secondary" href="{{ route('doctores.index') }}">Cancelar</a><button type="submit" class="btn btn-primary">Guardar cambios</button></div></form></div></div>
@endsection
