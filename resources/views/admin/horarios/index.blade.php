@extends('layouts.admin')

@section('title', 'Horarios de atención')

@section('content')
    <h2>Gestionar horarios de atención</h2>
    @if ($medico)
        <p class="sub">Dr(a). {{ $medico->nombres }} {{ $medico->apellidos }} ({{ $medico->codigo }})</p>
    @endif

    @if (session('exito'))
        <div class="exito" role="status" style="margin-top: 16px;">{{ session('exito') }}</div>
    @endif

    @if (session('error'))
        <div class="alerta" role="alert" style="margin-top: 16px;">{{ session('error') }}</div>
    @endif

    <div class="panel" style="margin-top: 20px;">
        <form method="GET" action="{{ route('admin.horarios.index') }}" class="buscar">
            <div>
                <label for="medico">Médico</label>
                <select id="medico" name="medico">
                    <option value="">Seleccione un médico...</option>
                    @foreach ($medicos as $m)
                        <option value="{{ $m->id_medico }}"
                                {{ (string) ($medico->id_medico ?? '') === (string) $m->id_medico ? 'selected' : '' }}>
                            {{ $m->apellidos }}, {{ $m->nombres }} — {{ $m->codigo }}{{ $m->estado ? '' : ' (inactivo)' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="boton gris">Ver</button>
        </form>
    </div>

    @if (! $medico)
        <div class="panel" style="margin-top: 20px;">
            <p class="sub" style="margin: 0;">Seleccione un médico para ver o registrar sus horarios.</p>
        </div>
    @else
        @php
            $turnoActual = $editando
                ? (substr($editando->hora_inicio, 0, 5) === '13:00' ? 'TARDE' : 'MANANA')
                : null;
        @endphp

        <div class="panel" style="margin-top: 20px;">
            <h3>{{ $editando ? 'Editar bloque de horario' : 'Nuevo bloque de horario' }}</h3>

            @if ($errors->any())
                <div class="alerta" role="alert">
                    <ul style="margin: 0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ $editando ? route('admin.horarios.update', $editando->id_horario) : route('admin.horarios.store') }}"
                  class="formulario">
                @csrf
                @if ($editando)
                    @method('PUT')
                @endif
                <input type="hidden" name="id_medico" value="{{ $medico->id_medico }}">

                <div>
                    <label for="dia_semana">Día *</label>
                    <select id="dia_semana" name="dia_semana">
                        @foreach ($dias as $num => $nombreDia)
                            <option value="{{ $num }}"
                                    {{ (string) old('dia_semana', $editando->dia_semana ?? '') === (string) $num ? 'selected' : '' }}>
                                {{ $nombreDia }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="turno">Turno *</label>
                    <select id="turno" name="turno">
                        <option value="">Seleccione...</option>
                        @foreach ($turnos as $clave => $t)
                            <option value="{{ $clave }}" {{ old('turno', $turnoActual) === $clave ? 'selected' : '' }}>
                                {{ $t['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Duración de cada cita</label>
                    <input type="text" value="15 min (fija)" disabled>
                </div>

                <div class="acciones-form" style="grid-column: 1 / -1;">
                    <button type="submit" class="boton">{{ $editando ? 'Actualizar' : 'Guardar' }}</button>
                    @if ($editando)
                        <a class="boton gris" href="{{ route('admin.horarios.index', ['medico' => $medico->id_medico]) }}">Cancelar</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="panel" style="margin-top: 20px;">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Día</th>
                        <th>Hora inicial</th>
                        <th>Hora final</th>
                        <th>Duración</th>
                        <th>Citas por bloque</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bloques as $b)
                        <tr>
                            <td>{{ $dias[$b->dia_semana] }}</td>
                            <td>{{ substr($b->hora_inicio, 0, 5) }}</td>
                            <td>{{ substr($b->hora_fin, 0, 5) }}</td>
                            <td>{{ $b->duracion_cita_min }} min</td>
                            <td>{{ $b->citasPorBloque() }}</td>
                            <td>
                                <span class="estado {{ $b->estado ? 'activo' : 'inactivo' }}">
                                    {{ $b->estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="acciones">
                                <a class="boton pequeno" href="{{ route('admin.horarios.edit', $b->id_horario) }}">Editar</a>
                                <form method="POST" action="{{ route('admin.horarios.toggle', $b->id_horario) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="boton pequeno {{ $b->estado ? 'peligro' : '' }}"
                                            onclick="return confirm('¿Estás seguro de cambiar el estado de este bloque?')">
                                        {{ $b->estado ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="vacio">Este médico aún no tiene horarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection