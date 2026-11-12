@extends('layouts.paciente')

@section('title', 'Mi perfil')
@section('page-title', 'Mi perfil')

@section('content')
    <div class="card">
        <h3 style="color: #2c3e50; font-size: 16px; margin-bottom: 15px;">Datos personales y contacto</h3>
        <p style="color: #7f8c8d; font-size: 13px; margin-bottom: 20px;">
            Actualiza tu teléfono, correo y dirección. El CI no puede ser modificado.
        </p>

        <form method="POST" action="{{ route('paciente.perfil.update') }}">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        CI (no editable)
                    </label>
                    <input type="text" value="{{ $paciente->ci }}" disabled
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background: #ecf0f1;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Nombres
                    </label>
                    <input type="text" value="{{ $paciente->nombres }}" disabled
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background: #ecf0f1;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Apellidos
                    </label>
                    <input type="text" value="{{ $paciente->apellidos }}" disabled
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background: #ecf0f1;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Fecha de nacimiento
                    </label>
                    <input type="text" value="{{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') }}" disabled
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background: #ecf0f1;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Sexo
                    </label>
                    <input type="text" value="{{ $paciente->sexo == 'M' ? 'Masculino' : ($paciente->sexo == 'F' ? 'Femenino' : 'Otro') }}" disabled
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background: #ecf0f1;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Teléfono <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="text" name="telefono" value="{{ old('telefono', $paciente->telefono) }}" required
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    @error('telefono')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Correo electrónico <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="email" name="correo" value="{{ old('correo', $paciente->correo) }}" required
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    @error('correo')
                        <small style="color: #e74c3c; font-size: 12px;">{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 500;">
                        Dirección
                    </label>
                    <input type="text" name="direccion" value="{{ old('direccion', $paciente->direccion) }}"
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </div>
@endsection