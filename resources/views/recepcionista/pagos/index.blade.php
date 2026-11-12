@extends('layouts.recepcionista')

@section('title', 'Historial de Pagos')
@section('page-title', 'Historial de Pagos')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <form method="GET" action="{{ route('recepcionista.pagos.index') }}" style="display: flex; gap: 10px; flex: 1; max-width: 500px;">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por comprobante o paciente..." 
                   style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
        <a href="{{ route('recepcionista.pagos.create') }}" class="btn btn-primary">+ Nuevo Pago</a>
    </div>

    <div style="background: white; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #ecf0f1;">
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Comprobante</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Fecha</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Paciente</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Método</th>
                    <th style="text-align: right; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Monto Pagado</th>
                    <th style="text-align: left; padding: 12px 15px; color: #2c3e50; font-size: 13px; font-weight: 600;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pagos as $pago)
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 12px 15px; font-weight: 600; color: #2980b9;">{{ $pago->comprobante }}</td>
                        <td style="padding: 12px 15px; color: #34495e;">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y H:i') }}</td>
                        <td style="padding: 12px 15px; color: #34495e;">{{ $pago->nombres }} {{ $pago->apellidos }}</td>
                        <td style="padding: 12px 15px; color: #34495e;">
                            @if($pago->metodo_pago === 'EFECTIVO') 💵 Efectivo
                            @elseif($pago->metodo_pago === 'TARJETA') 💳 Tarjeta
                            @else 🏦 Transferencia
                            @endif
                        </td>
                        <td style="padding: 12px 15px; color: #27ae60; font-weight: bold; text-align: right;">
                            Bs {{ number_format($pago->monto_pagado, 2) }}
                        </td>
                        <td style="padding: 12px 15px;">
                            @if($pago->estado === 'PAGADO')
                                <span style="background: #d4edda; color: #155724; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">PAGADO</span>
                            @else
                                <span style="background: #fff3cd; color: #856404; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">PARCIAL</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #7f8c8d;">
                            No se encontraron pagos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top: 20px;">
            {{ $pagos->links() }}
        </div>
    </div>
@endsection