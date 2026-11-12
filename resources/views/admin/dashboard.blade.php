@extends('layouts.admin')

@section('title', 'Inicio')

@section('content')
    <h2>Bienvenido, {{ auth()->user()->nombre_usuario }}</h2>
    <p class="sub">Opciones disponibles según su rol</p>

    <div class="tarjetas">
        <div class="tarjeta"><strong>{{ $estadisticas['medicos'] }}</strong><span>Médicos activos</span></div>
        <div class="tarjeta"><strong>{{ $estadisticas['especialidades'] }}</strong><span>Especialidades</span></div>
        <div class="tarjeta"><strong>{{ $estadisticas['pacientes'] }}</strong><span>Pacientes activos</span></div>
        <div class="tarjeta"><strong>{{ $estadisticas['citas_hoy'] }}</strong><span>Citas para hoy</span></div>
    </div>

    <div class="panel">
        <h3>Accesos rápidos</h3>
        <div class="accesos">
            <a class="boton" href="{{ route('admin.usuarios.index') }}">Usuarios</a>
            <a class="boton" href="{{ route('admin.medicos.index') }}">Médicos</a>
            <a class="boton" href="{{ route('admin.especialidades.index') }}">Especialidades</a>
            <a class="boton" href="{{ route('admin.horarios.index') }}">Horarios de atención</a>
            <span class="boton deshabilitado">Reportes</span>
        </div>
    </div>
@endsection