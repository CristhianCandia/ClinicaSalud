<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio · Clínica Vida Salud</title>
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body>
    <header class="barra">
        <span class="marca">✚ Clínica Vida Salud</span>
        <span>{{ $usuario->nombre_usuario }} <span class="rol">{{ $usuario->rol->nombre }}</span></span>
    </header>

    <div class="contenedor">
        <nav class="menu">
            <ul>
                @foreach ($menu as $opcion)
                    <li><a href="#">{{ $opcion }}</a></li>
                @endforeach
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-salir">Cerrar sesión</button>
                    </form>
                </li>
            </ul>
        </nav>

        <main class="contenido">
            <h2>Bienvenido, {{ $usuario->nombre_usuario }}</h2>
            <p>Opciones disponibles según su rol.</p>
        </main>
    </div>
</body>
</html>