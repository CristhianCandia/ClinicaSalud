@extends($layout)

@section('title', 'Programar cita médica')

@section('content')
    <h2>Programar cita médica</h2>
    <p class="sub">Solo se muestran los horarios disponibles del médico</p>

    {{-- El layout de recepción ya muestra los mensajes de sesión; el de paciente no --}}
    @if (session('error') && $esPaciente)
        <div class="alerta" role="alert" style="margin-top: 16px;">{{ session('error') }}</div>
    @endif

    @if ($esPaciente && ! $pacienteFijo)
        <div class="alerta" role="alert" style="margin-top: 16px;">
            Su cuenta no tiene una ficha de paciente. Consulte en recepción.
        </div>
    @else
        <div class="panel" style="margin-top: 20px;">
            <form method="GET" action="{{ route('citas.create') }}" class="formulario">
                @if (! $esPaciente)
                    <div style="grid-column: 1 / -1;">
                        <label for="paciente">Paciente *</label>
                        <select id="paciente" name="paciente">
                            <option value="">Seleccione...</option>
                            @foreach ($pacientes as $p)
                                <option value="{{ $p->id_paciente }}"
                                        {{ (string) $pacienteSeleccionado === (string) $p->id_paciente ? 'selected' : '' }}>
                                    {{ $p->codigo }} — {{ $p->apellidos }} {{ $p->nombres }} (CI {{ $p->ci }})
                                </option>
                            @endforeach
                        </select>
                        <small class="sub">¿No está en la lista? <a href="{{ route('recepcionista.pacientes.index') }}">Registrar paciente</a></small>
                    </div>
                @endif

                <div>
                    <label for="especialidad">Especialidad *</label>
                    <select id="especialidad" name="especialidad"
                            onchange="this.form.medico.value=''; this.form.submit();">
                        <option value="">Seleccione...</option>
                        @foreach ($especialidades as $e)
                            <option value="{{ $e->id_especialidad }}"
                                    {{ (string) $especialidadSeleccionada === (string) $e->id_especialidad ? 'selected' : '' }}>
                                {{ $e->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="medico">Médico *</label>
                    <select id="medico" name="medico">
                        <option value="">Seleccione...</option>
                        @foreach ($medicos as $m)
                            <option value="{{ $m->id_medico }}"
                                    {{ (string) ($medico->id_medico ?? '') === (string) $m->id_medico ? 'selected' : '' }}>
                                Dr(a). {{ $m->nombres }} {{ $m->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="fecha">Fecha *</label>
                    <input id="fecha" name="fecha" type="date" min="{{ $minFecha }}" value="{{ $fechaTexto }}">
                </div>

                <div class="acciones-form" style="grid-column: 1 / -1;">
                    <button type="submit" class="boton">Ver horarios</button>
                </div>
            </form>
        </div>

        @if ($error)
            <div class="alerta" role="alert" style="margin-top: 20px;">{{ $error }}</div>
        @elseif ($medico && $fecha)
            <div class="panel" style="margin-top: 20px;">
                <h3>Horarios disponibles — {{ $fecha->format('d/m/Y') }} ({{ $dias[$fecha->dayOfWeekIso] }})</h3>

                <form method="POST" action="{{ route('citas.store') }}" class="formulario" style="margin-top: 12px;">
                    @csrf
                    <input type="hidden" name="id_medico" value="{{ $medico->id_medico }}">
                    <input type="hidden" name="id_especialidad" value="{{ $especialidadSeleccionada }}">
                    <input type="hidden" name="fecha" value="{{ $fecha->toDateString() }}">
                    @if (! $esPaciente)
                        <input type="hidden" name="paciente" value="{{ $pacienteSeleccionado }}">
                    @endif

                    <div style="grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: 10px;">
                        @foreach ($libres as $hora)
                            <label class="boton gris" style="cursor: pointer;">
                                <input type="radio" name="hora" value="{{ $hora }}" required
                                       style="width: auto; margin-right: 6px;">{{ $hora }}
                            </label>
                        @endforeach
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label for="motivo">Motivo de la consulta (opcional)</label>
                        <input id="motivo" name="motivo" type="text" value="{{ old('motivo', 'Consulta general') }}">
                    </div>

                    <div class="acciones-form" style="grid-column: 1 / -1;">
                        <button type="submit" class="boton">Confirmar cita</button>
                    </div>
                </form>
            </div>
        @endif
    @endif
@endsection