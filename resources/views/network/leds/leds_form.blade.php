@extends('layouts.dashboard')
@section('title', $led ? 'Editar LED' : 'Añadir LED')

@section('content')

<div class="form-wrap">
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

        {{-- Nombre del LED --}}
        <div class="form-row">
            <label class="form-label">Nombre del <a href="#" style="color:#60a5fa;">LED</a></label>
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

        {{-- Estado predeterminado --}}
        <div class="form-row">
            <label class="form-label">Estado predeterminado</label>
            <div>
                <input type="hidden" name="estado_predeterminado" value="0">
                <input type="checkbox" name="estado_predeterminado" value="1" class="form-check"
                       {{ old('estado_predeterminado', $led['estado'] ?? false) ? 'checked' : '' }}>
            </div>
        </div>

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
        <div class="form-row" id="fieldModo"
             style="{{ old('disparador', $led['disparador'] ?? 'defaulton') === 'netdev' ? '' : 'display:none' }}">
            <label class="form-label">Modo de disparador</label>
            <div>
                <div class="ms">
                    <div class="ms-display">
                        <span class="ms-tag" id="tagLink" style="{{ in_array('link',$modos)?'':'display:none' }}">Enlace conectado</span>
                        <span class="ms-tag" id="tagTx"   style="{{ in_array('tx',$modos)?'':'display:none' }}">Transmitir</span>
                        <span class="ms-tag" id="tagRx"   style="{{ in_array('rx',$modos)?'':'display:none' }}">Recibir</span>
                        <button type="button" class="ms-caret"
                                onclick="document.getElementById('msOpts').classList.toggle('open')">▼</button>
                    </div>
                    <div class="ms-opts" id="msOpts">
                        <label class="ms-opt">
                            <input type="checkbox" name="modo_disparador[]" value="link" id="chkLink"
                                   onchange="updateTags()" {{ in_array('link',$modos)?'checked':'' }}>
                            Enlace conectado
                        </label>
                        <label class="ms-opt">
                            <input type="checkbox" name="modo_disparador[]" value="tx" id="chkTx"
                                   onchange="updateTags()" {{ in_array('tx',$modos)?'checked':'' }}>
                            Transmitir
                        </label>
                        <label class="ms-opt">
                            <input type="checkbox" name="modo_disparador[]" value="rx" id="chkRx"
                                   onchange="updateTags()" {{ in_array('rx',$modos)?'checked':'' }}>
                            Recibir
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timer — solo timer --}}
        <div class="form-row" id="fieldTimerOn"
             style="{{ old('disparador', $led['disparador'] ?? '') === 'timer' ? '' : 'display:none' }}">
            <label class="form-label">Tiempo encendido (ms)</label>
            <div>
                <input type="number" name="timer_on" class="form-input" style="max-width:140px;"
                       value="{{ old('timer_on', $led['timer_on'] ?? 500) }}" min="0">
            </div>
        </div>
        <div class="form-row" id="fieldTimerOff"
             style="{{ old('disparador', $led['disparador'] ?? '') === 'timer' ? '' : 'display:none' }}">
            <label class="form-label">Tiempo apagado (ms)</label>
            <div>
                <input type="number" name="timer_off" class="form-input" style="max-width:140px;"
                       value="{{ old('timer_off', $led['timer_off'] ?? 500) }}" min="0">
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('leds.index') }}" class="btn btn-gray">DESCARTAR</a>
            <button type="submit" class="btn btn-blue">GUARDAR</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function onDisparador(val) {
    document.getElementById('fieldModo').style.display     = val === 'netdev' ? '' : 'none';
    document.getElementById('fieldTimerOn').style.display  = val === 'timer'  ? '' : 'none';
    document.getElementById('fieldTimerOff').style.display = val === 'timer'  ? '' : 'none';
}
function updateTags() {
    document.getElementById('tagLink').style.display = document.getElementById('chkLink').checked ? '' : 'none';
    document.getElementById('tagTx').style.display   = document.getElementById('chkTx').checked   ? '' : 'none';
    document.getElementById('tagRx').style.display   = document.getElementById('chkRx').checked   ? '' : 'none';
}
</script>
@endpush

@endsection