@extends('layouts.medicontrol')
@section('title', 'Doctores')
@section('content')
<div class="mc-page-head"><div><span class="mc-eyebrow">Administración / Equipo médico</span><h1 class="mc-page-title">Doctores</h1><p class="mc-muted mb-0">Administra el personal médico disponible para citas.</p></div><a class="btn btn-primary" href="{{ route('doctores.create') }}">+ Registrar doctora</a></div>
<div class="mc-card"><div class="mc-card-head"><h2 class="mc-small-title mb-0">Equipo médico · {{ $doctores->total() }} registrados</h2></div><div class="mc-card-body"><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Nombre</th><th>Especialidad</th><th>Correo de contacto</th><th class="text-end">Acciones</th></tr></thead><tbody>
@forelse($doctores as $doctor)
    <tr><td><div class="mc-person"><span class="mc-avatar mc-avatar-green">{{ mb_strtoupper(mb_substr($doctor->nombre,0,1)) }}</span><div><strong>{{ $doctor->nombre }}</strong><small>Personal médico</small></div></div></td><td>{{ $doctor->especialidad ?: 'General' }}</td><td>{{ $doctor->email ?: 'No registrado' }}</td>
    <td class="text-end"><div class="d-inline-flex flex-wrap gap-2 justify-content-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('doctores.edit', $doctor->doctor_id) }}">Editar</a><form method="POST" action="{{ route('doctores.destroy', $doctor->doctor_id) }}" onsubmit="return confirm('¿Eliminar esta doctora? Solo es posible si no tiene citas.');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button></form></div></td></tr>
@empty
    <tr><td colspan="4"><div class="mc-empty"><strong>Aún no hay doctoras registradas</strong><p>Agrega la primera para habilitar el agendamiento.</p><a class="btn btn-sm btn-primary" href="{{ route('doctores.create') }}">Registrar doctora</a></div></td></tr>
@endforelse
</tbody></table></div><div class="mt-3">{{ $doctores->links('pagination::bootstrap-5') }}</div></div></div>
@endsection
