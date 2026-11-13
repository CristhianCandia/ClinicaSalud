<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Médico') · Clínica Vida Salud</title>
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body>
    <header class="barra">
        <span class="marca">✚ Clínica Vida Salud</span>
        <span>{{ auth()->user()->nombre_usuario }} <span class="rol">MEDICO</span></span>
    </header>

    <div class="contenedor">
        <nav class="menu">
            <ul>
                <li class="seccion">Agenda</li>
                <li><span class="deshabilitado">Mi agenda</span></li>

                <li class="seccion">Sesión</li>
                <li>
                    <a href="{{ route('cuenta.index') }}"
                       class="{{ request()->routeIs('cuenta*') ? 'activo' : '' }}">Mi cuenta</a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-salir">Cerrar sesión</button>
                    </form>
                </li>
            </ul>
        </nav>

        <main class="contenido">
            @if (session('exito'))
                <div class="exito" role="status">{{ session('exito') }}</div>
            @endif
            @if (session('error'))
                <div class="alerta">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>