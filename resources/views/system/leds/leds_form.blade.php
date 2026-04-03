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
            <label class="form-label">Nombre del LED</label>
            <div style="position:relative; min-width:220px;">

                <input type="hidden" name="led_name" id="led_name_val" value="{{ $selectedLedName }}">

                <button type="button" class="custom-select-btn" id="ledNameBtn"
                        onclick="toggleDd('ddLedName','ledNameBtn')">
                    <span id="ledNameLabel">{{ $selectedLedName }}</span>
                    <span>▼</span>
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
        </div>

        <div class="row-divider"></div>

        <div class="form-row">
            <label class="form-label">Estado predeterminado</label>
            <div>
                <input type="hidden" name="estado_predeterminado" value="0">
                <input type="checkbox" name="estado_predeterminado" value="1"
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
                    <span>▼</span>
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
        </div>

        <div class="row-divider" id="dividerModo"
             style="{{ $selectedDisparador === 'netdev' ? '' : 'display:none' }}"></div>

        <div class="form-row" id="fieldModo"
             style="{{ $selectedDisparador === 'netdev' ? '' : 'display:none' }}">
            <label class="form-label">Modo de disparador</label>
            <div style="position:relative;">
                <button type="button" class="custom-select-btn" id="modoBtn"
                        onclick="toggleDd('msOpts','modoBtn')">
                    Seleccionar ▼
                </button>

                <div class="custom-select-dd" id="msOpts" style="display:none;">
                    <label class="cs-opt">
                        <input type="checkbox" name="modo_disparador[]" value="link" id="chkLink"
                               onchange="updateTags()" {{ in_array('link',$modos) ? 'checked' : '' }}>
                        Enlace conectado
                    </label>
                    <label class="cs-opt">
                        <input type="checkbox" name="modo_disparador[]" value="tx" id="chkTx"
                               onchange="updateTags()" {{ in_array('tx',$modos) ? 'checked' : '' }}>
                        Transmitir
                    </label>
                    <label class="cs-opt">
                        <input type="checkbox" name="modo_disparador[]" value="rx" id="chkRx"
                               onchange="updateTags()" {{ in_array('rx',$modos) ? 'checked' : '' }}>
                        Recibir
                    </label>
                </div>
            </div>
        </div>

    </form>

</div>

<style>
.custom-select-btn {
    width: 100%;
    padding: 8px 12px;
    background: #1e2b4d;
    color: #fff;
    border: 1px solid #2f4ea2;
    border-radius: 8px;
    cursor: pointer;
}
.custom-select-dd {
    position: absolute;
    background: #1e2b4d;
    border: 1px solid #2f4ea2;
    border-radius: 8px;
    width: 100%;
    z-index: 200;
}
.cs-opt {
    padding: 8px;
    cursor: pointer;
}
.cs-opt:hover {
    background: rgba(255,255,255,.1);
}
.cs-opt-active {
    background: #3b82f6;
}
</style>

<script>
function toggleDd(ddId) {
    const dd = document.getElementById(ddId);
    dd.style.display = dd.style.display === 'none' ? '' : 'none';
}

function pickOption(inputId, labelId, ddId, val) {
    document.getElementById(inputId).value = val;
    document.getElementById(labelId).textContent = val;
    document.getElementById(ddId).style.display = 'none';
}

function pickDisparador(val) {
    document.getElementById('disparador_val').value = val;
    document.getElementById('disparadorLabel').textContent = val;
}

function updateTags() {}

document.addEventListener('click', e => {
    if (!e.target.closest('.custom-select-btn')) {
        document.querySelectorAll('.custom-select-dd').forEach(d => d.style.display = 'none');
    }
});
</script>

@endsection