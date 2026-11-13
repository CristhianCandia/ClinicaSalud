@extends($layout)

@section('title', 'Cita ' . $cita->codigo)

@section('content')
    <h2>Cita médica {{ $cita->codigo }}</h2>
    <p class="sub">Comprobante, acciones según el estado y trazabilidad de la cita</p>

    <div class="panel" style="margin-top: 20px; max-width: 760px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;">{{ $cita->codigo }}</h3>
            <span class="estado activo">{{ ucfirst(mb_strtolower($cita->estado)) }}</span>
        </div>

        <p><strong>Paciente:</strong> {{ $cita->paciente->nombres }} {{ $cita->paciente->apellidos }}
            ({{ $cita->paciente->codigo }}, CI {{ $cita->paciente->ci }})</p>
        <p><strong>Especialidad:</strong> {{ $cita->especialidad->nombre }}</p>
        <p><strong>Médico:</strong> Dr(a). {{ $cita->medico->nombres }} {{ $cita->medico->apellidos }}</p>
        <p><strong>Fecha:</strong> {{ $cita->fecha->format('d/m/Y') }} ({{ $dias[$cita->fecha->dayOfWeekIso] }})</p>
        <p><strong>Horario:</strong> {{ substr($cita->hora_inicio, 0, 5) }} – {{ substr($cita->hora_fin, 0, 5) }}</p>
        <p><strong>Motivo:</strong> {{ $cita->motivo }}</p>
        <p><strong>Registrada:</strong> {{ $cita->fecha_creacion?->format('d/m/Y H:i') }}
            por {{ $cita->registrador->nombre_usuario }}</p>

        <div class="acciones-form" style="margin-top: 16px;">
            <button type="button" class="boton gris" onclick="window.print()">Imprimir</button>
            <button type="button" class="boton" disabled title="Disponible en CU-15">Reprogramar</button>
            <button type="button" class="boton" disabled title="Disponible en CU-22">Confirmar asistencia</button>
            <button type="button" class="boton peligro" disabled title="Disponible en CU-16">Cancelar cita</button>
        </div>
    </div>

    <div class="panel" style="margin-top: 20px; max-width: 760px;">
        <h3>Historial de la cita (trazabilidad)</h3>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>Acción</th>
                    <th>Estado</th>
                    <th>Cambio de horario</th>
                    <th>Observación</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($historial as $h)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($h->fecha_registro)->format('d/m/Y H:i') }}</td>
                        <td>{{ ucfirst(mb_strtolower($h->accion)) }}</td>
                        <td>{{ ucfirst(mb_strtolower($h->estado_nuevo)) }}</td>
                        <td>
                            {{ $h->fecha_nueva
                                ? \Carbon\Carbon::parse($h->fecha_nueva)->format('d/m/Y') . ' ' . substr($h->hora_nueva, 0, 5)
                                : '—' }}
                        </td>
                        <td>{{ $h->observacion ?? '—' }}</td>
                        <td>{{ $h->nombre_usuario }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="vacio">Sin movimientos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection