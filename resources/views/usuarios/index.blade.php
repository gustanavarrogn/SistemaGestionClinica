@extends('layouts.medicontrol')
@section('title', 'Usuarios y permisos')
@section('content')
<div class="mc-page-head"><div><span class="mc-eyebrow">Administración / Seguridad</span><h1 class="mc-page-title">Usuarios y permisos</h1><p class="mc-muted">Administra las cuentas de pacientes, recepción y administradores. Las doctoras se registran como personal médico para las citas, sin un rol de acceso adicional.</p></div><a href="{{ route('usuarios.create') }}" class="btn btn-primary">+ Crear cuenta</a></div>
<div class="mc-card"><div class="mc-card-head"><h2 class="mc-small-title mb-0">Cuentas del sistema · {{ $usuarios->total() }}</h2></div><div class="mc-card-body"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Usuario</th><th>Correo</th><th>Rol</th><th>Estado</th><th class="text-end">Acción</th></tr></thead><tbody>
@forelse($usuarios as $usuario)
<tr><td class="fw-semibold">{{ $usuario->name }} @if($usuario->id === auth()->id())<span class="mc-muted small">(tú)</span>@endif</td><td>{{ $usuario->email }}</td><td><span class="mc-pill mc-status-pendiente">{{ ucfirst($usuario->rol) }}</span></td><td><span class="mc-pill {{ $usuario->is_active ? 'mc-status-confirmado' : 'mc-status-cancelada' }}">{{ $usuario->is_active ? 'Activo' : 'Desactivado' }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('usuarios.edit', $usuario) }}">Editar permisos</a></td></tr>
@empty<tr><td colspan="5" class="mc-empty">No hay cuentas registradas.</td></tr>@endforelse
</tbody></table></div>{{ $usuarios->links('pagination::bootstrap-5') }}</div></div>
@endsection
