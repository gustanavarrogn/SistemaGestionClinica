<x-guest-layout>
    <h2>Confirmar contraseña</h2><p class="mc-auth-description">Por seguridad, escribe tu contraseña para continuar.</p>
    <form method="POST" action="{{ route('password.confirm') }}">@csrf
        <div class="mb-4"><label class="form-label" for="password">Contraseña</label><input id="password" name="password" class="form-control @error('password') is-invalid @enderror" type="password" required autocomplete="current-password">@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <button class="btn btn-primary w-100" type="submit">Confirmar</button>
    </form>
</x-guest-layout>
