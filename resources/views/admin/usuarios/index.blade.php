@extends('layouts.admin')

@section('title', 'Gestionar usuarios')

@section('content')
    <h2>Gestionar usuarios</h2>
    <p class="sub">Registrar, modificar, asignar rol y activar o desactivar cuentas</p>

    @if ($errors->any())
        <div class="alerta">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel" style="margin-top: 20px;">
        <h3>{{ isset($usuarioEditando) ? 'Editar usuario' : 'Nuevo usuario' }}</h3>

        <form method="POST"
              action="{{ isset($usuarioEditando) ? route('admin.usuarios.update', $usuarioEditando->id_usuario) : route('admin.usuarios.store') }}"
              class="formulario">
            @csrf
            @if (isset($usuarioEditando))
                @method('PUT')
            @endif

            <div>
                <label for="nombre_usuario">Nombre de usuario *</label>
                <input id="nombre_usuario" name="nombre_usuario" type="text"
                       value="{{ old('nombre_usuario', $usuarioEditando->nombre_usuario ?? '') }}" required>
            </div>

            <div>
                <label for="correo">Correo electrónico *</label>
                <input id="correo" name="correo" type="email"
                       value="{{ old('correo', $usuarioEditando->correo ?? '') }}" required>
            </div>

            <div>
                <label for="contrasena">
                    Contraseña *
                    @if (isset($usuarioEditando))
                        <span class="sub">(dejar vacío para no cambiar)</span>
                    @endif
                </label>
                <input id="contrasena" name="contrasena" type="password" placeholder="Mínimo 8 caracteres"
                       {{ isset($usuarioEditando) ? '' : 'required' }}>
            </div>

            <div>
                <label for="id_rol">Rol *</label>
                <select id="id_rol" name="id_rol" required>
                    <option value="">Seleccione...</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id_rol }}"
                            {{ (string) old('id_rol', $usuarioEditando->id_rol ?? '') === (string) $rol->id_rol ? 'selected' : '' }}>
                            {{ $rol->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="acciones-form">
                <button type="submit" class="boton">{{ isset($usuarioEditando) ? 'Actualizar' : 'Guardar' }}</button>
                @if (isset($usuarioEditando))
                    <a class="boton gris" href="{{ route('admin.usuarios.index') }}">Cancelar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="panel" style="margin-top: 20px;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Último acceso</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                    <tr>
                        <td><strong>{{ $usuario->nombre_usuario }}</strong></td>
                        <td>{{ $usuario->correo }}</td>
                        <td>{{ $usuario->rol->nombre }}</td>
                        <td>{{ $usuario->ultimo_acceso?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>
                            <span class="estado {{ $usuario->estado ? 'activo' : 'inactivo' }}">
                                {{ $usuario->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="acciones">
                            <a class="boton pequeno" href="{{ route('admin.usuarios.edit', $usuario->id_usuario) }}">Editar</a>

                            @if ($usuario->id_usuario !== auth()->id())
                                <form method="POST" action="{{ route('admin.usuarios.toggle', $usuario->id_usuario) }}" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="boton pequeno {{ $usuario->estado ? 'peligro' : '' }}"
                                            onclick="return confirm('¿Estás seguro de cambiar el estado de este usuario?')">
                                        {{ $usuario->estado ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                            @else
                                <span class="sub">(Tú)</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="vacio">No hay usuarios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection