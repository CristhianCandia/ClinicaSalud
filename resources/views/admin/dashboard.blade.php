@extends('layouts.admin')

@section('title', 'Inicio')
@section('page-title', 'Bienvenido, ' . auth()->user()->nombre_usuario)

@section('content')
    <p style="color: #7f8c8d; margin-bottom: 25px;">Opciones disponibles según su rol</p>

    <!-- TARJETAS DE ESTADÍSTICAS -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
        <!-- Médicos Activos -->
        <div class="card" style="text-align: center; padding: 25px;">
            <div style="font-size: 36px; font-weight: bold; color: #3498db; margin-bottom: 5px;">
                {{ $estadisticas['medicos'] ?? 0 }}
            </div>
            <div style="color: #7f8c8d; font-size: 14px;">Médicos activos</div>
        </div>

        <!-- Especialidades -->
        <div class="card" style="text-align: center; padding: 25px;">
            <div style="font-size: 36px; font-weight: bold; color: #9b59b6; margin-bottom: 5px;">
                {{ $estadisticas['especialidades'] ?? 0 }}
            </div>
            <div style="color: #7f8c8d; font-size: 14px;">Especialidades</div>
        </div>

        <!-- Pacientes Activos -->
        <div class="card" style="text-align: center; padding: 25px;">
            <div style="font-size: 36px; font-weight: bold; color: #2ecc71; margin-bottom: 5px;">
                {{ $estadisticas['pacientes'] ?? 0 }}
            </div>
            <div style="color: #7f8c8d; font-size: 14px;">Pacientes activos</div>
        </div>

        <!-- Citas para hoy -->
        <div class="card" style="text-align: center; padding: 25px;">
            <div style="font-size: 36px; font-weight: bold; color: #f39c12; margin-bottom: 5px;">
                {{ $estadisticas['citas_hoy'] ?? 0 }}
            </div>
            <div style="color: #7f8c8d; font-size: 14px;">Citas para hoy</div>
        </div>
    </div>

    <!-- ACCESOS RÁPIDOS -->
    <div class="card">
        <h3 style="font-size: 16px; color: #2c3e50; margin-bottom: 15px;">Accesos rápidos</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.usuarios.index') }}" class="btn" style="background: #2980b9; color: white;">
                Usuarios
            </a>
            <a href="{{ route('admin.medicos.index') }}" class="btn" style="background: #2980b9; color: white;">
                Médicos
            </a>
            <a href="{{ route('admin.especialidades.index') }}" class="btn" style="background: #2980b9; color: white;">
                Especialidades
            </a>
            <a href="{{ route('admin.horarios.index') }}" class="btn" style="background: #2980b9; color: white;">
                Horarios de atención
            </a>
            <a href="#" class="btn" style="background: #95a5a6; color: white;">
                Reportes
            </a>
        </div>
    </div>
@endsection