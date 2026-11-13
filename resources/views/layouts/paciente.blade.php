<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Paciente') · Clínica Vida Salud</title>
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body>
    <header class="barra">
        <span class="marca">✚ Clínica Vida Salud</span>
        <span>{{ auth()->user()->nombre_usuario }} <span class="rol">PACIENTE</span></span>
    </header>

    <div class="contenedor">
        <nav class="menu">
            <ul>
                <li><a href="{{ route('inicio') }}">Inicio</a></li>
                <li><span class="deshabilitado">Mis citas</span></li>
                <li>
                    <a href="{{ route('paciente.perfil') }}"
                       class="{{ request()->routeIs('paciente.perfil*') ? 'activo' : '' }}">Mi perfil</a>
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

            @yield('content')
        </main>
    </div>
</body>
</html>