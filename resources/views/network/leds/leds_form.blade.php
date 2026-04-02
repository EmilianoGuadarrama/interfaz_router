@extends('layouts.dashboard')
@section('page-title', $led ? 'Editar LED' : 'Añadir LED')

@section('content')

<p style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
    Configura el comportamiento del <a href="#" style="color:var(--primary);">LED</a> del dispositivo.
</p>

<div class="panel-card">

    <form action="{{ $led ? route('leds.update', $led['key']) : route('leds.store') }}" method="POST">
        @csrf

        {{-- Nombre --}}
        <div class="form-row">
            <label class="form-label">Nombre</label>
            <div>
                <input type="text" name="nombre" class="form-input"
                       value="{{ old('nombre', $led['nombre'] ?? '') }}">
                @error('nombre') <p class="form-err">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="row-divider"></div>

        {{-- Nombre del LED --}}
        <div class="form-row">
            <label class="form-label">Nombre del <a href="#" style="color:var(--primary);">LED</a></label>
            <div class="sel-wrap">
                <select name="led_name" class="form-select">
                    @foreach($ledNames as $opt)
                        <option value="{{ $opt }}"
                            {{ old('led_name', $led['led_name'] ?? '') === $opt ? 'selected' : '' }}>
                            {{ $opt }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row-divider"></div>

        {{-- Estado predeterminado --}}
        <div class="form-row">
            <label class="form-label">Estado predeterminado</label>
            <div>
                <input type="hidden" name="estado_predeterminado" value="0">
                <input type="checkbox" name="estado_predeterminado" value="1" class="form-check"
                       {{ old('estado_predeterminado', $led['estado'] ?? false) ? 'checked' : '' }}>
            </div>
        </div>

        <div class="row-divider"></div>

        {{-- Disparador --}}
        <div class="form-row">
            <label class="form-label">Disparador</label>
            <div class="sel-wrap">
                <select name="disparador" id="disparador" class="form-select"
                        onchange="onDisparador(this.value)">
                    @foreach($disparadores as $opt)
                        <option value="{{ $opt }}"
                            {{ old('disparador', $led['disparador'] ?? 'defaulton') === $opt ? 'selected' : '' }}>
                            {{ $opt }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Modo de disparador — solo netdev --}}
        @php $modos = old('modo_disparador', $led['modo'] ?? []); @endphp

        <div class="row-divider" id="dividerModo"
             style="{{ old('disparador', $led['disparador'] ?? 'defaulton') === 'netdev' ? '' : 'display:none' }}"></div>

        <div class="form-row" id="fieldModo"
             style="{{ old('disparador', $led['disparador'] ?? 'defaulton') === 'netdev' ? '' : 'display:none' }}">
            <label class="form-label">Modo de disparador</label>
            <div>
                <div class="ms">
                    <div class="ms-display">
                        <span class="soft-badge" id="tagLink" style="{{ in_array('link',$modos)?'':'display:none' }}">Enlace conectado</span>
                        <span class="soft-badge" id="tagTx"   style="{{ in_array('tx',$modos)?'':'display:none' }}">Transmitir</span>
                        <span class="soft-badge" id="tagRx"   style="{{ in_array('rx',$modos)?'':'display:none' }}">Recibir</span>
                        <button type="button" class="btn btn-sm"
                                style="background:rgba(255,255,255,.08); color:var(--text-main); border:1px solid var(--border-soft); border-radius:8px; padding:4px 10px;"
                                onclick="document.getElementById('msOpts').classList.toggle('open')">▼</button>
                    </div>
                    <div class="ms-opts dropdown-menu dropdown-menu-dark" id="msOpts"
                         style="position:relative; top:4px; min-width:200px;">
                        <label class="dropdown-item" style="color:var(--text-main); cursor:pointer;">
                            <input type="checkbox" name="modo_disparador[]" value="link" id="chkLink"
                                   onchange="updateTags()" {{ in_array('link',$modos)?'checked':'' }}
                                   style="margin-right:8px;">
                            Enlace conectado
                        </label>
                        <label class="dropdown-item" style="color:var(--text-main); cursor:pointer;">
                            <input type="checkbox" name="modo_disparador[]" value="tx" id="chkTx"
                                   onchange="updateTags()" {{ in_array('tx',$modos)?'checked':'' }}
                                   style="margin-right:8px;">
                            Transmitir
                        </label>
                        <label class="dropdown-item" style="color:var(--text-main); cursor:pointer;">
                            <input type="checkbox" name="modo_disparador[]" value="rx" id="chkRx"
                                   onchange="updateTags()" {{ in_array('rx',$modos)?'checked':'' }}
                                   style="margin-right:8px;">
                            Recibir
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timer — solo timer --}}
        <div class="row-divider" id="dividerTimer"
             style="{{ old('disparador', $led['disparador'] ?? '') === 'timer' ? '' : 'display:none' }}"></div>

        <div class="form-row" id="fieldTimerOn"
             style="{{ old('disparador', $led['disparador'] ?? '') === 'timer' ? '' : 'display:none' }}">
            <label class="form-label">Tiempo encendido (ms)</label>
            <div>
                <input type="number" name="timer_on" class="form-input" style="max-width:140px;"
                       value="{{ old('timer_on', $led['timer_on'] ?? 500) }}" min="0">
            </div>
        </div>

        <div class="row-divider" id="dividerTimerOff"
             style="{{ old('disparador', $led['disparador'] ?? '') === 'timer' ? '' : 'display:none' }}"></div>

        <div class="form-row" id="fieldTimerOff"
             style="{{ old('disparador', $led['disparador'] ?? '') === 'timer' ? '' : 'display:none' }}">
            <label class="form-label">Tiempo apagado (ms)</label>
            <div>
                <input type="number" name="timer_off" class="form-input" style="max-width:140px;"
                       value="{{ old('timer_off', $led['timer_off'] ?? 500) }}" min="0">
            </div>
        </div>

    </form>

