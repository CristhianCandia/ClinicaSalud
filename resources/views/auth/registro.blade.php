<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Crear cuenta de paciente - Clínica Vida Salud</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body {
            background: linear-gradient(135deg, #1e5f8e 0%, #2980b9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px 35px;
            width: 100%;
            max-width: 700px;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header .logo { font-size: 28px; color: #2980b9; margin-bottom: 5px; }
        .register-header h1 { color: #2c3e50; font-size: 22px; margin-bottom: 5px; }
        .register-header p { color: #7f8c8d; font-size: 14px; }
        
        .form-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: block; color: #34495e; font-size: 13px; font-weight: 600; margin-bottom: 6px;
        }
        .form-group label .required { color: #e74c3c; }
        .form-group input, .form-group select {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd;
            border-radius: 5px; font-size: 14px; transition: border 0.3s;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none; border-color: #2980b9;
        }
        
        .btn {
            padding: 10px 20px; border: none; border-radius: 5px;
            cursor: pointer; font-size: 14px; font-weight: 600;
            transition: all 0.3s; text-decoration: none; display: inline-block;
        }
        .btn-primary { background: #2980b9; color: white; }
        .btn-primary:hover { background: #1e5f8e; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn-secondary:hover { background: #7f8c8d; }
        
        .alert {
            padding: 12px 15px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .info-text { color: #7f8c8d; font-size: 12px; margin-top: 15px; }
        .button-group { display: flex; gap: 10px; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="register-card">
        <div class="register-header">
            <div class="logo">✚</div>
            <h1>Clínica Vida Salud</h1>
            <p>Crear cuenta de paciente</p>
        </div>

        @if(session('exito'))
            <div class="alert alert-success">{{ session('exito') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('registro.store') }}">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label>CI <span class="required">*</span></label>
                    <input type="text" name="ci" value="{{ old('ci') }}" required>
                </div>

                <div class="form-group">
                    <label>Nombres <span class="required">*</span></label>
                    <input type="text" name="nombres" value="{{ old('nombres') }}" required>
                </div>

                <div class="form-group">
                    <label>Apellidos <span class="required">*</span></label>
                    <input type="text" name="apellidos" value="{{ old('apellidos') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Fecha de nacimiento <span class="required">*</span></label>
                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required>
                </div>

                <div class="form-group">
                    <label>Sexo <span class="required">*</span></label>
                    <select name="sexo" required>
                        <option value="">Seleccione...</option>
                        <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Femenino</option>
                        <option value="O" {{ old('sexo') == 'O' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Teléfono <span class="required">*</span></label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" name="correo" value="{{ old('correo') }}">
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label>Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Contraseña <span class="required">*</span></label>
                    <input type="password" name="contrasena" required>
                </div>

                <div class="form-group">
                    <label>Confirmar contraseña <span class="required">*</span></label>
                    <input type="password" name="contrasena_confirmation" required>
                </div>
            </div>

            <p class="info-text">
                Su usuario para iniciar sesión será su CI o su correo electrónico. El correo es obligatorio para crear la cuenta.
            </p>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">Crear cuenta</button>
                <a href="{{ route('login') }}" class="btn btn-secondary">Volver</a>
            </div>
        </form>
    </div>

</body>
</html>