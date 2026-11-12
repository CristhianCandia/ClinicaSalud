<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Obtenemos las estadísticas directamente de la base de datos
        // para evitar errores de modelos que aún no hemos creado
        $estadisticas = [
            'medicos' => DB::table('medicos')->where('estado', true)->count(),
            'especialidades' => DB::table('especialidades')->where('estado', true)->count(),
            'pacientes' => DB::table('pacientes')->where('estado', true)->count(),
            'citas_hoy' => DB::table('citas')->whereDate('fecha', today())->count(),
        ];

        return view('admin.dashboard', compact('estadisticas'));
    }
}