<x-guest-layout>
    <h2>Recuperar contraseña</h2>
    <p class="mc-auth-description">Escribe el correo de tu cuenta y recibirás un enlace de recuperación cuando el correo esté configurado.</p>
    <x-auth-session-status class="alert alert-info" :status="session('status')" />
    <form method="POST" action="{{ route('password.email') }}">@csrf
        <div class="mb-4"><label class="form-label" for="email">Correo electrónico</label><input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <button class="btn btn-primary w-100" type="submit">Enviar enlace</button>
    </form>
    <p class="mc-auth-foot"><a href="{{ route('login') }}">Volver al inicio de sesión</a></p>
</x-guest-layout>
