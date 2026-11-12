@extends('layouts.paciente')

@section('title', 'Inicio')
@section('page-title', 'Bienvenido, ' . auth()->user()->nombre_usuario)

@section('content')
    <div class="card">
        <h3>Bienvenido, {{ auth()->user()->nombre_usuario }} 👋</h3>
        <p style="color: #7f8c8d;">Has iniciado sesión como <strong>{{ auth()->user()->rol->nombre }}</strong>.</p>
        <p style="color: #7f8c8d; margin-top: 10px;">Usa el menú lateral para navegar por las diferentes secciones del sistema.</p>
    </div>
@endsection