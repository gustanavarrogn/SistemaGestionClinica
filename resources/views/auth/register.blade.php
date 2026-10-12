<x-guest-layout>
    <h2>Crea tu cuenta</h2>
    <p class="mc-auth-description">Regístrate como paciente para gestionar tus citas médicas.</p>
    {{-- El registro público no permite escoger roles administrativos. --}}
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="name">Nombre y apellidos</label>
            <input class="form-control @error('name') is-invalid @enderror" type="text" id="name" name="name" value="{{ old('name') }}" maxlength="255" autocomplete="name" placeholder="Tu nombre completo" required autofocus>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="email">Correo electrónico</label>
            <input class="form-control @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="nombre@correo.com" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Contraseña</label>
            <div class="mc-password-wrap"><input class="form-control @error('password') is-invalid @enderror" type="password" id="password" name="password" minlength="8" autocomplete="new-password" placeholder="Mínimo 8 caracteres" required><button class="mc-password-toggle" type="button" data-password-toggle="password" aria-label="Mostrar contraseña">Ver</button></div>
            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
            <div class="mc-password-wrap"><input class="form-control @error('password_confirmation') is-invalid @enderror" type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" placeholder="Repite tu contraseña" required><button class="mc-password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Mostrar contraseña">Ver</button></div>
            @error('password_confirmation')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input @error('consentimiento') is-invalid @enderror" type="checkbox" value="1" id="consentimiento" name="consentimiento" required @checked(old('consentimiento'))>
            <label class="form-check-label small" for="consentimiento">Acepto el uso de mis datos para administrar mis citas médicas.</label>
            @error('consentimiento')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-primary w-100" type="submit">Crear cuenta <span aria-hidden="true">→</span></button>
    </form>
    <p class="mc-auth-foot mb-0">¿Ya tienes cuenta? <a href="{{ route('login') }}">Iniciar sesión</a></p>
</x-guest-layout>
