<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · Clínica Vida Salud</title>
    <link rel="stylesheet" href="{{ asset('css/estilos.css') }}">
</head>
<body class="login-body">
    <main class="login-card">
        <h1 class="login-titulo">✚ Clínica Vida Salud</h1>
        <p class="login-sub" style="margin-top: 16px;">¿Eres paciente? <a href="{{ route('registro') }}">Crea tu cuenta</a></p>

        @if ($errors->has('general'))
            @if (session('exito'))
                <div class="exito" role="status">{{ session('exito') }}</div>
            @endif

            @if (session('error'))
                <div class="alerta" role="alert">{{ session('error') }}</div>
            @endif
        @endif

        <form method="POST" action="{{ route('login.enviar') }}" id="form-login" novalidate>
            @csrf
            <label for="login">Usuario o correo electrónico</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}"
                   autocomplete="username" autofocus>

            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" autocomplete="current-password">

            <button type="submit">Iniciar sesión</button>
        </form>
    </main>

    <script>
        // Flujo 3: validación de campos vacíos en el cliente (el servidor también valida)
        document.getElementById('form-login').addEventListener('submit', function (e) {
            var login = document.getElementById('login').value.trim();
            var pass = document.getElementById('password').value;
            var alerta = document.querySelector('.alerta');
            if (login === '' || pass === '') {
                e.preventDefault();
                if (!alerta) {
                    alerta = document.createElement('div');
                    alerta.className = 'alerta';
                    alerta.setAttribute('role', 'alert');
                    this.parentNode.insertBefore(alerta, this);
                }
                alerta.textContent = 'Complete todos los campos.';
            }
        });
    </script>
</body>
</html>