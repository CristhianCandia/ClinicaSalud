@extends('layouts.paciente')

@section('title', 'Mi perfil')

@section('content')
    <h2>Mi perfil</h2>
    <p class="sub">Datos personales y de contacto</p>

    @if ($errors->any())
        <div class="alerta" style="margin-top: 16px;">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel" style="margin-top: 20px; max-width: 820px;">
        <form method="POST" action="{{ route('paciente.perfil.update') }}" class="formulario">
            @csrf
            @method('PUT')

            <div>
                <label>CI</label>
                <input type="text" value="{{ $paciente->ci }}" disabled>
                <small class="sub">El CI no se puede cambiar. Si hay un error, acuda a recepción.</small>
            </div>

            <div>
                <label>Código de paciente</label>
                <input type="text" value="{{ $paciente->codigo }}" disabled>
            </div>

            <div>
                <label>Nombres</label>
                <input type="text" value="{{ $paciente->nombres }}" disabled>
            </div>

            <div>
                <label>Apellidos</label>
                <input type="text" value="{{ $paciente->apellidos }}" disabled>
            </div>

            <div>
                <label>Fecha de nacimiento</label>
                <input type="text"
                       value="{{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') }}" disabled>
            </div>

            <div>
                <label>Sexo</label>
                <input type="text"
                       value="{{ ['M' => 'Masculino', 'F' => 'Femenino', 'O' => 'Otro'][$paciente->sexo] ?? $paciente->sexo }}"
                       disabled>
            </div>

            <div>
                <label for="telefono">Teléfono *</label>
                <input id="telefono" name="telefono" type="text"
                       value="{{ old('telefono', $paciente->telefono) }}">
                @error('telefono') <small class="error-campo">{{ $message }}</small> @enderror
            </div>

            <div>
                <label for="correo">Correo electrónico *</label>
                <input id="correo" name="correo" type="email"
                       value="{{ old('correo', $paciente->correo) }}">
                @error('correo') <small class="error-campo">{{ $message }}</small> @enderror
            </div>

            <div style="grid-column: 1 / -1;">
                <label for="direccion">Dirección</label>
                <input id="direccion" name="direccion" type="text"
                       value="{{ old('direccion', $paciente->direccion) }}">
                @error('direccion') <small class="error-campo">{{ $message }}</small> @enderror
            </div>

            <div class="acciones-form" style="grid-column: 1 / -1;">
                <button type="submit" class="boton">Guardar</button>
            </div>
        </form>
    </div>
@endsection