<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MediControl') | MediControl</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/medicontrol.css') }}" rel="stylesheet">
</head>
<body>
    {{-- Esta plantilla mantiene el mismo encabezado en todas las pantallas. --}}
    <header class="mc-header">
        <div class="container d-flex justify-content-between align-items-center gap-3 py-3">
            <a class="mc-brand" href="{{ auth()->check() ? route('dashboard') : route('login') }}">
                <span class="mc-logo" aria-hidden="true">+</span> Medi<span>Control</span>
            </a>
            @auth
                <span class="d-none d-md-inline mc-header-user"><span class="mc-online-dot" aria-hidden="true"></span>{{ auth()->user()->name }} · {{ auth()->user()->rol === 'admin' ? 'Administración' : (auth()->user()->rol === 'paciente' ? 'Paciente' : 'Recepción') }}</span>
            @else
                <span class="mc-header-user">Gestión de citas médicas</span>
            @endauth
        </div>
    </header>

    @auth
        {{-- El menú muestra solamente las opciones que corresponden al rol actual. --}}
        <nav class="navbar navbar-expand-lg mc-navbar" aria-label="Menú principal">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menú">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="menuPrincipal">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Inicio</a></li>
                        @if(in_array(auth()->user()->rol, ['admin', 'recepcionista'], true))
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('pacientes.*') ? 'active' : '' }}" href="{{ route('pacientes.index') }}">Pacientes</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('citas.*') ? 'active' : '' }}" href="{{ route('citas.index') }}">Citas</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('recepcionista.agenda') ? 'active' : '' }}" href="{{ route('recepcionista.agenda') }}">Agenda del día</a></li>
                            @if(auth()->user()->isManagementAdmin())
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('doctores.*') ? 'active' : '' }}" href="{{ route('doctores.index') }}">Doctores</a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">Usuarios y roles</a></li>
                            @endif
                        @else
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('paciente.mis-citas') ? 'active' : '' }}" href="{{ route('paciente.mis-citas') }}">Mis citas</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('citas.create') ? 'active' : '' }}" href="{{ route('citas.create') }}">Solicitar cita</a></li>
                        @endif
                    </ul>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm" type="submit">Cerrar sesión</button>
                    </form>
                </div>
            </div>
        </nav>
    @endauth

    <main class="container mc-main" id="contenido">
        {{-- Estos mensajes vienen desde los controladores al guardar o validar un formulario. --}}
        @if(session('exito'))
            <div class="alert alert-success alert-dismissible fade show" role="status">
                {{ session('exito') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif
        @if(session('status'))
            <div class="alert alert-info" role="status">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Hay datos que debemos revisar:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="mc-footer py-4 text-center">MediControl · Sistema de gestión de citas médicas <span class="mx-2">·</span> Atención clara, organizada y segura</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/medicontrol.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
