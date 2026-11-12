@extends('layouts.recepcionista')

@section('title', 'Gestionar pacientes')
@section('page-title', 'Gestionar pacientes')

@section('content')
    <p style="color: #7f8c8d; margin-bottom: 20px; font-size: 14px;">
        Buscar por CI, nombre o apellido
    </p>

    <!-- TARJETA: BÚSQUEDA Y FORMULARIO -->
    <div style="background: white; border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        
        <!-- Formulario de búsqueda -->
        <form method="GET" action="{{ route('recepcionista.pacientes.index') }}" style="display: flex; gap: 10px; margin-bottom: 20px;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                    Buscar
                </label>
                <input type="text" name="buscar" 
                       value="{{ request('buscar') }}" 
                       placeholder="CI, nombre o apellido"
                       style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>
            <div style="display: flex; align-items: flex-end; gap: 10px;">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="{{ route('recepcionista.pacientes.index') }}" class="btn btn-secondary">+ Nuevo paciente</a>
            </div>
        </form>

        <!-- Formulario de registro/edición -->
        <form method="POST" action="{{ isset($pacienteEditando) ? route('recepcionista.pacientes.update', $pacienteEditando->id) : route('recepcionista.pacientes.store') }}">
            @csrf
            @if(isset($pacienteEditando))
                @method('PUT')
            @endif

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        CI <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="text" name="ci" 
                           value="{{ old('ci', $pacienteEditando->ci ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                           required>
                    @error('ci')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Nombres <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="text" name="nombres" 
                           value="{{ old('nombres', $pacienteEditando->nombres ?? '') }}" 
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
                           value="{{ old('apellidos', $pacienteEditando->apellidos ?? '') }}" 
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
                        Fecha de nacimiento <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="date" name="fecha_nacimiento" 
                           value="{{ old('fecha_nacimiento', $pacienteEditando->fecha_nacimiento ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                           required>
                    @error('fecha_nacimiento')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Sexo <span style="color: #e74c3c;">*</span>
                    </label>
                    <select name="sexo" required
                            style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">Seleccione...</option>
                        <option value="M" {{ old('sexo', $pacienteEditando->sexo ?? '') == 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('sexo', $pacienteEditando->sexo ?? '') == 'F' ? 'selected' : '' }}>Femenino</option>
                        <option value="O" {{ old('sexo', $pacienteEditando->sexo ?? '') == 'O' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('sexo')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Teléfono <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="text" name="telefono" 
                           value="{{ old('telefono', $pacienteEditando->telefono ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                           required>
                    @error('telefono')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Correo electrónico <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="email" name="correo" 
                           value="{{ old('correo', $pacienteEditando->correo ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                           required>
                    @error('correo')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Dirección
                    </label>
                    <input type="text" name="direccion" 
                           value="{{ old('direccion', $pacienteEditando->direccion ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Tipo de sangre
                    </label>
                    <select name="tipo_sangre"
                            style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">Seleccione...</option>
                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $tipo)
                            <option value="{{ $tipo }}" {{ old('tipo_sangre', $pacienteEditando->tipo_sangre ?? '') == $tipo ? 'selected' : '' }}>
                                {{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                {{ isset($pacienteEditando) ? 'Actualizar' : 'Guardar' }}
            </button>
            
            @if(isset($pacienteEditando))
                <a href="{{ route('recepcionista.pacientes.index') }}" class="btn btn-secondary" style="margin-left: 10px;">
                    Cancelar
                </a>
            @endif
        </form>
    </div>

    <!-- TARJETA: LISTA DE PACIENTES -->
    <div style="background: white; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #ecf0f1;">
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Código</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">CI</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Nombre completo</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Teléfono</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Registro</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Estado</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pacientes as $paciente)
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 12px 15px; color: #34495e;">
                            {{ $paciente->codigo ?? '—' }}
                        </td>
                        <td style="padding: 12px 15px; color: #34495e;">
                            {{ $paciente->ci }}
                        </td>
                        <td style="padding: 12px 15px; color: #34495e;">
                            {{ $paciente->nombres }} {{ $paciente->apellidos }}
                        </td>
                        <td style="padding: 12px 15px; color: #34495e;">
                            {{ $paciente->telefono }}
                        </td>
                        <td style="padding: 12px 15px; color: #34495e;">
                            {{ \Carbon\Carbon::parse($paciente->created_at)->format('d/m/Y') }}
                        </td>
                        <td style="padding: 12px 15px;">
                            @if($paciente->usuario_estado)
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
                                <a href="{{ route('recepcionista.pacientes.edit', $paciente->id) }}" 
                                   class="btn btn-primary" style="padding: 5px 12px; font-size: 12px;">
                                    Editar / citas
                                </a>
                                
                                <a href="#" class="btn btn-secondary" style="padding: 5px 12px; font-size: 12px;">
                                    Programar cita
                                </a>

                                <form method="POST" action="{{ route('recepcionista.pacientes.toggle', $paciente->id) }}" 
                                      style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            onclick="return confirm('¿Estás seguro de cambiar el estado de este paciente?')"
                                            class="btn btn-danger" style="padding: 5px 12px; font-size: 12px;">
                                        {{ $paciente->usuario_estado ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d;">
                            No hay pacientes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection