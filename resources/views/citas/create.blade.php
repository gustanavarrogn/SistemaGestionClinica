@extends('layouts.medicontrol')
@section('title', 'Nueva cita')
@section('content')
<div class="mb-4"><h1 class="mc-page-title">{{ auth()->user()->rol === 'paciente' ? 'Solicitar cita' : 'Agendar cita' }}</h1><p class="mc-muted">Selecciona la doctora, la fecha y un horario disponible.</p></div>
<div class="mc-card"><div class="mc-card-body">
    {{-- Los datos van a AppointmentController::store. Allí se revisa que el horario esté libre. --}}
    <form method="POST" action="{{ route('citas.store') }}" data-validar novalidate>
        @csrf
        <div class="row g-3">
            @if(auth()->user()->rol !== 'paciente')
                <div class="col-md-6"><label for="paciente_id" class="form-label">Paciente *</label>
                    <select name="paciente_id" id="paciente_id" class="form-select @error('paciente_id') is-invalid @enderror" required>
                        <option value="">Selecciona un paciente</option>
                        @foreach($pacientes as $paciente)
                            <option value="{{ $paciente->paciente_id }}" @selected((string) old('paciente_id') === (string) $paciente->paciente_id)>{{ $paciente->nombre }}</option>
                        @endforeach
                    </select><div class="invalid-feedback">Selecciona el paciente.</div></div>
            @endif
            <div class="col-md-6"><label for="doctor_id" class="form-label">Doctora *</label>
                <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                    <option value="">Selecciona una doctora</option>
                    @foreach($doctores as $doctor)
                        <option value="{{ $doctor->doctor_id }}" @selected((string) old('doctor_id') === (string) $doctor->doctor_id)>{{ $doctor->nombre }}{{ $doctor->especialidad ? ' — '.$doctor->especialidad : '' }}</option>
                    @endforeach
                </select><div class="invalid-feedback">Selecciona una doctora.</div></div>
            <div class="col-md-6"><label for="fecha" class="form-label">Fecha *</label><input id="fecha" name="fecha" type="date" min="{{ now()->toDateString() }}" value="{{ old('fecha') }}" required class="form-control @error('fecha') is-invalid @enderror"><div class="invalid-feedback">Selecciona una fecha válida.</div></div>
            <div class="col-md-6"><label for="hora" class="form-label">Hora *</label><input id="hora" name="hora" type="time" min="08:00" max="16:30" step="1800" value="{{ old('hora') }}" required class="form-control @error('hora') is-invalid @enderror"><div class="invalid-feedback">Selecciona un horario entre 8:00 y 16:30.</div></div>
            <div class="col-12"><div id="horarios-sugeridos" data-url="{{ route('citas.disponibilidad') }}" class="small" aria-live="polite"></div></div>
            <div class="col-12"><label for="razon" class="form-label">Motivo de la consulta *</label><textarea name="razon" id="razon" maxlength="500" rows="3" class="form-control @error('razon') is-invalid @enderror" required>{{ old('razon') }}</textarea><div class="invalid-feedback">Escribe el motivo de la consulta.</div></div>
        </div>
        <div class="alert alert-info mt-4 mb-0 small">Horario de atención: lunes a sábado de 8:00 a. m. a 5:00 p. m., en espacios de 30 minutos. La disponibilidad se vuelve a validar al guardar.</div>
        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="{{ route('citas.index') }}">Cancelar</a><button class="btn btn-primary" type="submit">Guardar cita</button></div>
    </form>
</div></div>
@endsection
