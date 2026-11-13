<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta · Clínica Vida Salud</title>
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body class="login-body">
    <main class="login-card" style="width: 720px; max-width: 95vw;">
        <h1 class="login-titulo">✚ Clínica Vida Salud</h1>
        <p class="login-sub">Crear cuenta de paciente</p>

        @if ($errors->any())
            <div class="alerta" role="alert">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('registro.guardar') }}" class="formulario">
            @csrf

            <div>
                <label for="ci">CI *</label>
                <input id="ci" name="ci" type="text" value="{{ old('ci') }}">
            </div>

            <div>
                <label for="nombres">Nombres *</label>
                <input id="nombres" name="nombres" type="text" value="{{ old('nombres') }}">
            </div>

            <div>
                <label for="apellidos">Apellidos *</label>
                <input id="apellidos" name="apellidos" type="text" value="{{ old('apellidos') }}">
            </div>

            <div>
                <label for="fecha_nacimiento">Fecha de nacimiento *</label>
                <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" value="{{ old('fecha_nacimiento') }}">
            </div>

            <div>
                <label for="sexo">Sexo *</label>
                <select id="sexo" name="sexo">
                    <option value="">Seleccione...</option>
                    <option value="M" {{ old('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ old('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                    <option value="O" {{ old('sexo') === 'O' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>

            <div>
                <label for="telefono">Teléfono *</label>
                <input id="telefono" name="telefono" type="text" value="{{ old('telefono') }}">
            </div>

            <div>
                <label for="correo">Correo electrónico *</label>
                <input id="correo" name="correo" type="email" value="{{ old('correo') }}">
            </div>

            <div>
                <label for="direccion">Dirección</label>
                <input id="direccion" name="direccion" type="text" value="{{ old('direccion') }}">
            </div>

            <div></div>

            <div>
                <label for="password">Contraseña *</label>
                <input id="password" name="password" type="password">
            </div>

            <div>
                <label for="password_confirmation">Confirmar contraseña *</label>
                <input id="password_confirmation" name="password_confirmation" type="password">
            </div>

            <p class="sub" style="grid-column: 1 / -1; margin: 8px 0 0;">
                Su usuario para iniciar sesión será su CI o su correo electrónico. El correo es obligatorio para crear la cuenta.
            </p>

            <div class="acciones-form" style="grid-column: 1 / -1;">
                <button type="submit" class="boton" style="width: auto; margin-top: 0;">Crear cuenta</button>
                <a class="boton gris" href="{{ route('login') }}">Volver</a>
            </div>
        </form>
    </main>
</body>
</html>