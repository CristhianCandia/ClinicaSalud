@extends('layouts.admin')

@section('title', 'Gestionar especialidades')
@section('page-title', 'Gestionar especialidades')

@section('content')
    <p style="color: #7f8c8d; margin-bottom: 20px; font-size: 14px;">
        El nombre no puede repetirse, sin importar mayúsculas o espacios
    </p>

    <!-- TARJETA: NUEVA ESPECIALIDAD -->
    <div style="background: white; border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="color: #2c3e50; font-size: 16px; margin-bottom: 15px;">
            {{ isset($especialidadEditando) ? 'Editar especialidad' : 'Nueva especialidad' }}
        </h3>

        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom: 15px;">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ isset($especialidadEditando) ? route('admin.especialidades.update', $especialidadEditando->id) : route('admin.especialidades.store') }}">
            @csrf
            @if(isset($especialidadEditando))
                @method('PUT')
            @endif

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Nombre <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="text" name="nombre" 
                           value="{{ old('nombre', $especialidadEditando->nombre ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                           required>
                    @error('nombre')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Descripción
                    </label>
                    <input type="text" name="descripcion" 
                           value="{{ old('descripcion', $especialidadEditando->descripcion ?? '') }}" 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                {{ isset($especialidadEditando) ? 'Actualizar' : 'Guardar' }}
            </button>
            
            @if(isset($especialidadEditando))
                <a href="{{ route('admin.especialidades.index') }}" class="btn btn-secondary" style="margin-left: 10px;">
                    Cancelar
                </a>
            @endif
        </form>
    </div>

    <!-- TARJETA: LISTA DE ESPECIALIDADES -->
    <div style="background: white; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #ecf0f1;">
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Especialidad</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Descripción</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Médicos</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Estado</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($especialidades as $esp)
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 12px 15px; font-weight: 600; color: #2c3e50;">
                            {{ $esp->nombre }}
                        </td>
                        <td style="padding: 12px 15px; color: #34495e;">
                            {{ $esp->descripcion ?? '—' }}
                        </td>
                        <td style="padding: 12px 15px; color: #34495e;">
                            {{ $esp->total_medicos ?? 0 }}
                        </td>
                        <td style="padding: 12px 15px;">
                            @if($esp->estado)
                                <span style="background: #d4edda; color: #155724; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                    Activa
                                </span>
                            @else
                                <span style="background: #f8d7da; color: #721c24; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                    Inactiva
                                </span>
                            @endif
                        </td>
                        <td style="padding: 12px 15px;">
                            <div style="display: flex; gap: 5px;">
                                <a href="{{ route('admin.especialidades.edit', $esp->id) }}" 
                                   class="btn btn-primary" style="padding: 5px 12px; font-size: 12px;">
                                    Editar
                                </a>

                                <form method="POST" action="{{ route('admin.especialidades.toggle', $esp->id) }}" 
                                      style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            onclick="return confirm('¿Estás seguro de cambiar el estado de esta especialidad?')"
                                            class="btn btn-danger" style="padding: 5px 12px; font-size: 12px;">
                                        {{ $esp->estado ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: #7f8c8d;">
                            No hay especialidades registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection