<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('pagos')
            ->select('pagos.*', 'pacientes.nombres', 'pacientes.apellidos', 'usuarios.nombre_usuario as recepcionista')
            ->join('pacientes', 'pagos.paciente_id', '=', 'pacientes.id')
            ->join('usuarios', 'pagos.usuario_id', '=', 'usuarios.id')
            ->orderBy('pagos.fecha_pago', 'desc');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('pagos.comprobante', 'like', "%{$buscar}%")
                  ->orWhere('pacientes.ci', 'like', "%{$buscar}%")
                  ->orWhere('pacientes.nombres', 'like', "%{$buscar}%")
                  ->orWhere('pacientes.apellidos', 'like', "%{$buscar}%");
            });
        }

        $pagos = $query->paginate(15);
        return view('recepcionista.pagos.index', compact('pagos'));
    }

    public function create()
    {
        $pacientes = DB::table('pacientes')->where('estado', true)->orderBy('nombres')->get();
        return view('recepcionista.pagos.create', compact('pacientes'));
    }

    public function store(Request $request)
    {
        // Validación corregida: numero_referencia es nullable por defecto
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'tipo_servicio' => 'required|in:CITA,ESTUDIO',
            'servicio_id' => 'required|integer',
            'monto_total' => 'required|numeric|min:0.01',
            'monto_pagado' => 'required|numeric|min:0.01|lte:monto_total',
            'metodo_pago' => 'required|in:EFECTIVO,TARJETA,TRANSFERENCIA',
            'numero_referencia' => 'nullable|string|max:50', 
        ], [
            'monto_pagado.lte' => 'El monto pagado no puede ser mayor al monto total.',
        ]);

        // Validación manual: Si es Tarjeta o Transferencia, la referencia SÍ es obligatoria
        if (in_array($request->metodo_pago, ['TARJETA', 'TRANSFERENCIA']) && empty(trim($request->numero_referencia))) {
            return back()
                ->withInput()
                ->withErrors(['numero_referencia' => 'El número de referencia/autorización es obligatorio para Tarjetas o Transferencias.']);
        }

        DB::transaction(function () use ($request) {
            // 1. Registrar el pago
            $pagoId = DB::table('pagos')->insertGetId([
                'paciente_id' => $request->paciente_id,
                'cita_id' => $request->tipo_servicio === 'CITA' ? $request->servicio_id : null,
                'estudio_id' => $request->tipo_servicio === 'ESTUDIO' ? $request->servicio_id : null,
                'monto_total' => $request->monto_total,
                'monto_pagado' => $request->monto_pagado,
                'metodo_pago' => $request->metodo_pago,
                'numero_referencia' => !empty($request->numero_referencia) ? trim($request->numero_referencia) : null,
                'usuario_id' => auth()->id(),
                'estado' => $request->monto_pagado == $request->monto_total ? 'PAGADO' : 'PARCIAL',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Actualizar el estado del servicio (opcional, dependiendo de tu flujo)
            if ($request->tipo_servicio === 'CITA') {
                DB::table('citas')->where('id', $request->servicio_id)->update(['estado' => 'PAGADA']);
            } elseif ($request->tipo_servicio === 'ESTUDIO') {
                DB::table('estudios_solicitados')->where('id', $request->servicio_id)->update(['estado' => 'PAGADO']);
            }
        });

        return redirect()->route('recepcionista.pagos.index')
            ->with('exito', 'Pago registrado correctamente.');
    }
}