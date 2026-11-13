@extends('layouts.admin')

@section('title', 'Reportes')

@section('content')
    <style>
        @media print {
            .menu, .no-imprimir { display: none !important; }
            .contenido { width: 100% !important; }
        }
    </style>

    <h2>Reportes</h2>
    <p class="sub no-imprimir">Reportes de citas y pacientes con filtro por rango de fechas</p>

    @if ($error)
        <div class="alerta" role="alert" style="margin-top: 16px;">{{ $error }}</div>
    @endif

    <div class="panel no-imprimir" style="margin-top: 20px;">
        <form method="GET" action="{{ route('admin.reportes.index') }}" class="formulario">
            <div>
                <label for="tipo">Tipo de reporte *</label>
                <select id="tipo" name="tipo">
                    @foreach ($tipos as $clave => $texto)
                        <option value="{{ $clave }}" {{ $tipo === $clave ? 'selected' : '' }}>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="desde">Desde *</label>
                <input id="desde" name="desde" type="date" value="{{ $desde }}">
            </div>

            <div>
                <label for="hasta">Hasta *</label>
                <input id="hasta" name="hasta" type="date" value="{{ $hasta }}">
            </div>

            <div>
                <label for="id_medico">Médico</label>
                <select id="id_medico" name="id_medico">
                    <option value="">Todos</option>
                    @foreach ($medicos as $m)
                        <option value="{{ $m->id_medico }}"
                                {{ (string) ($filtros['id_medico'] ?? '') === (string) $m->id_medico ? 'selected' : '' }}>
                            Dr(a). {{ $m->nombres }} {{ $m->apellidos }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="id_especialidad">Especialidad</label>
                <select id="id_especialidad" name="id_especialidad">
                    <option value="">Todas</option>
                    @foreach ($especialidades as $e)
                        <option value="{{ $e->id_especialidad }}"
                                {{ (string) ($filtros['id_especialidad'] ?? '') === (string) $e->id_especialidad ? 'selected' : '' }}>
                            {{ $e->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Todos</option>
                    @foreach ($estados as $est)
                        <option value="{{ $est }}" {{ ($filtros['estado'] ?? '') === $est ? 'selected' : '' }}>
                            {{ ucfirst(mb_strtolower($est)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="acciones-form" style="grid-column: 1 / -1;">
                <button type="submit" class="boton">Generar reporte</button>
                <a class="boton gris" href="{{ route('admin.reportes.csv', request()->query()) }}">Exportar CSV</a>
                <button type="button" class="boton gris" onclick="window.print()">Imprimir</button>
            </div>
        </form>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 20px;">
        @foreach ($totales as $etiqueta => $valor)
            <div class="panel" style="min-width: 160px;">
                <div class="sub">{{ $etiqueta }}</div>
                <strong style="font-size: 1.4em;">{{ $valor }}</strong>
            </div>
        @endforeach
    </div>

    <div class="panel" style="margin-top: 20px;">
        <h3>{{ $tipos[$tipo] }} — {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</h3>

        @if ($tipo === 'PACIENTES')
            <p class="sub no-imprimir">Los filtros de médico, especialidad y estado no aplican a pacientes registrados.</p>
        @endif

        @if ($vacio)
            <div class="vacio">No hay datos para el período seleccionado.</div>
        @else
            <table class="tabla" style="margin-top: 12px;">
                <thead>
                    <tr>
                        @foreach ($tabla['encabezados'] as $encabezado)
                            <th>{{ $encabezado }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tabla['filas'] as $fila)
                        <tr>
                            @foreach ($fila as $celda)
                                <td>{{ $celda }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection