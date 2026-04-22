<div class="row mb-4 align-items-start">
    <div class="col-md-3 text-md-end pt-1">
        <label class="form-label mb-0"
            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Estado</label>
    </div>
    <div class="col-md-9">
        <div class="d-inline-flex p-3"
            style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-soft); border-radius: 8px;">
            <div class="me-2 text-center">
                <i class="bi bi-diagram-3" style="font-size: 1.5rem; color: #5bc0de;"></i>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-main); line-height: 1.3;">
                <strong>Dispositivo: <span id="editIfaceDev">--</span></strong><br>
                <strong>MAC:</strong> <span id="editIfaceMac">--</span><br>
                <strong>RX:</strong> <span id="editIfaceRx">0</span> bytes<br>
                <strong>TX:</strong> <span id="editIfaceTx">0</span> bytes<br>
                <strong>IPv4:</strong> <span id="editIfaceIpv4">--</span>
            </div>
        </div>
    </div>
</div>

<div class="row align-items-center mb-4">
    <div class="col-md-3 text-md-end">
        <label class="form-label mb-0"
            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Protocolo</label>
    </div>
    <div class="col-md-9">
        <select class="form-select w-50" name="proto" id="editIfaceProto">
            <option value="dhcp">Cliente DHCP</option>
            <option value="unmanaged">No administrado</option>
            <option value="ppp">PPP</option>
            <option value="pppoe">PPPoE</option>
            <option value="static">Dirección estática</option>
        </select>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 text-md-end pt-1">
        <label class="form-label mb-0"
            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Iniciar en
            el arranque</label>
    </div>
    <div class="col-md-9">
        <div class="form-check m-0">
            <input class="form-check-input" type="checkbox" name="auto" id="editIfaceAuto" value="1">
        </div>
    </div>
</div>

<!-- ============================ -->
<!-- SECCIÓN ESTÁTICA             -->
<!-- ============================ -->
<div id="protoStaticFields" class="d-none">
    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Dirección
                IPv4</label>
        </div>
        <div class="col-md-9 gap-2">
            <div class="d-flex gap-2">
                <input type="text" name="ipaddr" id="editIfaceIpaddr"
                    class="form-control w-50"
                    value="">
                <button type="button" class="btn btn-sm"
                    style="background: rgba(255,255,255,0.1); color: var(--text-main); border: 1px solid var(--border-soft);"
                    title="Cambiar a la notación de lista CIDR">...</button>
            </div>
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Máscara de
                red IPv4</label>
        </div>
        <div class="col-md-9">
            <select name="netmask" id="editIfaceNetmask"
                class="form-select w-50" onchange="document.getElementById('editIfaceNetmaskCustom').classList.toggle('d-none', this.value !== 'custom');">
                <option value="" style="font-style: italic;">Sin especificar</option>
                <option value="255.255.255.0">255.255.255.0</option>
                <option value="255.255.0.0">255.255.0.0</option>
                <option value="255.0.0.0">255.0.0.0</option>
                <option value="custom">-- Personalizado --</option>
            </select>
            <input type="text" name="custom_netmask" id="editIfaceNetmaskCustom" class="form-control w-50 mt-2 d-none" placeholder="Ej: 255.255.255.128">
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Puerta de
                enlace IPv4</label>
        </div>
        <div class="col-md-9">
            <input type="text" name="gateway" id="editIfaceGateway"
                class="form-control w-50"
                value="">
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Difusión
                IPv4</label>
        </div>
        <div class="col-md-9">
            <input type="text" name="broadcast" id="editIfaceBroadcast" class="form-control w-50" value="">
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Servidores DNS personalizados</label>
        </div>
        <div class="col-md-9">
            <div class="d-flex w-50">
                <input type="text" name="dns" id="editIfaceDns" class="form-control form-control-sm me-2"
                    placeholder="Ej: 8.8.8.8 1.1.1.1" style="border-radius: 2px;">
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1">
            <label class="form-label mb-0" style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Longitud de asignación de IPv6</label>
        </div>
        <div class="col-md-9">
            <select name="ip6assign" id="editIfaceIp6assign" class="form-select w-50 mb-1">
                <option value="">Sin especificar</option>
                <option value="disabled">Desactivado</option>
                <option value="60">60</option>
                <option value="64">64</option>
            </select>
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end"><label class="form-label mb-0" style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Dirección IPv6</label></div>
        <div class="col-md-9"><input type="text" name="ip6addr" id="editIfaceIp6addr" class="form-control w-50"></div>
    </div>
    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end"><label class="form-label mb-0" style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Puerta de enlace IPv6</label></div>
        <div class="col-md-9"><input type="text" name="ip6gw" id="editIfaceIp6gw" class="form-control w-50"></div>
    </div>
    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1"><label class="form-label mb-0" style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Prefijo IPv6 enrutado</label></div>
        <div class="col-md-9"><input type="text" name="ip6prefix" id="editIfaceIp6prefix" class="form-control w-50 mb-1"></div>
    </div>
    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1"><label class="form-label mb-0" style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Sufijo IPv6</label></div>
        <div class="col-md-9"><input type="text" name="ip6ifaceid" id="editIfaceIp6ifaceid" class="form-control w-50 mb-1" value=""></div>
    </div>
</div>

<!-- ============================ -->
<!-- SECCIÓN DHCP CLIENT          -->
<!-- ============================ -->
<div id="protoDhcpFields" class="d-none">
    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Nombre de host a enviar</label>
        </div>
        <div class="col-md-9">
            <input type="text" name="hostname" id="editIfaceHostname" class="form-control w-50" placeholder="OpenWrt">
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Usar servidores DNS del par</label>
        </div>
        <div class="col-md-9">
            <div class="form-check m-0">
                <input class="form-check-input" type="checkbox" name="peerdns" id="editIfacePeerdns" value="1" checked>
            </div>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Ignora o usa servidores DNS dados por el servidor DHCP remoto.</small>
        </div>
    </div>
</div>

<!-- ============================ -->
<!-- SECCIÓN PPP / PPPOE          -->
<!-- ============================ -->
<div id="protoPppFields" class="d-none">
    <div id="pppoeExtra" class="d-none">
        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-md-end">
                <label class="form-label mb-0" style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Concentrador de acceso</label>
            </div>
            <div class="col-md-9">
                <input type="text" name="ac" id="editIfaceAc" class="form-control w-50" placeholder="Opcional">
            </div>
        </div>
        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-md-end">
                <label class="form-label mb-0" style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Nombre del servicio</label>
            </div>
            <div class="col-md-9">
                <input type="text" name="service" id="editIfaceService" class="form-control w-50" placeholder="Opcional">
            </div>
        </div>
    </div>

    <div id="pppExtra" class="d-none">
        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-md-end">
                <label class="form-label mb-0" style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Dispositivo de Módem</label>
            </div>
            <div class="col-md-9">
                <input type="text" name="device" id="editIfaceModemDev" class="form-control w-50" placeholder="/dev/ttyUSB0">
            </div>
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Usuario PAP/CHAP</label>
        </div>
        <div class="col-md-9">
            <input type="text" name="username" id="editIfaceUsername" class="form-control w-50">
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Contraseña PAP/CHAP</label>
        </div>
        <div class="col-md-9">
            <input type="password" name="password" id="editIfacePassword" class="form-control w-50">
        </div>
    </div>
</div>
