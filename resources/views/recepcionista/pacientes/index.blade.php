@extends('layouts.recepcionista')

@section('title', 'Gestionar pacientes')

@section('content')
    <h2>Gestionar pacientes</h2>
    <p class="sub">Buscar por CI, nombre o apellido</p>

    @php $mostrarFormulario = $pacienteEditando || $errors->any() || request()->boolean('nuevo'); @endphp

    @if ($mostrarFormulario)
        <div class="panel" style="margin-top: 20px;">
            <h3>{{ $pacienteEditando ? 'Editar paciente' : 'Nuevo paciente' }}</h3>
            <p class="sub">Los campos con * son obligatorios. El CI no puede repetirse.</p>

            @if ($errors->any())
                <div class="alerta">
                    <ul style="margin: 0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ $pacienteEditando ? route('recepcionista.pacientes.update', $pacienteEditando->id_paciente) : route('recepcionista.pacientes.store') }}"
                  class="formulario">
                @csrf
                @if ($pacienteEditando)
                    @method('PUT')
                @endif

                <div>
                    <label for="ci">CI *</label>
                    <input id="ci" name="ci" type="text" value="{{ old('ci', $pacienteEditando->ci ?? '') }}" required>
                    @error('ci') <small class="error-campo">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label for="nombres">Nombres *</label>
                    <input id="nombres" name="nombres" type="text" value="{{ old('nombres', $pacienteEditando->nombres ?? '') }}" required>
                    @error('nombres') <small class="error-campo">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label for="apellidos">Apellidos *</label>
                    <input id="apellidos" name="apellidos" type="text" value="{{ old('apellidos', $pacienteEditando->apellidos ?? '') }}" required>
                    @error('apellidos') <small class="error-campo">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label for="fecha_nacimiento">Fecha de nacimiento *</label>
                    <input id="fecha_nacimiento" name="fecha_nacimiento" type="date"
                           value="{{ old('fecha_nacimiento', $pacienteEditando?->fecha_nacimiento?->format('Y-m-d') ?? '') }}" required>
                    @error('fecha_nacimiento') <small class="error-campo">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label for="sexo">Sexo *</label>
                    <select id="sexo" name="sexo" required>
                        <option value="">Seleccione...</option>
                        <option value="M" {{ old('sexo', $pacienteEditando->sexo ?? '') === 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('sexo', $pacienteEditando->sexo ?? '') === 'F' ? 'selected' : '' }}>Femenino</option>
                        <option value="O" {{ old('sexo', $pacienteEditando->sexo ?? '') === 'O' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('sexo') <small class="error-campo">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label for="telefono">Teléfono *</label>
                    <input id="telefono" name="telefono" type="text" value="{{ old('telefono', $pacienteEditando->telefono ?? '') }}" required>
                    @error('telefono') <small class="error-campo">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label for="correo">Correo electrónico</label>
                    <input id="correo" name="correo" type="email" value="{{ old('correo', $pacienteEditando->correo ?? '') }}">
                    @error('correo') <small class="error-campo">{{ $message }}</small> @enderror
                </div>

                <div>
                    <label for="direccion">Dirección</label>
                    <input id="direccion" name="direccion" type="text" value="{{ old('direccion', $pacienteEditando->direccion ?? '') }}">
                    @error('direccion') <small class="error-campo">{{ $message }}</small> @enderror
                </div>

                <div class="acciones-form">
                    <button type="submit" class="boton">{{ $pacienteEditando ? 'Actualizar' : 'Guardar' }}</button>
                    @if ($pacienteEditando)
                        <a class="boton gris" href="{{ route('recepcionista.pacientes.index') }}">Cancelar</a>
                    @endif
                </div>
            </form>
        </div>
    @endif

    <div class="panel" style="margin-top: 20px;">
        <form method="GET" action="{{ route('recepcionista.pacientes.index') }}" class="buscar">
            <div>
                <label for="buscar">Buscar</label>
                <input id="buscar" name="buscar" type="text" value="{{ $buscar }}" placeholder="CI, nombre o apellido">
            </div>
            <button type="submit" class="boton">Buscar</button>
            <a class="boton gris" href="{{ route('recepcionista.pacientes.index', ['nuevo' => 1]) }}">+ Nuevo paciente</a>
        </form>

        <table class="tabla" style="margin-top: 16px;">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>CI</th>
                    <th>Nombre completo</th>
                    <th>Teléfono</th>
                    <th>Registro</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pacientes as $paciente)
                    <tr>
                        <td>{{ $paciente->codigo }}</td>
                        <td>{{ $paciente->ci }}</td>
                        <td>{{ $paciente->nombres }} {{ $paciente->apellidos }}</td>
                        <td>{{ $paciente->telefono }}</td>
                        <td>{{ $paciente->fecha_creacion?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            <span class="estado {{ $paciente->estado ? 'activo' : 'inactivo' }}">
                                {{ $paciente->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="acciones">
                            <a class="boton pequeno" href="{{ route('recepcionista.pacientes.edit', $paciente->id_paciente) }}">Editar</a>
                            <form method="POST" action="{{ route('recepcionista.pacientes.toggle', $paciente->id_paciente) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="boton pequeno {{ $paciente->estado ? 'peligro' : '' }}"
                                        onclick="return confirm('¿Estás seguro de cambiar el estado de este paciente?')">
                                    {{ $paciente->estado ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="vacio">No hay pacientes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection