@extends('layouts.admin')

@section('title', 'Gestionar horarios de atención')
@section('page-title', 'Gestionar horarios de atención')

@section('content')
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('exito'))
        <div class="alert alert-success">{{ session('exito') }}</div>
    @endif

    <!-- SELECTOR DE MÉDICO -->
    <div style="background: white; border-radius: 8px; padding: 20px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <form method="GET" action="{{ route('admin.horarios.index') }}">
            <div style="display: flex; gap: 10px; align-items: flex-end;">
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Médico
                    </label>
                    <select name="medico_id" style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <option value="">-- Seleccione un médico --</option>
                        @foreach($medicos as $med)
                            <option value="{{ $med->id }}">{{ $med->nombres }} {{ $med->apellidos }} — {{ $med->codigo }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="height: 42px;">Ver</button>
            </div>
        </form>
    </div>

    @if($medicoSeleccionado)
        <p style="color: #2c3e50; font-size: 14px; margin-bottom: 20px;">
            <strong>Dr(a). {{ $medicoSeleccionado->nombres }} {{ $medicoSeleccionado->apellidos }} ({{ $medicoSeleccionado->codigo }})</strong>
        </p>

        <!-- FORMULARIO: NUEVO BLOQUE DE HORARIO -->
        <div style="background: white; border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="color: #2c3e50; font-size: 16px; margin-bottom: 15px;">
                @if(isset($horarioEditando))
                    Editar bloque de horario
                @else
                    Nuevo bloque de horario
                @endif
            </h3>

            <form method="POST" action="{{ isset($horarioEditando) ? route('admin.horarios.update', $horarioEditando->id) : route('admin.horarios.store') }}">
                @csrf
                @if(isset($horarioEditando))
                    @method('PUT')
                @endif

                <input type="hidden" name="medico_id" value="{{ $medicoSeleccionado->id }}">

                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                            Día <span style="color: #e74c3c;">*</span>
                        </label>
                        <select name="dia_semana" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <option value="">Seleccione...</option>
                            <option value="1" {{ old('dia_semana', $horarioEditando->dia_semana ?? '') == 1 ? 'selected' : '' }}>Lunes</option>
                            <option value="2" {{ old('dia_semana', $horarioEditando->dia_semana ?? '') == 2 ? 'selected' : '' }}>Martes</option>
                            <option value="3" {{ old('dia_semana', $horarioEditando->dia_semana ?? '') == 3 ? 'selected' : '' }}>Miércoles</option>
                            <option value="4" {{ old('dia_semana', $horarioEditando->dia_semana ?? '') == 4 ? 'selected' : '' }}>Jueves</option>
                            <option value="5" {{ old('dia_semana', $horarioEditando->dia_semana ?? '') == 5 ? 'selected' : '' }}>Viernes</option>
                            <option value="6" {{ old('dia_semana', $horarioEditando->dia_semana ?? '') == 6 ? 'selected' : '' }}>Sábado</option>
                            <option value="7" {{ old('dia_semana', $horarioEditando->dia_semana ?? '') == 7 ? 'selected' : '' }}>Domingo</option>
                        </select>
                        @error('dia_semana')
                            <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                            Hora inicial <span style="color: #e74c3c;">*</span>
                        </label>
                        <input type="time" name="hora_inicio" value="{{ old('hora_inicio', $horarioEditando->hora_inicio ?? '') }}" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        @error('hora_inicio')
                            <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                            Hora final <span style="color: #e74c3c;">*</span>
                        </label>
                        <input type="time" name="hora_fin" value="{{ old('hora_fin', $horarioEditando->hora_fin ?? '') }}" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        @error('hora_fin')
                            <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                            Duración de cita (min) <span style="color: #e74c3c;">*</span>
                        </label>
                        <input type="number" name="duracion_cita_min" value="{{ old('duracion_cita_min', $horarioEditando->duracion_cita_min ?? 30) }}" min="10" max="120" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        @error('duracion_cita_min')
                            <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    @if(isset($horarioEditando))
                        Actualizar
                    @else
                        Guardar
                    @endif
                </button>
                
                @if(isset($horarioEditando))
                    <a href="{{ route('admin.horarios.index', ['medico_id' => $medicoSeleccionado->id]) }}" class="btn btn-secondary" style="margin-left: 10px;">
                        Cancelar
                    </a>
                @endif
            </form>
        </div>

        <!-- TABLA: HORARIOS REGISTRADOS -->
        <div style="background: white; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #ecf0f1;">
                        <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Día</th>
                        <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Hora inicial</th>
                        <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Hora final</th>
                        <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Duración</th>
                        <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Citas por bloque</th>
                        <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Estado</th>
                        <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($horarios as $horario)
                        <tr style="border-bottom: 1px solid #ecf0f1;">
                            <td style="padding: 12px 15px; color: #34495e;">
                                {{ ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'][$horario->dia_semana - 1] }}
                            </td>
                            <td style="padding: 12px 15px; color: #34495e;">
                                {{ date('H:i', strtotime($horario->hora_inicio)) }}
                            </td>
                            <td style="padding: 12px 15px; color: #34495e;">
                                {{ date('H:i', strtotime($horario->hora_fin)) }}
                            </td>
                            <td style="padding: 12px 15px; color: #34495e;">
                                {{ $horario->duracion_cita_min }} min
                            </td>
                            <td style="padding: 12px 15px; color: #34495e;">
                                {{ $horario->citas_por_bloque ?? 0 }}
                            </td>
                            <td style="padding: 12px 15px;">
                                @if($horario->estado)
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
                                    <a href="{{ route('admin.horarios.edit', $horario->id) }}" class="btn btn-primary" style="padding: 5px 12px; font-size: 12px;">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('admin.horarios.toggle', $horario->id) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" onclick="return confirm('¿Estás seguro?')" class="btn btn-danger" style="padding: 5px 12px; font-size: 12px;">
                                            {{ $horario->estado ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d;">
                                No hay horarios registrados para este médico.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div style="background: white; border-radius: 8px; padding: 40px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <p style="color: #7f8c8d; font-size: 16px;">Seleccione un médico para gestionar sus horarios de atención</p>
        </div>
    @endif
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[method="POST"]');
        const horaInicioInput = document.querySelector('input[name="hora_inicio"]');
        const horaFinInput = document.querySelector('input[name="hora_fin"]');

        if (horaInicioInput && horaFinInput) {
            form.addEventListener('submit', function(e) {
                if (horaInicioInput.value >= horaFinInput.value) {
                    e.preventDefault();
                    alert('La hora final debe ser posterior a la hora inicial.');
                    horaFinInput.focus();
                }
            });
        }
    });
</script>
@endsection