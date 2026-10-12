@extends('layouts.medicontrol')
@section('title', 'Crear cuenta')
@section('content')
<div class="mc-page-head"><div><span class="mc-eyebrow">Usuarios / Nueva cuenta</span><h1 class="mc-page-title">Crear usuario</h1><p class="mc-muted">Crea accesos para administración, recepción o pacientes.</p></div><a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Volver</a></div>
<div class="mc-card"><div class="mc-card-body"><form method="POST" action="{{ route('usuarios.store') }}" data-validar>@csrf @include('partials.user-form')<div class="mc-form-actions"><a class="btn btn-outline-secondary" href="{{ route('usuarios.index') }}">Cancelar</a><button class="btn btn-primary" type="submit">Crear cuenta</button></div></form></div></div>
@endsection
