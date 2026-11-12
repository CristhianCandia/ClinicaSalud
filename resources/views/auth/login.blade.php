<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Clínica Vida Salud</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e5f8e 0%, #2980b9 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-card {
            background: white;
            padding: 40px 35px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .logo { font-size: 40px; color: #2980b9; margin-bottom: 10px; }
        .login-card h2 { color: #2c3e50; margin-bottom: 5px; }
        .login-card p { color: #7f8c8d; font-size: 14px; margin-bottom: 30px; }
        
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label {
            display: block;
            color: #34495e;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2980b9;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #2980b9;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-login:hover { background: #1e5f8e; }
        
        .error-msg {
            background: #fee;
            color: #c0392b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #f5c6cb;
        }
        
        .register-link {
            margin-top: 20px;
            font-size: 14px;
            color: #7f8c8d;
        }
        .register-link a {
            color: #2980b9;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo">✚</div>
        <h2>Clínica Vida Salud</h2>
        <p>Gestión de citas médicas</p>

        @if ($errors->any())
            <div class="error-msg">
                {{ $errors->first('identificador') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.process') }}">
            @csrf
            
            <div class="form-group">
                <label for="identificador">Usuario o correo electrónico</label>
                <input 
                    type="text" 
                    id="identificador" 
                    name="identificador" 
                    value="{{ old('identificador') }}"
                    placeholder="Ej: cristhian o cristhian@gmail.com"
                    required 
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input 
                    type="password" 
                    id="contrasena" 
                    name="contrasena" 
                    placeholder="••••••••"
                    required
                >
            </div>

            <button type="submit" class="btn-login">Iniciar sesión</button>
        </form>

        <div class="register-link">
            ¿Eres paciente? <a href="{{ route('registro') }}">Crea tu cuenta</a>
        </div>
    </div>

</body>
</html>