<x-guest-layout>
    <h2>Restablecer contraseña</h2><p class="mc-auth-description">Elige una nueva contraseña segura para acceder a tu cuenta.</p>
    <form method="POST" action="{{ route('password.store') }}">@csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="mb-3"><label for="email" class="form-label">Correo electrónico</label><input id="email" name="email" class="form-control @error('email') is-invalid @enderror" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="email">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="mb-3"><label for="password" class="form-label">Nueva contraseña</label><input id="password" name="password" class="form-control @error('password') is-invalid @enderror" type="password" required autocomplete="new-password">@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="mb-4"><label for="password_confirmation" class="form-label">Confirmar contraseña</label><input id="password_confirmation" name="password_confirmation" class="form-control" type="password" required autocomplete="new-password"></div>
        <button class="btn btn-primary w-100" type="submit">Guardar contraseña</button>
    </form>
</x-guest-layout>
