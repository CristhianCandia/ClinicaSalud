@extends('layouts.admin')

@section('title', 'Médicos')

@section('content')
    <h2>Gestionar médicos</h2>
    <p class="sub">Un médico inactivo no puede recibir nuevas citas; su historial se conserva</p>

    @if (session('exito'))
        <div class="exito" role="status" style="margin-top: 16px;">{{ session('exito') }}</div>
    @endif

    <div class="panel" style="margin-top: 20px;">
        <form method="GET" action="{{ route('admin.medicos.index') }}" class="buscar">
            <div>
                <label for="buscar">Nombre o código</label>
                <input id="buscar" name="buscar" type="text" value="{{ $buscar }}">
            </div>

            <div>
                <label for="especialidad">Especialidad</label>
                <select id="especialidad" name="especialidad">
                    <option value="">Todas</option>
                    @foreach ($especialidades as $e)
                        <option value="{{ $e->id_especialidad }}"
                                {{ (string) $especialidadSeleccionada === (string) $e->id_especialidad ? 'selected' : '' }}>
                            {{ $e->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="boton">Buscar</button>
            <a class="boton gris" href="{{ route('admin.medicos.create') }}">+ Nuevo médico</a>
        </form>

        <table class="tabla" style="margin-top: 16px;">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Reg. profesional</th>
                    <th>Médico</th>
                    <th>Especialidades</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($medicos as $m)
                    <tr>
                        <td>{{ $m->codigo }}</td>
                        <td>{{ $m->registro_profesional }}</td>
                        <td>Dr(a). {{ $m->nombres }} {{ $m->apellidos }}</td>
                        <td>{{ $m->especialidades->pluck('nombre')->implode(', ') ?: '—' }}</td>
                        <td>{{ $m->telefono }}</td>
                        <td>
                            <span class="estado {{ $m->estado ? 'activo' : 'inactivo' }}">
                                {{ $m->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="acciones">
                            <a class="boton pequeno" href="{{ route('admin.medicos.edit', $m->id_medico) }}">Editar</a>
                            <button type="button" class="boton pequeno gris" disabled title="Disponible en CU-08">Horarios</button>
                            <form method="POST" action="{{ route('admin.medicos.toggle', $m->id_medico) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="boton pequeno {{ $m->estado ? 'peligro' : '' }}"
                                        onclick="return confirm('¿Estás seguro de cambiar el estado de este médico?')">
                                    {{ $m->estado ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="vacio">No hay médicos que coincidan con la búsqueda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection