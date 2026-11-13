@extends('layouts.admin')

@section('title', 'Especialidades')

@section('content')
    <h2>Gestionar especialidades</h2>
    <p class="sub">El nombre no puede repetirse, sin importar mayúsculas o espacios</p>

    @if (session('exito'))
        <div class="exito" role="status" style="margin-top: 16px;">{{ session('exito') }}</div>
    @endif

    <div class="panel" style="margin-top: 20px;">
        <h3>{{ $editando ? 'Editar especialidad' : 'Nueva especialidad' }}</h3>

        <form method="POST"
              action="{{ $editando
                    ? route('admin.especialidades.update', $editando->id_especialidad)
                    : route('admin.especialidades.store') }}"
              class="formulario">
            @csrf
            @if ($editando)
                @method('PUT')
            @endif

            <div>
                <label for="nombre">Nombre *</label>
                <input id="nombre" name="nombre" type="text"
                       value="{{ old('nombre', $editando->nombre ?? '') }}">
                @error('nombre') <small class="error-campo">{{ $message }}</small> @enderror
            </div>

            <div>
                <label for="descripcion">Descripción</label>
                <input id="descripcion" name="descripcion" type="text"
                       value="{{ old('descripcion', $editando->descripcion ?? '') }}">
                @error('descripcion') <small class="error-campo">{{ $message }}</small> @enderror
            </div>

            <div class="acciones-form" style="grid-column: 1 / -1;">
                <button type="submit" class="boton">{{ $editando ? 'Actualizar' : 'Guardar' }}</button>
                @if ($editando)
                    <a class="boton gris" href="{{ route('admin.especialidades.index') }}">Cancelar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="panel" style="margin-top: 20px;">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Especialidad</th>
                    <th>Descripción</th>
                    <th>Médicos</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($especialidades as $e)
                    <tr>
                        <td>{{ $e->nombre }}</td>
                        <td>{{ $e->descripcion ?? '—' }}</td>
                        <td>{{ $e->medicos_count }}</td>
                        <td>
                            <span class="estado {{ $e->estado ? 'activo' : 'inactivo' }}">
                                {{ $e->estado ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="acciones">
                            <a class="boton pequeno" href="{{ route('admin.especialidades.edit', $e->id_especialidad) }}">Editar</a>
                            <form method="POST" action="{{ route('admin.especialidades.toggle', $e->id_especialidad) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="boton pequeno {{ $e->estado ? 'peligro' : '' }}"
                                        onclick="return confirm('¿Estás seguro de cambiar el estado de esta especialidad?')">
                                    {{ $e->estado ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="vacio">No hay especialidades registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection