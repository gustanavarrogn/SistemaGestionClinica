<x-guest-layout>
    <h2>Verificar correo</h2><p class="mc-auth-description">Para continuar, confirma tu dirección de correo desde el enlace enviado a tu bandeja de entrada.</p>
    @if(session('status') === 'verification-link-sent')
        <div class="alert alert-success">Se envió un nuevo enlace de verificación.</div>
    @endif
    <div class="d-grid gap-2">
        <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn btn-primary w-100" type="submit">Reenviar enlace</button></form>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-secondary w-100" type="submit">Cerrar sesión</button></form>
    </div>
</x-guest-layout>
