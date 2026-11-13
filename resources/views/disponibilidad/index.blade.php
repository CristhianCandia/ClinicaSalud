@extends($layout)

@section('title', 'Consultar disponibilidad')

@section('content')
    <h2>Consultar disponibilidad de citas</h2>
    <p class="sub">Seleccione la especialidad, el médico y la fecha</p>

    <div class="panel" style="margin-top: 20px;">
        <form method="GET" action="{{ route('disponibilidad.index') }}" class="buscar">
            <div>
                <label for="especialidad">Especialidad</label>
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
                <label for="medico">Médico</label>
                <select id="medico" name="medico">
                    <option value="">Seleccione...</option>
                    @foreach ($medicos as $m)
                        <option value="{{ $m->id_medico }}"
                                {{ (string) $medicoSeleccionado === (string) $m->id_medico ? 'selected' : '' }}>
                            Dr(a). {{ $m->nombres }} {{ $m->apellidos }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="fecha">Fecha</label>
                <input id="fecha" name="fecha" type="date" min="{{ $minFecha }}" value="{{ $fechaTexto }}">
            </div>

            <button type="submit" class="boton">Consultar</button>
        </form>
    </div>

    @if ($error)
        <div class="alerta" role="alert" style="margin-top: 20px;">{{ $error }}</div>
    @elseif ($fecha && $medico)
        <div class="panel" style="margin-top: 20px;">
            <h3>Horarios disponibles — {{ $fecha->format('d/m/Y') }} ({{ $dias[$fecha->dayOfWeekIso] }})</h3>

            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px;">
                @foreach ($libres as $hora)
                    <span class="boton gris">{{ $hora }}</span>
                @endforeach
            </div>
        </div>
    @endif
@endsection