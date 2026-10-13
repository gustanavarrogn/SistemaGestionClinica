{{-- Los mismos campos sirven para crear y editar al personal médico. --}}
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="nombre">Nombre de la doctora *</label>
        <input class="form-control @error('nombre') is-invalid @enderror" name="nombre" id="nombre" maxlength="120" required value="{{ old('nombre', $doctor->nombre ?? '') }}" placeholder="Dra. Valeria Rivas">
        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="especialidad">Especialidad *</label>
        <input class="form-control @error('especialidad') is-invalid @enderror" name="especialidad" id="especialidad" maxlength="120" required value="{{ old('especialidad', $doctor->especialidad ?? '') }}" placeholder="Medicina general">
        @error('especialidad')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="email">Correo de contacto *</label>
        <input class="form-control @error('email') is-invalid @enderror" name="email" type="email" id="email" maxlength="255" required value="{{ old('email', $doctor->email ?? '') }}" placeholder="doctora@clinica.com" autocomplete="off">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<p class="mc-muted small mt-3 mb-0">En esta fase las doctoras se registran para asignarles citas; no tienen cuentas de acceso propias.</p>
