@extends('layouts.dashboard')
@section('page-title', 'Configuración de LEDs')

@section('content')

<p style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
    Personaliza el comportamiento de los <a href="#" style="color:var(--primary);">LEDs</a> del dispositivo, si es posible.
</p>

@if(session('result_title'))
    <div class="alert {{ session('result_success') ? 'alert-success' : 'alert-danger' }} mb-4">
        {{ session('result_title') }}
    </div>
@endif

<div class="panel-card">
    @if(count($leds) > 0)
        <table class="table-dark-custom">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Nombre del <span style="color:var(--primary);">LED</span></th>
                    <th>Estado predeterminado</th>
                    <th>Disparador</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($leds as $led)
                <tr>
                    <td>{{ $led['nombre'] }}</td>
                    <td><span class="soft-badge">{{ $led['led_name'] }}</span></td>
                    <td>{{ $led['estado'] }}</td>
                    <td>{{ $led['disparador'] }}</td>
                    <td>
                        <div style="display:flex; gap:8px; justify-content:flex-end; align-items:center;">
                            <a href="{{ route('leds.edit', $led['key']) }}" class="btn btn-main btn-sm">EDITAR</a>
                            <form action="{{ route('leds.destroy', $led['key']) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar este LED?')">
                                @csrf
                                <button type="submit" class="btn btn-sm"
                                        style="background:#dc3545;color:white;border:none;border-radius:10px;padding:6px 14px;font-weight:700;cursor:pointer;">
                                    ELIMINAR
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="display:flex;flex-direction:column;align-items:center;padding:52px 20px;gap:12px;">
            <i class="bi bi-inbox" style="font-size:2.5rem;color:var(--text-muted);opacity:.4;"></i>
            <p style="font-size:13.5px;color:var(--text-muted);">No hay LEDs configurados.</p>
        </div>
    @endif

    <div style="padding:16px 20px;">
        <a href="{{ route('leds.create') }}" class="btn btn-main btn-sm">+ AÑADIR ACCIÓN LED</a>
    </div>
</div>

{{-- Bottom bar --}}
<div style="display:flex;justify-content:flex-end;align-items:center;gap:10px;padding-top:22px;">

    <div style="position:relative;" id="ddWrap">
        <div style="display:inline-flex;border-radius:14px;overflow:hidden;">
            <form method="POST" action="{{ route('leds.guardar-aplicar') }}">
                @csrf
                <button type="submit" class="btn btn-main" style="border-radius:0;">
                    GUARDAR Y APLICAR
                </button>
            </form>
            <button class="btn btn-main"
                    style="border-radius:0;border-left:1px solid rgba(255,255,255,.2);padding:10px 12px;"
                    onclick="toggleDd()">▼</button>
        </div>
        <div id="dd1" class="dropdown-menu dropdown-menu-dark"
             style="display:none;position:absolute;right:0;top:calc(100% + 4px);min-width:210px;z-index:100;">
            <form method="POST" action="{{ route('leds.guardar-aplicar') }}">
                @csrf
                <button type="submit" class="dropdown-item" style="color:var(--text-main);width:100%;text-align:left;">
                    GUARDAR Y APLICAR
                </button>
            </form>
            <form method="POST" action="{{ route('leds.guardar-aplicar') }}">
                @csrf
                <button type="submit" class="dropdown-item" style="color:var(--text-main);width:100%;text-align:left;">
                    APLICAR SIN RESTRICCIÓN
                </button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('leds.guardar') }}">
        @csrf
        <button type="submit" class="btn btn-sm"
                style="background:rgba(255,255,255,.08);color:var(--text-main);border:1px solid var(--border-soft);border-radius:10px;padding:8px 18px;font-weight:600;">
            GUARDAR
        </button>
    </form>

    <form method="POST" action="{{ route('leds.restablecer') }}"
          onsubmit="return confirm('¿Restablecer la configuración de LEDs?')">
        @csrf
        <button type="submit" class="btn btn-sm"
                style="background:#dc3545;color:white;border:none;border-radius:10px;padding:8px 18px;font-weight:700;cursor:pointer;">
            RESTABLECER
        </button>
    </form>

</div>

@push('scripts')
<script>
const dd1 = document.getElementById('dd1');

function toggleDd() {
    dd1.style.display = dd1.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('click', e => {
    if (!e.target.closest('#ddWrap')) dd1.style.display = 'none';
});
</script>
@endpush

@endsection