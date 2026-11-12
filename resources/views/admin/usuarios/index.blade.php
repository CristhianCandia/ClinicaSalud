@extends('layouts.admin')

@section('title', 'Gestionar usuarios')
@section('page-title', 'Gestionar usuarios')

@section('content')
    <!-- TARJETA: NUEVO USUARIO -->
    <div style="background: white; border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="color: #2c3e50; font-size: 16px; margin-bottom: 15px;">Nuevo usuario</h3>
        <p style="color: #7f8c8d; font-size: 13px; margin-bottom: 20px;">
            Registrar, modificar, asignar rol y activar o desactivar cuentas
        </p>

        <form method="POST" action="{{ isset($usuarioEditando) ? route('admin.usuarios.update', $usuarioEditando->id) : route('admin.usuarios.store') }}">
            @csrf
            @if(isset($usuarioEditando))
                @method('PUT')
            @endif

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Nombre de usuario <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="text" name="nombre_usuario" 
                           value="{{ old('nombre_usuario', $usuarioEditando->nombre_usuario ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                           required>
                    @error('nombre_usuario')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Correo electrónico <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="email" name="correo" 
                           value="{{ old('correo', $usuarioEditando->correo ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                           required>
                    @error('correo')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Contraseña <span style="color: #e74c3c;">*</span> 
                        @if(isset($usuarioEditando))
                            <span style="color: #95a5a6; font-weight: normal;">(dejar vacío para no cambiar)</span>
                        @endif
                    </label>
                    <input type="password" name="contrasena" 
                           {{ isset($usuarioEditando) ? '' : 'required' }}
                           placeholder="Mínimo 8 caracteres"
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    @error('contrasena')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Rol <span style="color: #e74c3c;">*</span>
                    </label>
                    <select name="rol_id" required
                            style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">Seleccione...</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" 
                                {{ old('rol_id', $usuarioEditando->rol_id ?? '') == $rol->id ? 'selected' : '' }}>
                                {{ $rol->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('rol_id')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <button type="submit" 
                    style="background: #2980b9; color: white; padding: 8px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500;">
                {{ isset($usuarioEditando) ? 'Actualizar' : 'Guardar' }}
            </button>
            
            @if(isset($usuarioEditando))
                <a href="{{ route('admin.usuarios.index') }}" 
                   style="background: #95a5a6; color: white; padding: 8px 20px; border-radius: 4px; text-decoration: none; margin-left: 10px; font-size: 14px;">
                    Cancelar
                </a>
            @endif
        </form>
    </div>

    <!-- TARJETA: LISTA DE USUARIOS -->
    <div style="background: white; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #ecf0f1;">
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Usuario</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Correo</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Rol</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Último acceso</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Estado</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 12px 15px; font-weight: 600; color: #2c3e50;">
                            {{ $usuario->nombre_usuario }}
                        </td>
                        <td style="padding: 12px 15px; color: #34495e;">
                            {{ $usuario->correo }}
                        </td>
                        <td style="padding: 12px 15px; color: #34495e;">
                            {{ $usuario->rol->nombre }}
                        </td>
                        <td style="padding: 12px 15px; color: #34495e;">
                            {{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td style="padding: 12px 15px;">
                            @if($usuario->estado)
                                <span style="background: #d4edda; color: #155724; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                    Activo
                                </span>
                            @else
                                <span style="background: #f8d7da; color: #721c24; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td style="padding: 12px 15px;">
                            <div style="display: flex; gap: 5px;">
                                <a href="{{ route('admin.usuarios.edit', $usuario->id) }}" 
                                   style="background: #3498db; color: white; padding: 5px 12px; border-radius: 4px; text-decoration: none; font-size: 12px;">
                                    Editar
                                </a>
                                
                                @if($usuario->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.usuarios.toggle', $usuario->id) }}" 
                                          style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                onclick="return confirm('¿Estás seguro de cambiar el estado de este usuario?')"
                                                style="background: #e74c3c; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                            {{ $usuario->estado ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                @else
                                    <span style="color: #95a5a6; font-size: 12px;">(Tú)</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #7f8c8d;">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection