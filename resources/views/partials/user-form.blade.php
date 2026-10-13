{{-- Los roles disponibles corresponden a los tres roles de la Fase 2. --}}
<div class="row g-3">
    <div class="col-md-6"><label for="name" class="form-label">Nombre completo *</label><input id="name" name="name" type="text" maxlength="120" required class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $usuario->name ?? '') }}">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    @if(!isset($usuario))
        <div class="col-md-6"><label for="email" class="form-label">Correo *</label><input id="email" name="email" type="email" maxlength="255" required class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    @else
        <div class="col-md-6"><label class="form-label">Correo electrónico</label><div class="form-control bg-light">{{ $usuario->email }}</div></div>
    @endif
    <div class="col-md-6"><label for="rol" class="form-label">Rol *</label><select name="rol" id="rol" required class="form-select @error('rol') is-invalid @enderror">
        @foreach(['admin' => 'Administrador', 'recepcionista' => 'Recepcionista', 'paciente' => 'Paciente'] as $valor => $nombre)
            <option value="{{ $valor }}" @selected(old('rol', $usuario->rol ?? 'paciente') === $valor)>{{ $nombre }}</option>
        @endforeach
    </select>@error('rol')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    @if(isset($usuario))
        <div class="col-md-6"><label for="is_active" class="form-label">Acceso a la cuenta *</label><select name="is_active" id="is_active" class="form-select" required><option value="1" @selected(old('is_active', $usuario->is_active ? '1' : '0') == '1')>Activo</option><option value="0" @selected(old('is_active', $usuario->is_active ? '1' : '0') == '0')>Desactivado</option></select></div>
    @endif
    <div class="col-md-6"><label for="password" class="form-label">Contraseña {{ isset($usuario) ? '(opcional)' : '*' }}</label><div class="mc-password-wrap"><input id="password" name="password" type="password" minlength="8" autocomplete="new-password" class="form-control @error('password') is-invalid @enderror" {{ isset($usuario) ? '' : 'required' }}><button type="button" class="mc-password-toggle" data-password-toggle="password" aria-label="Mostrar contraseña">Ver</button></div>@error('password')<small class="text-danger">{{ $message }}</small>@enderror</div>
    <div class="col-md-6"><label for="password_confirmation" class="form-label">Confirmar contraseña</label><div class="mc-password-wrap"><input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" class="form-control" {{ isset($usuario) ? '' : 'required' }}><button type="button" class="mc-password-toggle" data-password-toggle="password_confirmation" aria-label="Mostrar contraseña">Ver</button></div></div>
</div>
