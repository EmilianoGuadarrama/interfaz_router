<div class="row align-items-start mb-4">
    <div class="col-md-3 text-md-end pt-2">
        <label class="form-label mb-0" style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Crear /
            Asignar zona de cortafuegos</label>
    </div>
    <div class="col-md-9">
        <select name="firewall_zone" id="editIfaceFirewallZone" class="form-select w-50">
            <option value="">Sin especificar</option>
            @if(isset($uciFirewallZones))
                @foreach($uciFirewallZones as $zone)
                    <option value="{{ $zone['name'] }}">
                        Zona: {{ $zone['name'] }}
                    </option>
                @endforeach
            @endif
            <option value="custom">-- Personalizada --</option>
        </select>
        <input type="text" name="custom_firewall_zone" id="editIfaceFirewallZoneCustom"
            class="form-control w-50 mt-2 d-none" placeholder="Nombre de la nueva zona">
        <small class="d-block mt-2" style="color: #e2eaff; font-size: 0.75rem;">
            Elija la zona del cortafuegos a la que quiere asignar esta interfaz o seleccione <em>Sin especificar</em>
            para remover la interfaz de la zona actual.
        </small>
    </div>
</div>