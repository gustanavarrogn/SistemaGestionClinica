{{-- Formulario compartido para crear y actualizar pacientes. --}}
<div class="row g-4">
    <div class="col-md-6">
        <label class="form-label" for="nombre">Nombre completo <span class="text-danger">*</span></label>
        <input class="form-control @error('nombre') is-invalid @enderror" type="text" id="nombre" name="nombre" maxlength="120" required autofocus value="{{ old('nombre', $paciente->nombre ?? '') }}" placeholder="Ej. Sofía Martínez">
        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="telefono">Teléfono</label>
        <input class="form-control @error('telefono') is-invalid @enderror" type="tel" id="telefono" name="telefono" maxlength="30" value="{{ old('telefono', $paciente->telefono ?? '') }}" placeholder="7000-0000">
        @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">Correo electrónico</label>
        <input class="form-control @error('email') is-invalid @enderror" type="email" id="email" name="email" maxlength="255" value="{{ old('email', $paciente->email ?? '') }}" placeholder="nombre@correo.com">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="birth_date">Fecha de nacimiento</label>
        <input class="form-control @error('birth_date') is-invalid @enderror" type="date" id="birth_date" name="birth_date" max="{{ now()->subDay()->toDateString() }}" value="{{ old('birth_date', $paciente->birth_date ?? '') }}">
        @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @if(auth()->user()->rol === 'admin')
        {{-- Solo administración con función clínica puede registrar esta información. --}}
        <div class="col-12"><div class="mc-section-label">Información clínica básica</div></div>
        <div class="col-md-4">
            <label class="form-label" for="tipo_de_sangre">Tipo de sangre</label>
            <select name="tipo_de_sangre" id="tipo_de_sangre" class="form-select">
                <option value="">No registrado</option>
                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $grupo)
                    <option value="{{ $grupo }}" @selected(old('tipo_de_sangre', $paciente->tipo_de_sangre ?? '') === $grupo)>{{ $grupo }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="alergias">Alergias</label>
            <textarea class="form-control" rows="2" maxlength="2000" id="alergias" name="alergias" placeholder="Medicamentos o alimentos">{{ old('alergias', $paciente->alergias ?? '') }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="enfermedades_cronicas">Enfermedades crónicas</label>
            <textarea class="form-control" rows="2" maxlength="2000" id="enfermedades_cronicas" name="enfermedades_cronicas" placeholder="Antecedentes relevantes">{{ old('enfermedades_cronicas', $paciente->enfermedades_cronicas ?? '') }}</textarea>
        </div>
    @endif
</div>
