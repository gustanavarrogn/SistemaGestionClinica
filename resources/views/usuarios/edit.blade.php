@extends('layouts.medicontrol')
@section('title', 'Editar permisos')
@section('content')
<div class="mc-page-head"><div><span class="mc-eyebrow">Usuarios / Permisos</span><h1 class="mc-page-title">Editar cuenta</h1><p class="mc-muted">{{ $usuario->name }} · Los cambios de acceso se validan en el servidor.</p></div><a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Volver</a></div>
<div class="mc-card"><div class="mc-card-body"><form method="POST" action="{{ route('usuarios.update', $usuario) }}" data-validar>@csrf @method('PUT') @include('partials.user-form')<div class="mc-form-actions"><a class="btn btn-outline-secondary" href="{{ route('usuarios.index') }}">Cancelar</a><button class="btn btn-primary" type="submit">Guardar cambios</button></div></form></div></div>
@endsection
