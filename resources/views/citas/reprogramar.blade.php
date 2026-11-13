@extends($layout)

@section('title', 'Reprogramar cita ' . $cita->codigo)

@section('content')
    <h2>Reprogramar cita {{ $cita->codigo }}</h2>
    <p class="sub">Elija una nueva fecha y hora libres del mismo médico</p>

    {{-- El layout de recepción ya muestra los mensajes de sesión; el de paciente no --}}
    @if (session('error') && $esPaciente)
        <div class="alerta" role="alert" style="margin-top: 16px;">{{ session('error') }}</div>
    @endif

    <div class="panel" style="margin-top: 20px; max-width: 760px;">
        <h3>Horario actual</h3>
        <p><strong>Paciente:</strong> {{ $cita->paciente->nombres }} {{ $cita->paciente->apellidos }}</p>
        <p><strong>Médico:</strong> Dr(a). {{ $cita->medico->nombres }} {{ $cita->medico->apellidos }}</p>
        <p><strong>Especialidad:</strong> {{ $cita->especialidad->nombre }}</p>
        <p><strong>Fecha y horario:</strong>
            {{ $cita->fecha->format('d/m/Y') }} ({{ $dias[$cita->fecha->dayOfWeekIso] }}),
            {{ substr($cita->hora_inicio, 0, 5) }} – {{ substr($cita->hora_fin, 0, 5) }}
        </p>
        <p><strong>Estado:</strong> {{ ucfirst(mb_strtolower($cita->estado)) }}</p>
    </div>

    <div class="panel" style="margin-top: 20px; max-width: 760px;">
        <form method="GET" action="{{ route('citas.reprogramar.edit', $cita->id_cita) }}" class="formulario">
            <div style="grid-column: 1 / -1;">
                <label for="fecha">Nueva fecha *</label>
                <input id="fecha" name="fecha" type="date" min="{{ $minFecha }}" value="{{ $fechaTexto }}" required>
            </div>
            <div class="acciones-form" style="grid-column: 1 / -1;">
                <button type="submit" class="boton">Ver horarios</button>
                <a class="boton gris" href="{{ route('citas.comprobante', $cita->id_cita) }}">Volver al comprobante</a>
            </div>
        </form>
    </div>

    @if ($error)
        <div class="alerta" role="alert" style="margin-top: 20px;">{{ $error }}</div>
    @elseif ($fecha)
        <div class="panel" style="margin-top: 20px; max-width: 760px;">
            <h3>Nuevos horarios disponibles — {{ $fecha->format('d/m/Y') }} ({{ $dias[$fecha->dayOfWeekIso] }})</h3>
            <p class="sub">El horario actual no aparece porque la cita lo ocupa.</p>

            <form method="POST" action="{{ route('citas.reprogramar.update', $cita->id_cita) }}"
                  class="formulario" style="margin-top: 12px;">
                @csrf
                @method('PUT')
                <input type="hidden" name="fecha" value="{{ $fecha->toDateString() }}">

                <div style="grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: 10px;">
                    @forelse ($libres as $hora)
                        <label class="boton gris" style="cursor: pointer;">
                            <input type="radio" name="hora" value="{{ $hora }}" required
                                   style="width: auto; margin-right: 6px;">{{ $hora }}
                        </label>
                    @empty
                        <div class="vacio">No hay horarios disponibles para esta fecha.</div>
                    @endforelse
                </div>
                @error('hora')
                    <small class="error-campo" style="grid-column: 1 / -1;">{{ $message }}</small>
                @enderror

                @if (count($libres))
                    <div style="grid-column: 1 / -1;">
                        <label for="observacion">Observación de la reprogramación *</label>
                        <input id="observacion" name="observacion" type="text" value="{{ old('observacion') }}" required>
                        @error('observacion')
                            <small class="error-campo">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="acciones-form" style="grid-column: 1 / -1;">
                        <button type="submit" class="boton">Guardar reprogramación</button>
                    </div>
                @endif
            </form>
        </div>
    @endif
@endsection