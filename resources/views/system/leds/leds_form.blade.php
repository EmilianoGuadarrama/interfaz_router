@extends('layouts.dashboard')
@section('page-title', $led ? 'Editar LED' : 'Añadir LED')

@section('content')

<p style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
    Configura el comportamiento del <a href="#" style="color:var(--primary);">LED</a> del dispositivo.
</p>

@php
    $selectedLedName    = old('led_name',   $led['led_name']   ?? ($ledNames[0] ?? ''));
    $selectedDisparador = old('disparador', $led['disparador'] ?? 'defaulton');
    $modos              = old('modo_disparador', $led['modo']  ?? []);

    $estado = $led['estado'] ?? null;

    $estadoChecked = old('estado_predeterminado',
        is_bool($estado)
            ? $estado
            : ($estado === 'Encendido')
    );
@endphp

<div class="panel-card">

    <form id="led-form"
          action="{{ $led ? route('leds.update', $led['key']) : route('leds.store') }}"
          method="POST">
        @csrf
        @if($led)
            @method('PUT')
        @endif

        <div class="form-row">
            <label class="form-label">Nombre</label>
            <div>
                <input type="text" name="nombre" class="form-input"
                       value="{{ old('nombre', $led['nombre'] ?? '') }}">
                @error('nombre')
                    <p class="form-err">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="row-divider"></div>

        <div class="form-row">
            <label class="form-label">Nombre del <a href="#" style="color:var(--primary);">LED</a></label>
            <div style="position:relative; min-width:220px;">

                <input type="hidden" name="led_name" id="led_name_val" value="{{ $selectedLedName }}">

                <button type="button" class="custom-select-btn" id="ledNameBtn"
                        onclick="toggleDd('ddLedName','ledNameBtn')">
                    <span id="ledNameLabel">{{ $selectedLedName }}</span>
                    <span class="cs-arrow">▼</span>
                </button>

                <div class="custom-select-dd" id="ddLedName" style="display:none;">
                    @foreach($ledNames as $opt)
                        <div class="cs-opt {{ $opt === $selectedLedName ? 'cs-opt-active' : '' }}"
                             onclick="pickOption('led_name_val','ledNameLabel','ddLedName','{{ $opt }}')">
                            {{ $opt }}
                        </div>
                    @endforeach
                </div>

            </div>
            @error('led_name')
                <p class="form-err">{{ $message }}</p>
            @enderror
        </div>

        <div class="row-divider"></div>

        <div class="form-row">
            <label class="form-label">Estado predeterminado</label>
            <div>
                <input type="hidden" name="estado_predeterminado" value="0">
                <input type="checkbox" name="estado_predeterminado" value="1" class="form-check"
                       {{ (bool)$estadoChecked ? 'checked' : '' }}>
            </div>
        </div>

        <div class="row-divider"></div>

        <div class="form-row">
            <label class="form-label">Disparador</label>
            <div style="position:relative; min-width:220px;">

                <input type="hidden" name="disparador" id="disparador_val" value="{{ $selectedDisparador }}">

                <button type="button" class="custom-select-btn" id="disparadorBtn"
                        onclick="toggleDd('ddDisparador','disparadorBtn')">
                    <span id="disparadorLabel">{{ $selectedDisparador }}</span>
                    <span class="cs-arrow">▼</span>
                </button>

                <div class="custom-select-dd" id="ddDisparador" style="display:none;">
                    @foreach($disparadores as $opt)
                        <div class="cs-opt {{ $opt === $selectedDisparador ? 'cs-opt-active' : '' }}"
                             onclick="pickDisparador('{{ $opt }}')">
                            {{ $opt }}
                        </div>
                    @endforeach
                </div>

            </div>
            @error('disparador')
                <p class="form-err">{{ $message }}</p>
            @enderror
        </div>

        <div class="row-divider" id="dividerModo"
             style="{{ $selectedDisparador === 'netdev' ? '' : 'display:none' }}"></div>

        <div class="form-row" id="fieldModo"
             style="{{ $selectedDisparador === 'netdev' ? '' : 'display:none' }}">
            <label class="form-label">Modo de disparador</label>
            <div style="position:relative;">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:4px;">
                    <span class="soft-badge" id="tagLink" style="{{ in_array('link',$modos) ? '' : 'display:none' }}">Enlace conectado</span>
                    <span class="soft-badge" id="tagTx"   style="{{ in_array('tx',$modos)   ? '' : 'display:none' }}">Transmitir</span>
                    <span class="soft-badge" id="tagRx"   style="{{ in_array('rx',$modos)   ? '' : 'display:none' }}">Recibir</span>
                    <button type="button" class="custom-select-btn" id="modoBtn"
                            style="width:auto; padding:5px 12px;"
                            onclick="toggleDd('msOpts','modoBtn')">
                        Seleccionar <span class="cs-arrow">▼</span>
                    </button>
                </div>
                <div class="custom-select-dd" id="msOpts" style="display:none; min-width:200px;">
                    <label class="cs-opt" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="modo_disparador[]" value="link" id="chkLink"
                               onchange="updateTags()" {{ in_array('link',$modos) ? 'checked' : '' }}>
                        Enlace conectado
                    </label>
                    <label class="cs-opt" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="modo_disparador[]" value="tx" id="chkTx"
                               onchange="updateTags()" {{ in_array('tx',$modos) ? 'checked' : '' }}>
                        Transmitir
                    </label>
                    <label class="cs-opt" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="modo_disparador[]" value="rx" id="chkRx"
                               onchange="updateTags()" {{ in_array('rx',$modos) ? 'checked' : '' }}>
                        Recibir
                    </label>
                </div>
            </div>
        </div>

        <div class="row-divider" id="dividerTimerOn"
             style="{{ $selectedDisparador === 'timer' ? '' : 'display:none' }}"></div>

        <div class="form-row" id="fieldTimerOn"
             style="{{ $selectedDisparador === 'timer' ? '' : 'display:none' }}">
            <label class="form-label">Tiempo encendido (ms)</label>
            <div>
                <input type="number" name="timer_on" class="form-input" style="max-width:140px;"
                       value="{{ old('timer_on', $led['timer_on'] ?? 500) }}" min="0">
            </div>
        </div>

        <div class="row-divider" id="dividerTimerOff"
             style="{{ $selectedDisparador === 'timer' ? '' : 'display:none' }}"></div>

        <div class="form-row" id="fieldTimerOff"
             style="{{ $selectedDisparador === 'timer' ? '' : 'display:none' }}">
            <label class="form-label">Tiempo apagado (ms)</label>
            <div>
                <input type="number" name="timer_off" class="form-input" style="max-width:140px;"
                       value="{{ old('timer_off', $led['timer_off'] ?? 500) }}" min="0">
            </div>
        </div>

    </form>

</div>

<div style="display:flex; justify-content:flex-end; align-items:center; gap:10px; padding-top:22px;">

    <div style="position:relative;">
        <div style="display:inline-flex; border-radius:14px; overflow:hidden;">
            <button form="led-form" type="submit" class="btn btn-main" style="border-radius:0;">
                GUARDAR Y APLICAR
            </button>
            <button type="button" class="btn btn-main"
                    style="border-radius:0; border-left:1px solid rgba(255,255,255,.2); padding:10px 12px;"
                    onclick="toggleBottomDd()">▼</button>
        </div>
        <div id="dd1" class="dropdown-menu dropdown-menu-dark"
             style="display:none; position:absolute; right:0; top:calc(100% + 4px); min-width:200px; z-index:100;">
            <button type="button" class="dropdown-item" style="color:var(--text-main);"
                    onclick="document.getElementById('led-form').submit()">
                GUARDAR Y APLICAR
            </button>
            <button type="button" class="dropdown-item" style="color:var(--text-main);"
                    onclick="document.getElementById('led-form').submit()">
                APLICAR SIN RESTRICCIÓN
            </button>
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
function toggleDd(ddId, btnId) {
    const dd     = document.getElementById(ddId);
    const isOpen = dd.style.display !== 'none';
    closeAllDd();
    if (!isOpen) {
        dd.style.display = '';
        if (btnId) document.getElementById(btnId).classList.add('open');
    }
}

function toggleBottomDd() {
    const dd = document.getElementById('dd1');
    if (dd) dd.style.display = dd.style.display === 'none' ? '' : 'none';
}

function closeAllDd() {
    document.querySelectorAll('.custom-select-dd').forEach(d => d.style.display = 'none');
    document.querySelectorAll('.custom-select-btn').forEach(b => b.classList.remove('open'));
    const dd1 = document.getElementById('dd1');
    if (dd1) dd1.style.display = 'none';
}

document.addEventListener('click', e => {
    if (!e.target.closest('.custom-select-btn') &&
        !e.target.closest('.custom-select-dd') &&
        !e.target.closest('#dd1'))
        closeAllDd();
});

function pickOption(inputId, labelId, ddId, val) {
    document.getElementById(inputId).value       = val;
    document.getElementById(labelId).textContent = val;
    document.querySelectorAll('#' + ddId + ' .cs-opt').forEach(el => {
        el.classList.toggle('cs-opt-active', el.textContent.trim() === val);
    });
    closeAllDd();
}

function pickDisparador(val) {
    document.getElementById('disparador_val').value        = val;
    document.getElementById('disparadorLabel').textContent = val;
    document.querySelectorAll('#ddDisparador .cs-opt').forEach(el => {
        el.classList.toggle('cs-opt-active', el.textContent.trim() === val);
    });
    closeAllDd();
    onDisparador(val);
}

function onDisparador(val) {
    const show = (id, v) => {
        const el = document.getElementById(id);
        if (el) el.style.display = v ? '' : 'none';
    };
    show('dividerModo',     val === 'netdev');
    show('fieldModo',       val === 'netdev');
    show('dividerTimerOn',  val === 'timer');
    show('fieldTimerOn',    val === 'timer');
    show('dividerTimerOff', val === 'timer');
    show('fieldTimerOff',   val === 'timer');
}

function updateTags() {
    document.getElementById('tagLink').style.display = document.getElementById('chkLink').checked ? '' : 'none';
    document.getElementById('tagTx').style.display   = document.getElementById('chkTx').checked   ? '' : 'none';
    document.getElementById('tagRx').style.display   = document.getElementById('chkRx').checked   ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    const disparador = document.getElementById('disparador_val');
    if (disparador) {
        onDisparador(disparador.value);
    }
});
</script>
@endpush

@endsection