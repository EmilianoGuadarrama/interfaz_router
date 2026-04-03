@extends('layouts.dashboard')
@section('page-title', 'Configuración de LEDs')

@section('content')

<p style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
    Personaliza el comportamiento de los <a href="#" style="color:var(--primary);">LEDs</a> del dispositivo, si es posible.
</p>

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
            <button class="btn btn-main" style="border-radius:0;">GUARDAR Y APLICAR</button>
            <button class="btn btn-main" style="border-radius:0;border-left:1px solid rgba(255,255,255,.2);padding:10px 12px;"
                    onclick="document.getElementById('dd1').classList.toggle('show')">▼</button>
        </div>
        <div id="dd1" class="dropdown-menu dropdown-menu-dark"
             style="display:none;position:absolute;right:0;top:calc(100% + 4px);min-width:210px;z-index:100;">
            <button class="dropdown-item" style="color:var(--text-main);">GUARDAR Y APLICAR</button>
            <button class="dropdown-item" style="color:var(--text-main);">APLICAR SIN RESTRICCIÓN</button>
        </div>
    </div>
    <button class="btn btn-sm"
            style="background:rgba(255,255,255,.08);color:var(--text-main);border:1px solid var(--border-soft);border-radius:10px;padding:8px 18px;font-weight:600;">
        GUARDAR
    </button>
    <button class="btn btn-sm"
            style="background:#dc3545;color:white;border:none;border-radius:10px;padding:8px 18px;font-weight:700;cursor:pointer;">
        RESTABLECER
    </button>
</div>

@push('scripts')
<script>
const dd1 = document.getElementById('dd1');
document.addEventListener('click', e => {
    if (!e.target.closest('#ddWrap')) dd1.style.display = 'none';
});
dd1.addEventListener('click', () => dd1.style.display = 'none');
</script>
@endpush

@endsection