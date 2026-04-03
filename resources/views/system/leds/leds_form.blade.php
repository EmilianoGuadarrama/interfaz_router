@extends('layouts.dashboard') 
@section('page-title', $led ? 'Editar LED' : 'Añadir LED')

@section('content')

<p style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
    Configura el comportamiento del <span style="color:var(--primary);">LED</span>.
</p>

@php
    $selectedLedName    = old('led_name',   $led['led_name']   ?? ($ledNames[0] ?? ''));
    $selectedDisparador = old('disparador', $led['disparador'] ?? 'defaulton');
@endphp

<div class="panel-card">

<form id="led-form"
      action="{{ $led ? route('leds.update', $led['key']) : route('leds.store') }}"
      method="POST">
    @csrf
    @if($led) @method('PUT') @endif

    <!-- Nombre -->
    <div class="form-row">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-input"
               value="{{ old('nombre', $led['nombre'] ?? '') }}">
    </div>

    <div class="row-divider"></div>

    <!-- LED -->
    <div class="form-row">
        <label class="form-label">Nombre del LED</label>

        <input type="hidden" name="led_name" id="led_name_val" value="{{ $selectedLedName }}">

        <div class="custom-select">
            <button type="button" onclick="toggleDd('ddLed')"
                class="custom-select-btn">
                <span id="ledLabel">{{ $selectedLedName }}</span> ▼
            </button>

            <div id="ddLed" class="custom-select-dd">
                @foreach($ledNames as $opt)
                    <div class="cs-opt"
                         onclick="pick('led_name_val','ledLabel','ddLed','{{ $opt }}')">
                        {{ $opt }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row-divider"></div>

    <!-- Estado -->
    <div class="form-row">
        <label class="form-label">Estado predeterminado</label>
        <input type="checkbox" name="estado_predeterminado" value="1">
    </div>

    <div class="row-divider"></div>

    <!-- Disparador -->
    <div class="form-row">
        <label class="form-label">Disparador</label>

        <input type="hidden" name="disparador" id="disparador_val" value="{{ $selectedDisparador }}">

        <div class="custom-select">
            <button type="button" onclick="toggleDd('ddDisp')"
                class="custom-select-btn">
                <span id="dispLabel">{{ $selectedDisparador }}</span> ▼
            </button>

            <div id="ddDisp" class="custom-select-dd">
                @foreach($disparadores as $opt)
                    <div class="cs-opt"
                         onclick="pickDisparador('{{ $opt }}')">
                        {{ $opt }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- NETDEV -->
    <div id="netdevFields" style="display:none;">
        <div class="row-divider"></div>

        <div class="form-row">
            <label class="form-label">Dispositivo</label>
            <input type="text" name="device" class="form-input" placeholder="wlan0">
        </div>

        <div class="form-row">
            <label class="form-label">Modo</label>
            <label><input type="checkbox" name="modo[]" value="link"> Link</label>
            <label><input type="checkbox" name="modo[]" value="tx"> TX</label>
            <label><input type="checkbox" name="modo[]" value="rx"> RX</label>
        </div>
    </div>

    <!-- TIMER -->
    <div id="timerFields" style="display:none;">
        <div class="row-divider"></div>

        <div class="form-row">
            <label class="form-label">Tiempo ON</label>
            <input type="number" name="timer_on" class="form-input">
        </div>

        <div class="form-row">
            <label class="form-label">Tiempo OFF</label>
            <input type="number" name="timer_off" class="form-input">
        </div>
    </div>

    <!-- SWITCH -->
    <div id="switchFields" style="display:none;">
        <div class="row-divider"></div>

        <div class="form-row">
            <label class="form-label">Máscara switch</label>
            <input type="text" name="switch_mask" class="form-input">
        </div>

        <div class="form-row">
            <label class="form-label">Velocidad</label>
            <input type="text" name="speed_mask" class="form-input">
        </div>
    </div>

</form>
</div>

<!-- BOTONES -->
<div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
    
    <button onclick="document.getElementById('led-form').submit()"
        class="btn btn-main">
        GUARDAR
    </button>

    <a href="{{ route('leds.index') }}"
       class="btn"
       style="background:#dc3545;color:#fff;">
        DESCARTAR
    </a>

</div>

<style>
.custom-select{position:relative;}
.custom-select-dd{
    display:none;
    position:absolute;
    width:100%;
    background:#1e2b4d;
    border:1px solid #2f4ea2;
    border-radius:8px;
    z-index:100;
}
.custom-select-btn{
    width:100%;
    padding:8px;
    background:#1e2b4d;
    color:#fff;
    border:1px solid #2f4ea2;
    border-radius:8px;
}
.cs-opt{padding:8px;cursor:pointer;}
.cs-opt:hover{background:#3b82f6;}
</style>

<script>
function toggleDd(id){
    document.querySelectorAll('.custom-select-dd').forEach(d=>d.style.display='none');
    let el=document.getElementById(id);
    el.style.display = el.style.display==='block'?'none':'block';
}

function pick(input,label,dd,val){
    document.getElementById(input).value=val;
    document.getElementById(label).innerText=val;
    document.getElementById(dd).style.display='none';
}

function pickDisparador(val){
    document.getElementById('disparador_val').value=val;
    document.getElementById('dispLabel').innerText=val;
    activarCampos(val);
}

function activarCampos(val){
    document.getElementById('netdevFields').style.display='none';
    document.getElementById('timerFields').style.display='none';
    document.getElementById('switchFields').style.display='none';

    if(val==='netdev') document.getElementById('netdevFields').style.display='block';
    if(val==='timer') document.getElementById('timerFields').style.display='block';
    if(val==='switch0') document.getElementById('switchFields').style.display='block';
}

document.addEventListener('click', e=>{
    if(!e.target.closest('.custom-select')){
        document.querySelectorAll('.custom-select-dd').forEach(d=>d.style.display='none');
    }
});

document.addEventListener('DOMContentLoaded',()=>{
    activarCampos(document.getElementById('disparador_val').value);
});
</script>

@endsection