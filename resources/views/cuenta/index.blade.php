@extends($layout)

@section('title', 'Mi cuenta')

@section('content')
    <h2>Mi cuenta</h2>
    <p class="sub">Datos de su cuenta y cambio de contraseña</p>

    <div class="panel" style="margin-top: 20px; max-width: 720px;">
        <table class="tabla">
            <tbody>
                <tr>
                    <th style="width: 200px;">Usuario</th>
                    <td><strong>{{ $usuario->nombre_usuario }}</strong></td>
                </tr>
                <tr>
                    <th>Rol</th>
                    <td>{{ ucfirst(mb_strtolower($usuario->rol->nombre)) }}</td>
                </tr>
                <tr>
                    <th>Correo</th>
                    <td>{{ $usuario->correo }}</td>
                </tr>
                <tr>
                    <th>Último acceso</th>
                    <td>{{ $usuario->ultimo_acceso?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="panel" style="margin-top: 20px; max-width: 720px;">
        <h3>Cambiar contraseña</h3>

        @if ($errors->any())
            <div class="alerta" role="alert" style="margin-top: 12px;">Revise los campos marcados.</div>
        @endif

        <form method="POST" action="{{ route('cuenta.contrasena') }}" class="formulario" style="margin-top: 12px;">
            @csrf
            @method('PUT')

            <div style="grid-column: 1 / -1;">
                <label for="contrasena_actual">Contraseña actual *</label>
                <input id="contrasena_actual" name="contrasena_actual" type="password"
                       autocomplete="current-password">
                @error('contrasena_actual')
                    <small class="error-campo">{{ $message }}</small>
                @enderror
            </div>

            <div>
                <label for="contrasena_nueva">Nueva contraseña *</label>
                <input id="contrasena_nueva" name="contrasena_nueva" type="password"
                       autocomplete="new-password">
                <small class="sub">Mínimo 8 caracteres.</small>
                @error('contrasena_nueva')
                    <small class="error-campo">{{ $message }}</small>
                @enderror
            </div>

            <div>
                <label for="contrasena_nueva_confirmation">Confirmar nueva contraseña *</label>
                <input id="contrasena_nueva_confirmation" name="contrasena_nueva_confirmation" type="password"
                       autocomplete="new-password">
            </div>

            <div class="acciones-form" style="grid-column: 1 / -1;">
                <button type="submit" class="boton">Cambiar contraseña</button>
            </div>
        </form>
    </div>
@endsection