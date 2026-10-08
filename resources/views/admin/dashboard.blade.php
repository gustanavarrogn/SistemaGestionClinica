<!DOCTYPE html>
<html>
<head>
    <title>Panel de Administrador</title>
</head>
<body>
    <h1>Panel de Administrador</h1>
    <p>Bienvenido, {{ auth()->user()->name }}</p>
    <p>Rol: {{ auth()->user()->rol }}</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Cerrar sesión</button>
    </form>
</body>
</html>