@extends('layouts.app')
@section('title', 'Configuración de LEDs')

@section('content')

<p style="font-size:13px; color:var(--t3); margin-bottom:18px;">
    Personaliza el comportamiento de los <a href="#" style="color:#60a5fa;">LEDs</a> del dispositivo, si es posible.
</p>

<div class="card">
    @if(count($leds) > 0)
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Nombre del <a href="#" style="color:#60a5fa;">LED</a></th>
                    <th>Estado predeterminado</th>
                    <th>Disparador</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($leds as $led)
                <tr>
                    <td>{{ $led['nombre'] }}</td>
                    <td><span class="mono">{{ $led['led_name'] }}</span></td>
                    <td>{{ $led['estado'] }}</td>
                    <td>{{ $led['disparador'] }}</td>
                    <td>
                        <div class="td-actions">
                            <a href="{{ route('leds.edit', $led['key']) }}" class="btn btn-blue btn-sm">EDITAR</a>
                            <form action="{{ route('leds.destroy', $led['key']) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar este LED?')">
                                @csrf
                                <button type="submit" class="btn btn-red btn-sm">ELIMINAR</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">
            <div class="empty-icon"></div>
            <p class="empty-text">No hay LEDs configurados.</p>
        </div>
    @endif

    <div style="padding:14px 18px;">
        <a href="{{ route('leds.create') }}" class="btn btn-blue btn-sm">AÑADIR ACCIÓN LED</a>
    </div>
</div>

<div class="bottom-bar">
    <div class="dd-wrap">
        <div class="split">
            <button class="split-main">GUARDAR Y APLICAR</button>
            <button class="split-caret" type="button"
                    onclick="document.getElementById('dd1').classList.toggle('open')">▼</button>
        </div>
        <div class="dd-menu" id="dd1">
            <button class="dd-item">GUARDAR Y APLICAR</button>
            <button class="dd-item">APLICAR SIN RESTRICCIÓN</button>
        </div>
    </div>
    <button class="btn btn-gray">GUARDAR</button>
    <button class="btn btn-red">RESTABLECER</button>
</div>

@endsection