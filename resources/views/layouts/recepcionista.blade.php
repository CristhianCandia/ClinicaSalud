<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Recepción') · Clínica Vida Salud</title>
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
                <li><a href="{{ route('recepcionista.dashboard') }}" class="{{ request()->routeIs('recepcionista.dashboard') ? 'activo' : '' }}">Inicio</a></li>

                <li class="seccion">Pacientes</li>
                <li><a href="{{ route('recepcionista.pacientes.index') }}" class="{{ request()->routeIs('recepcionista.pacientes.*') ? 'activo' : '' }}">Pacientes</a></li>

                <li class="seccion">Citas</li>
                <li><a href="{{ route('disponibilidad.index') }}" class="{{ request()->routeIs('disponibilidad*') ? 'activo' : '' }}">Disponibilidad</a></li>
                <li><a href="{{ route('citas.create') }}" class="{{ request()->routeIs('citas.*') ? 'activo' : '' }}">Programar cita</a></li>
                <li><span class="deshabilitado">Citas registradas</span></li>
                <li><span class="deshabilitado">Agenda médica</span></li>
                <li><span class="deshabilitado">Historial de citas</span></li>

                <li class="seccion">Caja y pagos</li>
                <li><a href="{{ route('recepcionista.pagos.index') }}" class="{{ request()->routeIs('recepcionista.pagos.*') ? 'activo' : '' }}">Registrar pago / historial</a></li>

                <li class="seccion">Sesión</li>
                <li><a href="{{ route('cuenta.index') }}" class="{{ request()->routeIs('cuenta*') ? 'activo' : '' }}">Mi cuenta</a></li>
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