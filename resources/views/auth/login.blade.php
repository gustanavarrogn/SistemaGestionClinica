<x-guest-layout>
    <h2>Bienvenido de nuevo</h2>
    <p class="mc-auth-description">Ingresa tus datos para acceder a MediControl.</p>

    {{-- Breeze muestra mensajes cuando se recupera o cambia una contraseña. --}}
    <x-auth-session-status class="alert alert-info mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf
        {{-- Laravel valida nuevamente los datos al recibir el formulario. --}}
        <div class="mb-3">
            <label class="form-label" for="email">Correo electrónico</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nombre@correo.com" autocomplete="username" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Contraseña</label>
            <div class="mc-password-wrap"><input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" placeholder="Ingresa tu contraseña" autocomplete="current-password" required><button class="mc-password-toggle" type="button" data-password-toggle="password" aria-label="Mostrar contraseña" aria-pressed="false">Ver</button></div>
            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <label class="form-check-label small" for="remember_me"><input id="remember_me" name="remember" type="checkbox" class="form-check-input me-2">Recordarme</label>
            @if(Route::has('password.request'))
                <a class="small fw-bold" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
            @endif
        </div>
        <button type="submit" class="btn btn-primary w-100">Iniciar sesión <span aria-hidden="true">→</span></button>
    </form>
    @if(Route::has('register'))
        <p class="mc-auth-foot mb-0">¿Todavía no tienes cuenta? <a href="{{ route('register') }}">Crear una cuenta</a></p>
    @endif
</x-guest-layout>
