<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administración') · Clínica Vida Salud</title>
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body>
    <header class="barra">
        <span class="marca">✚ Clínica Vida Salud</span>
        <span>{{ auth()->user()->nombre_usuario }} <span class="rol">{{ auth()->user()->rol->nombre }}</span></span>
    </header>

    <div class="contenedor">
        <nav class="menu">
            <ul>
                <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'activo' : '' }}">Inicio</a></li>

                <li class="seccion">Seguridad</li>
                <li><a href="{{ route('admin.usuarios.index') }}" class="{{ request()->routeIs('admin.usuarios.*') ? 'activo' : '' }}">Usuarios</a></li>

                <li class="seccion">Gestión médica</li>
                <li><a href="{{ route('admin.especialidades.index') }}" class="{{ request()->routeIs('admin.especialidades.*') ? 'activo' : '' }}">Especialidades</a></li>
                <li><a href="{{ route('admin.medicos.index') }}" class="{{ request()->routeIs('admin.medicos.*') ? 'activo' : '' }}">Médicos</a></li>
                <li><a href="{{ route('admin.horarios.index') }}" class="{{ request()->routeIs('admin.horarios.*') ? 'activo' : '' }}">Horarios de atención</a></li>

                <li class="seccion">Pacientes y citas</li>
                <li><span class="deshabilitado">Pacientes</span></li>
                <li><span class="deshabilitado">Disponibilidad</span></li>
                <li><span class="deshabilitado">Citas</span></li>
                <li><span class="deshabilitado">Agenda médica</span></li>

                <li class="seccion">Reportes</li>
                <li><span class="deshabilitado">Reportes</span></li>

                <li class="seccion">Sesión</li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-salir">Cerrar sesión</button>
                    </form>
                </li>
            </ul>
        </nav>

        <main class="contenido">
            @if(session('exito'))
                <div class="exito">{{ session('exito') }}</div>
            @endif
            @if(session('error'))
                <div class="alerta">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>