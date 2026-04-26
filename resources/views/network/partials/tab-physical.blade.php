<div class="row mb-4">
    <div class="col-md-3 text-md-end pt-1">
        <label class="form-label mb-0" style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Puentear
            interfaces</label>
    </div>
    <div class="col-md-9">
        <div class="form-check m-0 mb-1">
            <input class="form-check-input" type="checkbox" name="type" id="editIfaceType" value="bridge">
        </div>
        <small style="color: var(--text-muted); font-size: 0.75rem;">Crea un puente sobre la interfaz o interfaces
            asociadas (br-lan)</small>
    </div>
</div>

<div class="row align-items-start mb-4">
    <div class="col-md-3 text-md-end pt-2">
        <label class="form-label mb-0" style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Dispositivo
            Físico</label>
    </div>
    <div class="col-md-9">
        <!-- Render a list of options using $devices from controller -->
        <select name="ifname[]" id="editIfaceIfname" class="form-select w-75" multiple style="min-height: 120px;">
            @if(isset($devices))
                @foreach($devices as $dev)
                    <option value="{{ $dev }}">{{ $dev }}</option>
                @endforeach
            @endif
            <option value="custom">-- Personalizado --</option>
        </select>
        <input type="text" name="custom_ifname" id="editIfaceIfnameCustom" class="form-control w-75 mt-2 d-none"
            placeholder="Ej: eth0.3">
        <small class="d-block mt-2" style="color: var(--text-muted); font-size: 0.75rem;">
            (Para seleccionar múltiples mantenga presionada la tecla Ctrl/Cmd)
        </small>
    </div>
</div>