@extends('layouts.dashboard')
@section('page-title', $led ? 'Editar LED' : 'Añadir LED')

@section('content')

@php
    $selectedLedName    = old('led_name',   $led['led_name']   ?? ($ledNames[0] ?? ''));
    $selectedDisparador = old('disparador', $led['disparador'] ?? 'defaulton');
@endphp

<div class="panel-card" style="max-width:680px;">

    <form id="led-form"
          action="{{ $led ? route('leds.update', $led['key']) : route('leds.store') }}"
          method="POST">
        @csrf

        {{-- Nombre --}}
        <div class="form-row-custom">
            <label class="form-label-custom">Nombre</label>
            <input type="text" name="nombre" class="form-control"
                   value="{{ old('nombre', $led['nombre'] ?? '') }}">
            @error('nombre') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <hr style="border-color:var(--border-soft); margin:0;">

        {{-- Nombre del LED --}}
        <div class="form-row-custom">
            <label class="form-label-custom">Nombre del <span style="color:var(--primary);">LED</span></label>
            <div style="position:relative;">
                <input type="hidden" name="led_name" id="led_name_val" value="{{ $selectedLedName }}">
                <button type="button" class="custom-select-btn" onclick="toggleDd('ddLed')">
                    <span id="ledLabel">{{ $selectedLedName }}</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div id="ddLed" class="custom-dd">
                    @foreach($ledNames as $opt)
                        <div class="custom-dd-opt {{ $opt === $selectedLedName ? 'active' : '' }}"
                             onclick="pick('led_name_val','ledLabel','ddLed','{{ $opt }}')">
                            {{ $opt }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <hr style="border-color:var(--border-soft); margin:0;">

        {{-- Estado predeterminado --}}
        <div class="form-row-custom">
            <label class="form-label-custom">Estado predeterminado</label>
            <div style="padding-top:4px;">
                <input type="hidden" name="estado_predeterminado" value="0">
                <input type="checkbox" name="estado_predeterminado" value="1"
                       class="form-check-input"
                       {{ old('estado_predeterminado', ($led['estado'] ?? false) === true) ? 'checked' : '' }}>
            </div>
        </div>

        <hr style="border-color:var(--border-soft); margin:0;">

        {{-- Disparador --}}
        <div class="form-row-custom">
            <label class="form-label-custom">Disparador</label>
            <div style="position:relative;">
                <input type="hidden" name="disparador" id="disparador_val" value="{{ $selectedDisparador }}">
                <button type="button" class="custom-select-btn" onclick="toggleDd('ddDisp')">
                    <span id="dispLabel">{{ $selectedDisparador }}</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div id="ddDisp" class="custom-dd">
                    @foreach($disparadores as $opt)
                        <div class="custom-dd-opt {{ $opt === $selectedDisparador ? 'active' : '' }}"
                             onclick="pickDisparador('{{ $opt }}')">
                            {{ $opt }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Netdev --}}
        <div id="netdevFields" style="display:none;">
            <hr style="border-color:var(--border-soft); margin:0;">
            <div class="form-row-custom">
                <label class="form-label-custom">Modo de disparador</label>
                <div style="display:flex; flex-direction:column; gap:10px; padding-top:4px;">
                    @php $modos = old('modo_disparador', $led['modo'] ?? []); @endphp
                    <label style="display:flex;align-items:center;gap:8px;color:var(--text-soft);">
                        <input type="checkbox" class="form-check-input" name="modo_disparador[]" value="link"
                               {{ in_array('link',$modos)?'checked':'' }}> Enlace conectado
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;color:var(--text-soft);">
                        <input type="checkbox" class="form-check-input" name="modo_disparador[]" value="tx"
                               {{ in_array('tx',$modos)?'checked':'' }}> Transmitir
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;color:var(--text-soft);">
                        <input type="checkbox" class="form-check-input" name="modo_disparador[]" value="rx"
                               {{ in_array('rx',$modos)?'checked':'' }}> Recibir
                    </label>
                </div>
            </div>
        </div>

        {{-- Timer --}}
        <div id="timerFields" style="display:none;">
            <hr style="border-color:var(--border-soft); margin:0;">
            <div class="form-row-custom">
                <label class="form-label-custom">Tiempo encendido (ms)</label>
                <input type="number" name="timer_on" class="form-control" style="max-width:160px;"
                       value="{{ old('timer_on', $led['timer_on'] ?? 500) }}" min="0">
            </div>
            <hr style="border-color:var(--border-soft); margin:0;">
            <div class="form-row-custom">
                <label class="form-label-custom">Tiempo apagado (ms)</label>
                <input type="number" name="timer_off" class="form-control" style="max-width:160px;"
                       value="{{ old('timer_off', $led['timer_off'] ?? 500) }}" min="0">
            </div>
        </div>

    </form>
</div>

{{-- Botones --}}
<div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
    <a href="{{ route('leds.index') }}" class="btn btn-sm"
       style="background:rgba(255,255,255,.08);color:var(--text-main);border:1px solid var(--border-soft);border-radius:10px;padding:8px 20px;font-weight:600;">
        DESCARTAR
    </a>
    <button onclick="document.getElementById('led-form').submit()" class="btn btn-main">
        GUARDAR
    </button>
</div>

<style>
.form-row-custom {
    display: grid;
    grid-template-columns: 220px 1fr;
    align-items: start;
    gap: 16px;
    padding: 18px 24px;
}
.form-label-custom {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--text-soft);
    padding-top: 8px;
}
.custom-select-btn {
    width: 100%;
    padding: 9px 14px;
    background: rgba(255,255,255,.06);
    color: var(--text-main);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 14px;
    text-align: left;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    font-size: 13.5px;
    transition: border-color .15s;
}
.custom-select-btn:hover { border-color: var(--primary); }
.custom-dd {
    display: none;
    position: absolute;
    top: calc(100% + 4px);
    left: 0; right: 0;
    background: #1a2744;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 14px;
    z-index: 100;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0,0,0,.4);
}
.custom-dd-opt {
    padding: 10px 16px;
    font-size: 13.5px;
    color: var(--text-soft);
    cursor: pointer;
    transition: background .15s;
}
.custom-dd-opt:hover, .custom-dd-opt.active { background: var(--primary); color: #fff; }
</style>

<script>
function toggleDd(id) {
    document.querySelectorAll('.custom-dd').forEach(d => d.style.display = 'none');
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}
function pick(input, label, dd, val) {
    document.getElementById(input).value = val;
    document.getElementById(label).innerText = val;
    document.getElementById(dd).style.display = 'none';
}
function pickDisparador(val) {
    document.getElementById('disparador_val').value = val;
    document.getElementById('dispLabel').innerText = val;
    document.getElementById('ddDisp').style.display = 'none';
    activarCampos(val);
}
function activarCampos(val) {
    document.getElementById('netdevFields').style.display = val === 'netdev' ? 'block' : 'none';
    document.getElementById('timerFields').style.display  = val === 'timer'  ? 'block' : 'none';
}
document.addEventListener('click', e => {
    if (!e.target.closest('.custom-select-btn') && !e.target.closest('.custom-dd'))
        document.querySelectorAll('.custom-dd').forEach(d => d.style.display = 'none');
});
document.addEventListener('DOMContentLoaded', () => {
    activarCampos('{{ $selectedDisparador }}');
});
</script>

@endsection