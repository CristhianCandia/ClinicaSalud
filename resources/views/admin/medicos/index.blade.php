@extends('layouts.admin')

@section('title', 'Gestionar médicos')
@section('page-title', 'Gestionar médicos')

@section('content')
    <p style="color: #7f8c8d; margin-bottom: 20px; font-size: 14px;">
        Un médico inactivo no puede recibir nuevas citas; su historial se conserva
    </p>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <!-- TARJETA: BÚSQUEDA Y LISTA -->
    <div style="background: white; border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        
        <!-- Barra de búsqueda y botón Nuevo -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px;">
            <div style="display: flex; gap: 10px; flex: 1;">
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Nombre o código
                    </label>
                    <form method="GET" action="{{ route('admin.medicos.index') }}" style="display: flex; gap: 10px;">
                        <input type="text" name="buscar" 
                               value="{{ request('buscar') }}" 
                               placeholder="Buscar por nombre o código"
                               style="flex: 1; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        
                        <select name="especialidad" 
                                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <option value="">Todas</option>
                            @foreach($especialidades as $esp)
                                <option value="{{ $esp->id }}" {{ request('especialidad') == $esp->id ? 'selected' : '' }}>
                                    {{ $esp->nombre }}
                                </option>
                            @endforeach
                        </select>
                        
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </form>
                </div>
            </div>
            
            <div>
                <button type="button" class="btn btn-secondary" onclick="mostrarFormulario()">
                    + Nuevo médico
                </button>
            </div>
        </div>

        <!-- TABLA DE MÉDICOS -->
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #ecf0f1;">
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Código</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Reg. profesional</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Médico</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Especialidades</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Teléfono</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Estado</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($medicos as $medico)
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 12px 15px; font-weight: 600; color: #2c3e50;">
                            {{ $medico->codigo ?? '—' }}
                        </td>
                        <td style="padding: 12px 15px; color: #34495e;">
                            {{ $medico->registro_profesional }}
                        </td>
                        <td style="padding: 12px 15px; color: #34495e;">
                            Dr(a). {{ $medico->nombres }} {{ $medico->apellidos }}
                        </td>
                        <td style="padding: 12px 15px; color: #34495e; font-size: 13px;">
                            {{ $medico->especialidades ?: '—' }}
                        </td>
                        <td style="padding: 12px 15px; color: #34495e;">
                            {{ $medico->telefono }}
                        </td>
                        <td style="padding: 12px 15px;">
                            @if($medico->estado)
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
                                <a href="{{ route('admin.medicos.edit', $medico->id) }}" 
                                   class="btn btn-primary" style="padding: 5px 12px; font-size: 12px;">
                                    Editar
                                </a>

                                <a href="#" class="btn" style="background: #95a5a6; color: white; padding: 5px 12px; font-size: 12px;">
                                    Horarios
                                </a>

                                <form method="POST" action="{{ route('admin.medicos.toggle', $medico->id) }}" 
                                      style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            onclick="return confirm('¿Estás seguro de cambiar el estado de este médico?')"
                                            class="btn btn-danger" style="padding: 5px 12px; font-size: 12px;">
                                        {{ $medico->estado ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d;">
                            No hay médicos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- FORMULARIO (oculto inicialmente) -->
    <div id="formularioMedico" style="background: white; border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: {{ isset($medicoEditando) ? 'block' : 'none' }};">
        <h3 style="color: #2c3e50; font-size: 16px; margin-bottom: 15px;">
            {{ isset($medicoEditando) ? 'Editar médico' : 'Nuevo médico' }}
        </h3>

        <form method="POST" action="{{ isset($medicoEditando) ? route('admin.medicos.update', $medicoEditando->id) : route('admin.medicos.store') }}">
            @csrf
            @if(isset($medicoEditando))
                @method('PUT')
            @endif

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Registro profesional <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="text" name="registro_profesional" 
                           value="{{ old('registro_profesional', $medicoEditando->registro_profesional ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                           required>
                    @error('registro_profesional')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Nombres <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="text" name="nombres" 
                           value="{{ old('nombres', $medicoEditando->nombres ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                           required>
                    @error('nombres')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Apellidos <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="text" name="apellidos" 
                           value="{{ old('apellidos', $medicoEditando->apellidos ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                           required>
                    @error('apellidos')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Teléfono <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="text" name="telefono" 
                           value="{{ old('telefono', $medicoEditando->telefono ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                           required>
                    @error('telefono')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Correo electrónico <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="email" name="correo" 
                           value="{{ old('correo', $medicoEditando->correo ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                           required>
                    @error('correo')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Cuenta de usuario (rol Médico)
                    </label>
                    <select name="usuario_id" 
                            style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">Sin cuenta</option>
                        @foreach($usuariosMedico as $usuario)
                            <option value="{{ $usuario->id }}" 
                                {{ old('usuario_id', $medicoEditando->usuario_id ?? '') == $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->nombre_usuario }} ({{ $usuario->correo }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #34495e; font-size: 13px; font-weight: 500;">
                    Especialidades <span style="color: #e74c3c;">*</span>
                </label>
                <div style="display: flex; flex-wrap: wrap; gap: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #fafafa;">
                    @forelse($especialidades as $esp)
                        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 14px; color: #34495e;">
                            <input type="checkbox" name="especialidades[]" value="{{ $esp->id }}"
                                {{ in_array($esp->id, old('especialidades', $medicoEditando->especialidades_seleccionadas ?? [])) ? 'checked' : '' }}>
                            {{ $esp->nombre }}
                        </label>
                    @empty
                        <span style="color: #7f8c8d; font-size: 13px;">No hay especialidades activas. Registre una primero.</span>
                    @endforelse
                </div>
                @error('especialidades')
                    <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                {{ isset($medicoEditando) ? 'Actualizar' : 'Guardar' }}
            </button>
            
            <button type="button" class="btn btn-secondary" style="margin-left: 10px;" onclick="ocultarFormulario()">
                Cancelar
            </button>
        </form>
    </div>

    <script>
        function mostrarFormulario() {
            document.getElementById('formularioMedico').style.display = 'block';
            // Scroll al formulario
            document.getElementById('formularioMedico').scrollIntoView({ behavior: 'smooth' });
        }
        
        function ocultarFormulario() {
            document.getElementById('formularioMedico').style.display = 'none';
            // Recargar para limpiar el formulario
            window.location.href = '{{ route('admin.medicos.index') }}';
        }
    </script>
@endsection