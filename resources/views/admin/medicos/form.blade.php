@extends('layouts.admin')

@section('title', $medico ? 'Editar médico' : 'Nuevo médico')

@section('content')
    <h2>{{ $medico ? 'Editar médico' : 'Nuevo médico' }}</h2>
    <p class="sub">Marque una o varias especialidades</p>

    <div class="panel" style="margin-top: 20px; max-width: 900px;">
        <form method="POST"
              action="{{ $medico ? route('admin.medicos.update', $medico->id_medico) : route('admin.medicos.store') }}"
              class="formulario">
            @csrf
            @if ($medico)
                @method('PUT')
            @endif

            <div>
                <label for="registro_profesional">Registro profesional *</label>
                <input id="registro_profesional" name="registro_profesional" type="text"
                       value="{{ old('registro_profesional', $medico->registro_profesional ?? '') }}">
                @error('registro_profesional') <small class="error-campo">{{ $message }}</small> @enderror
            </div>

            <div>
                <label for="nombres">Nombres *</label>
                <input id="nombres" name="nombres" type="text"
                       value="{{ old('nombres', $medico->nombres ?? '') }}">
                @error('nombres') <small class="error-campo">{{ $message }}</small> @enderror
            </div>

            <div>
                <label for="apellidos">Apellidos *</label>
                <input id="apellidos" name="apellidos" type="text"
                       value="{{ old('apellidos', $medico->apellidos ?? '') }}">
                @error('apellidos') <small class="error-campo">{{ $message }}</small> @enderror
            </div>

            <div>
                <label for="telefono">Teléfono *</label>
                <input id="telefono" name="telefono" type="text"
                       value="{{ old('telefono', $medico->telefono ?? '') }}">
                @error('telefono') <small class="error-campo">{{ $message }}</small> @enderror
            </div>

            <div>
                <label for="correo">Correo electrónico *</label>
                <input id="correo" name="correo" type="email"
                       value="{{ old('correo', $medico->correo ?? '') }}">
                @error('correo') <small class="error-campo">{{ $message }}</small> @enderror
            </div>

            <div>
                <label for="id_usuario">Cuenta de usuario (rol Médico)</label>
                <select id="id_usuario" name="id_usuario">
                    <option value="">Sin cuenta</option>
                    @foreach ($cuentas as $c)
                        <option value="{{ $c->id_usuario }}"
                                {{ (string) old('id_usuario', $medico->id_usuario ?? '') === (string) $c->id_usuario ? 'selected' : '' }}>
                            {{ $c->nombre_usuario }}
                        </option>
                    @endforeach
                </select>
                @error('id_usuario') <small class="error-campo">{{ $message }}</small> @enderror
            </div>

            <div style="grid-column: 1 / -1;">
                <label>Especialidades *</label>
                @php
                    $marcadas = old('especialidades', $medico ? $medico->especialidades->pluck('id_especialidad')->all() : []);
                @endphp
                <div style="display: flex; flex-wrap: wrap; gap: 16px; margin-top: 6px;">
                    @foreach ($especialidades as $e)
                        <label style="display: flex; align-items: center; gap: 6px; margin: 0; font-weight: 400;">
                            <input type="checkbox" name="especialidades[]" value="{{ $e->id_especialidad }}"
                                   style="width: auto;"
                                   {{ in_array($e->id_especialidad, $marcadas) ? 'checked' : '' }}>
                            {{ $e->nombre }}{{ $e->estado ? '' : ' (inactiva)' }}
                        </label>
                    @endforeach
                </div>
                @error('especialidades') <small class="error-campo">{{ $message }}</small> @enderror
            </div>

            <div class="acciones-form" style="grid-column: 1 / -1;">
                <button type="submit" class="boton">{{ $medico ? 'Actualizar' : 'Guardar' }}</button>
                <a class="boton gris" href="{{ route('admin.medicos.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection