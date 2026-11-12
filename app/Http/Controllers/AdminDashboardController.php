<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Paciente;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $hoy = now('America/La_Paz')->toDateString();

        $estadisticas = [
            'medicos' => Medico::where('estado', true)->count(),
            'especialidades' => Especialidad::where('estado', true)->count(),
            'pacientes' => Paciente::where('estado', true)->count(),
            'citas_hoy' => Cita::whereDate('fecha', $hoy)
                ->where('estado', '!=', 'CANCELADA')
                ->count(),
        ];

        return view('admin.dashboard', compact('estadisticas'));
    }
}