</div>

{{-- Bottom bar --}}
<div style="display:flex; justify-content:flex-end; align-items:center; gap:10px; padding-top:22px;">

    {{-- Guardar y Aplicar split --}}
    <div style="position:relative;">
        <div style="display:inline-flex; border-radius:14px; overflow:hidden;">
            <button form="led-form" type="submit" class="btn btn-main" style="border-radius:0;">GUARDAR Y APLICAR</button>
            <button type="button" class="btn btn-main" style="border-radius:0; border-left:1px solid rgba(255,255,255,.2); padding:10px 12px;"
                    onclick="document.getElementById('dd1').classList.toggle('show')">▼</button>
        </div>
        <div id="dd1" class="dropdown-menu dropdown-menu-dark"
             style="display:none; position:absolute; right:0; top:calc(100% + 4px); min-width:200px; z-index:100;">
            <button class="dropdown-item" style="color:var(--text-main);">GUARDAR Y APLICAR</button>
            <button class="dropdown-item" style="color:var(--text-main);">APLICAR SIN RESTRICCIÓN</button>
        </div>
    </div>

    <button form="led-form" type="submit" class="btn btn-sm"
            style="background:rgba(255,255,255,.08); color:var(--text-main); border:1px solid var(--border-soft); border-radius:10px; padding:8px 18px; font-weight:600;">
        GUARDAR
    </button>

    <a href="{{ route('leds.index') }}" class="btn btn-sm"
       style="background:#dc3545; color:white; border:none; border-radius:10px; padding:8px 18px; font-weight:600; cursor:pointer; text-decoration:none;">
        DESCARTAR
    </a>

</div>

@push('scripts')
<script>
function onDisparador(val) {
    const show = (id, visible) => {
        document.getElementById(id).style.display = visible ? '' : 'none';
    };
    show('fieldModo',     val === 'netdev');
    show('dividerModo',   val === 'netdev');
    show('fieldTimerOn',  val === 'timer');
    show('fieldTimerOff', val === 'timer');
    show('dividerTimer',  val === 'timer');
    show('dividerTimerOff', val === 'timer');
}
function updateTags() {
    document.getElementById('tagLink').style.display = document.getElementById('chkLink').checked ? '' : 'none';
    document.getElementById('tagTx').style.display   = document.getElementById('chkTx').checked   ? '' : 'none';
    document.getElementById('tagRx').style.display   = document.getElementById('chkRx').checked   ? '' : 'none';
}
document.addEventListener('click', e => {
    if (!e.target.closest('[onclick]'))
        document.querySelectorAll('.dropdown-menu').forEach(d => d.style.display = 'none');
});
</script>
@endpush

@endsection