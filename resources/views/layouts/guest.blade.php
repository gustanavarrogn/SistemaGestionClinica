<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Acceso | MediControl</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/medicontrol.css') }}" rel="stylesheet">
</head>
<body class="mc-auth-page">
    {{-- Este diseño es compartido por las pantallas de autenticación de Breeze. --}}
    <header class="mc-auth-top">
        <a href="{{ url('/') }}" class="mc-brand"><span class="mc-logo" aria-hidden="true">+</span> Medi<span>Control</span></a>
    </header>
    <main class="mc-auth-shell">
        <section class="mc-auth-story" aria-label="Presentación de MediControl">
            <span class="mc-kicker">Tu salud, mejor organizada</span>
            <h1>Tu atención médica, <em>más sencilla.</em></h1>
            <p>Gestiona tus citas y consulta su estado desde un solo lugar. Una experiencia clara, rápida y diseñada para ti.</p>
            <div class="mc-auth-benefits"><span>✓ Citas organizadas</span><span>✓ Información protegida</span><span>✓ Acceso sencillo</span></div>
        </section>
        <section class="mc-auth-card" aria-label="Formulario de acceso">
            {{ $slot }}
        </section>
    </main>
    <footer class="mc-footer text-center">MediControl · Sistema de gestión de citas médicas</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/medicontrol.js') }}" defer></script>
</body>
</html>
