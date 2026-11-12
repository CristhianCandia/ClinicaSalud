@extends('layouts.recepcionista')

@section('title', 'Registrar Pago')
@section('page-title', 'Registrar nuevo pago')

@section('content')
    <div style="background: white; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto;">
        <h3 style="color: #2c3e50; font-size: 18px; margin-bottom: 20px; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px;">
            Datos del Pago
        </h3>

        <form method="POST" action="{{ route('recepcionista.pagos.store') }}">
            @csrf

            {{-- BLOQUE DE ERRORES DE VALIDACIÓN --}}
            @if($errors->any())
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    <strong>Hay errores en el formulario:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{-- FIN BLOQUE DE ERRORES --}}

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 600;">
                        Paciente <span style="color: #e74c3c;">*</span>
                    </label>
                    <select name="paciente_id" id="paciente_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">Seleccione un paciente...</option>
                        @foreach($pacientes as $p)
                            <option value="{{ $p->id }}">{{ $p->ci }} - {{ $p->nombres }} {{ $p->apellidos }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 600;">
                        Tipo de Servicio <span style="color: #e74c3c;">*</span>
                    </label>
                    <select name="tipo_servicio" id="tipo_servicio" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="CITA">Consulta Médica (Cita)</option>
                        <option value="ESTUDIO">Estudio de Laboratorio/Imagen</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 600;">
                    ID del Servicio (N° de Cita o Estudio) <span style="color: #e74c3c;">*</span>
                </label>
                <input type="number" name="servicio_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Ej: 1">
                <small style="color: #7f8c8d; font-size: 12px;">Ingrese el ID interno de la cita o estudio que se está pagando.</small>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 600;">
                        Monto Total del Servicio (Bs) <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="number" step="0.01" name="monto_total" id="monto_total" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" placeholder="0.00">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 600;">
                        Monto Pagado (Bs) <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="number" step="0.01" name="monto_pagado" id="monto_pagado" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" placeholder="0.00">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 600;">
                        Método de Pago <span style="color: #e74c3c;">*</span>
                    </label>
                    <select name="metodo_pago" id="metodo_pago" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="EFECTIVO">Efectivo</option>
                        <option value="TARJETA">Tarjeta (Crédito/Débito)</option>
                        <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                    </select>
                </div>
                <div id="div_referencia" style="display: none;">
                    <label style="display: block; margin-bottom: 5px; color: #34495e; font-size: 13px; font-weight: 600;">
                        N° de Referencia / Autorización <span style="color: #e74c3c;">*</span>
                    </label>
                    <input type="text" name="numero_referencia" id="numero_referencia" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Ej: 123456 o REF-999">
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px; font-size: 15px;">
                    💾 Registrar Pago
                </button>
                <a href="{{ route('recepcionista.pagos.index') }}" class="btn btn-secondary" style="padding: 12px 24px; font-size: 15px;">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    @section('scripts')
    <script>
        // Mostrar/ocultar campo de referencia según el método de pago
        document.getElementById('metodo_pago').addEventListener('change', function() {
            const divRef = document.getElementById('div_referencia');
            const inputRef = document.getElementById('numero_referencia');
            
            if (this.value === 'TARJETA' || this.value === 'TRANSFERENCIA') {
                divRef.style.display = 'block';
                inputRef.setAttribute('required', 'required');
            } else {
                divRef.style.display = 'none';
                inputRef.removeAttribute('required');
                inputRef.value = '';
            }
        });
    </script>
    @endsection
@endsection