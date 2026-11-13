<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

// CU-20 Generar reportes (solo administrador)
class ReporteController extends Controller
{
    public const TIPOS = [
        'POR_FECHA' => 'Citas por fecha',
        'POR_MEDICO' => 'Citas por médico',
        'POR_ESPECIALIDAD' => 'Citas por especialidad',
        'POR_ESTADO' => 'Citas por estado',
        'PACIENTES' => 'Pacientes registrados',
        'AGENDA' => 'Agenda diaria',
    ];

    public const ESTADOS = ['PROGRAMADA', 'CONFIRMADA', 'ATENDIDA', 'CANCELADA', 'INASISTENCIA'];

    public function index(Request $request)
    {
        $reporte = $this->generar($request);

        return view('admin.reportes.index', $reporte + [
            'tipos' => self::TIPOS,
            'estados' => self::ESTADOS,
            'medicos' => DB::table('medico')
                ->where('estado', true)
                ->orderBy('apellidos')->orderBy('nombres')
                ->get(['id_medico', 'nombres', 'apellidos']),
            'especialidades' => DB::table('especialidad')
                ->where('estado', true)
                ->orderBy('nombre')
                ->get(['id_especialidad', 'nombre']),
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        $reporte = $this->generar($request);
        $nombre = 'reporte_' . strtolower($reporte['tipo']) . '_' . $reporte['desde'] . '_' . $reporte['hasta'] . '.csv';

        return response()->streamDownload(function () use ($reporte) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF"); // BOM UTF-8 para que Excel muestre bien las tildes
            fputcsv($salida, $reporte['tabla']['encabezados'], ';');
            foreach ($reporte['tabla']['filas'] as $fila) {
                fputcsv($salida, $fila, ';');
            }
            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function generar(Request $request): array
    {
        $datos = $request->validate([
            'tipo' => ['nullable', 'in:' . implode(',', array_keys(self::TIPOS))],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
            'id_medico' => ['nullable', 'integer', 'exists:medico,id_medico'],
            'id_especialidad' => ['nullable', 'integer', 'exists:especialidad,id_especialidad'],
            'estado' => ['nullable', 'in:' . implode(',', self::ESTADOS)],
        ]);

        $ahora = now('America/La_Paz');
        $mesDesde = $ahora->copy()->startOfMonth()->toDateString();
        $mesHasta = $ahora->copy()->endOfMonth()->toDateString();

        $tipo = $datos['tipo'] ?? 'POR_FECHA';
        $desde = $datos['desde'] ?? $mesDesde;
        $hasta = $datos['hasta'] ?? $mesHasta;
        $error = null;

        // Excepción: rango inválido, se usa el mes actual
        if ($desde > $hasta) {
            $error = 'El rango es inválido: «Desde» es posterior a «Hasta». Se usa el mes actual.';
            $desde = $mesDesde;
            $hasta = $mesHasta;
        }

        $f = [
            'id_medico' => $datos['id_medico'] ?? null,
            'id_especialidad' => $datos['id_especialidad'] ?? null,
            'estado' => $datos['estado'] ?? null,
        ];

        if ($tipo === 'PACIENTES') {
            $tabla = $this->tablaPacientes($desde, $hasta);
            $totales = $this->totalesPacientes($desde, $hasta);
        } else {
            $tabla = match ($tipo) {
                'POR_FECHA' => $this->tablaPorFecha($desde, $hasta, $f),
                'POR_MEDICO' => $this->tablaPorMedico($desde, $hasta, $f),
                'POR_ESPECIALIDAD' => $this->tablaPorEspecialidad($desde, $hasta, $f),
                'POR_ESTADO' => $this->tablaPorEstado($desde, $hasta, $f),
                'AGENDA' => $this->tablaAgenda($desde, $hasta, $f),
            };
            $totales = $this->totalesCitas($desde, $hasta, $f);
        }

        return [
            'tipo' => $tipo,
            'desde' => $desde,
            'hasta' => $hasta,
            'filtros' => $f,
            'error' => $error,
            'totales' => $totales,
            'tabla' => $tabla,
            'vacio' => empty($tabla['filas']),
        ];
    }

    private function citas(string $desde, string $hasta, array $f)
    {
        $q = DB::table('cita as c')->whereBetween('c.fecha', [$desde, $hasta]);

        if ($f['id_medico']) {
            $q->where('c.id_medico', $f['id_medico']);
        }
        if ($f['id_especialidad']) {
            $q->where('c.id_especialidad', $f['id_especialidad']);
        }
        if ($f['estado']) {
            $q->where('c.estado', $f['estado']);
        }

        return $q;
    }

    private static function conteos(): string
    {
        return "COUNT(*) AS total,
            COUNT(*) FILTER (WHERE c.estado = 'ATENDIDA') AS atendidas,
            COUNT(*) FILTER (WHERE c.estado = 'CANCELADA') AS canceladas,
            COUNT(*) FILTER (WHERE c.estado = 'INASISTENCIA') AS inasistencias,
            COUNT(*) FILTER (WHERE c.estado IN ('PROGRAMADA', 'CONFIRMADA')) AS pendientes";
    }

    private function agrupar($q, string $etiqueta, string $grupo): array
    {
        return $q->selectRaw("$etiqueta AS nombre_grupo")
            ->selectRaw(self::conteos())
            ->groupByRaw($grupo)
            ->orderBy('nombre_grupo')
            ->get()
            ->map(fn ($r) => [
                $r->nombre_grupo,
                (int) $r->total,
                (int) $r->atendidas,
                (int) $r->canceladas,
                (int) $r->inasistencias,
                (int) $r->pendientes,
            ])
            ->all();
    }

    private function tablaPorFecha(string $desde, string $hasta, array $f): array
    {
        $filas = collect($this->agrupar($this->citas($desde, $hasta, $f), 'c.fecha', 'c.fecha'))
            ->map(fn ($r) => [Carbon::parse($r[0])->format('d/m/Y'), ...array_slice($r, 1)])
            ->all();

        return [
            'encabezados' => ['Fecha', 'Total', 'Atendidas', 'Canceladas', 'Inasistencias', 'Pendientes'],
            'filas' => $filas,
        ];
    }

    private function tablaPorMedico(string $desde, string $hasta, array $f): array
    {
        $q = $this->citas($desde, $hasta, $f)
            ->join('medico as m', 'm.id_medico', '=', 'c.id_medico');

        return [
            'encabezados' => ['Médico', 'Total', 'Atendidas', 'Canceladas', 'Inasistencias', 'Pendientes'],
            'filas' => $this->agrupar($q, "m.apellidos || ' ' || m.nombres", 'c.id_medico, m.apellidos, m.nombres'),
        ];
    }

    private function tablaPorEspecialidad(string $desde, string $hasta, array $f): array
    {
        $q = $this->citas($desde, $hasta, $f)
            ->join('especialidad as e', 'e.id_especialidad', '=', 'c.id_especialidad');

        return [
            'encabezados' => ['Especialidad', 'Total', 'Atendidas', 'Canceladas', 'Inasistencias', 'Pendientes'],
            'filas' => $this->agrupar($q, 'e.nombre', 'c.id_especialidad, e.nombre'),
        ];
    }

    private function tablaPorEstado(string $desde, string $hasta, array $f): array
    {
        $grupos = $this->agrupar($this->citas($desde, $hasta, $f), 'c.estado', 'c.estado');
        $suma = array_sum(array_column($grupos, 1));

        $filas = array_map(fn ($r) => [
            $r[0],
            $r[1],
            $suma ? round($r[1] * 100 / $suma, 1) . ' %' : '0 %',
        ], $grupos);

        return [
            'encabezados' => ['Estado', 'Total', '% del total'],
            'filas' => $filas,
        ];
    }

    private function tablaAgenda(string $desde, string $hasta, array $f): array
    {
        $filas = $this->citas($desde, $hasta, $f)
            ->join('paciente as p', 'p.id_paciente', '=', 'c.id_paciente')
            ->join('medico as m', 'm.id_medico', '=', 'c.id_medico')
            ->join('especialidad as e', 'e.id_especialidad', '=', 'c.id_especialidad')
            ->orderBy('c.fecha')
            ->orderBy('c.hora_inicio')
            ->get([
                'c.fecha', 'c.hora_inicio', 'c.codigo', 'c.estado',
                DB::raw("p.apellidos || ' ' || p.nombres AS paciente"),
                DB::raw("m.apellidos || ' ' || m.nombres AS medico"),
                'e.nombre as especialidad',
            ])
            ->map(fn ($c) => [
                Carbon::parse($c->fecha)->format('d/m/Y'),
                substr($c->hora_inicio, 0, 5),
                $c->codigo,
                $c->paciente,
                $c->medico,
                $c->especialidad,
                $c->estado,
            ])
            ->all();

        return [
            'encabezados' => ['Fecha', 'Hora', 'Código', 'Paciente', 'Médico', 'Especialidad', 'Estado'],
            'filas' => $filas,
        ];
    }

    private function tablaPacientes(string $desde, string $hasta): array
    {
        $filas = DB::table('paciente as p')
            ->whereRaw('p.fecha_creacion::date BETWEEN ? AND ?', [$desde, $hasta])
            ->orderBy('p.fecha_creacion')
            ->get([
                'p.codigo', 'p.ci', 'p.fecha_creacion',
                DB::raw("p.apellidos || ' ' || p.nombres AS paciente"),
                DB::raw("CASE WHEN p.estado THEN 'Activo' ELSE 'Inactivo' END AS estado_texto"),
            ])
            ->map(fn ($p) => [
                $p->codigo,
                $p->ci,
                $p->paciente,
                Carbon::parse($p->fecha_creacion)->format('d/m/Y'),
                $p->estado_texto,
            ])
            ->all();

        return [
            'encabezados' => ['Código', 'CI', 'Paciente', 'Fecha de registro', 'Estado'],
            'filas' => $filas,
        ];
    }

    private function totalesCitas(string $desde, string $hasta, array $f): array
    {
        $c = $this->citas($desde, $hasta, $f)->selectRaw(self::conteos())->first();
        $denominador = (int) $c->atendidas + (int) $c->inasistencias;

        return [
            'Total de citas' => (int) $c->total,
            'Atendidas' => (int) $c->atendidas,
            'Canceladas' => (int) $c->canceladas,
            'Inasistencias' => (int) $c->inasistencias,
            'Pendientes' => (int) $c->pendientes,
            '% de asistencia' => $denominador ? round($c->atendidas * 100 / $denominador, 1) . ' %' : '—',
        ];
    }

    private function totalesPacientes(string $desde, string $hasta): array
    {
        $q = DB::table('paciente as p')
            ->whereRaw('p.fecha_creacion::date BETWEEN ? AND ?', [$desde, $hasta]);

        return [
            'Pacientes registrados' => (clone $q)->count(),
            'Activos' => (clone $q)->where('p.estado', true)->count(),
        ];
    }
}