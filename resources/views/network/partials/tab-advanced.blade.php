<div id="protoGenericAdvancedFields">
    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Usar métrica de puerta de enlace</label>
        </div>
        <div class="col-md-9">
            <input type="text" name="metric" id="editIfaceMetric" class="form-control w-50" value="">
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Utilizar la gestión integrada de IPv6</label>
        </div>
        <div class="col-md-9">
            <div class="form-check m-0">
                <input class="form-check-input" type="checkbox" name="delegate" id="editIfaceDelegate" value="1">
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Forzar enlace</label>
        </div>
        <div class="col-md-9">
            <div class="form-check m-0 mb-1">
                <input class="form-check-input" type="checkbox" name="force_link" id="editIfaceForceLink" value="1">
            </div>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Configura las propiedades de la interfaz independientemente del operador de enlace.</small>
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Reemplazar dirección MAC</label>
        </div>
        <div class="col-md-9">
            <input type="text" name="macaddr" id="editIfaceMacaddr"
                class="form-control w-50" placeholder="Ej: 98:BA:5F:C5:83:71" value="">
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Reemplazar MTU</label>
        </div>
        <div class="col-md-9">
            <input type="text" name="mtu" id="editIfaceMtu"
                class="form-control w-50" value="">
        </div>
    </div>
</div>

<!-- Opciones exclusivas de DHCP Cliente -->
<div id="protoDhcpAdvancedFields" class="d-none">
    
    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Utilizar la gestión integrada de IPv6</label>
        </div>
        <div class="col-md-9">
            <div class="form-check m-0">
                <input class="form-check-input" type="checkbox" name="delegate" id="editIfaceDelegateDhcp" value="1">
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Forzar enlace</label>
        </div>
        <div class="col-md-9">
            <div class="form-check m-0 mb-1">
                <input class="form-check-input" type="checkbox" name="force_link" id="editIfaceForceLinkDhcp" value="1">
            </div>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Configura las propiedades de la interfaz independientemente del operador de enlace.</small>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Usar marca de difusión</label>
        </div>
        <div class="col-md-9">
            <div class="form-check m-0 mb-1">
                <input class="form-check-input" type="checkbox" name="broadcast" id="editIfaceBroadcastAdvanced" value="1">
            </div>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Requerido para algunos ISP, p. ej. Charter con DOCSIS 3</small>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Utilizar la puerta predeterminada</label>
        </div>
        <div class="col-md-9">
            <div class="form-check m-0 mb-1">
                <input class="form-check-input" type="checkbox" name="defaultroute" id="editIfaceDefaultroute" value="1">
            </div>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Si no está marcado, no se configura ninguna ruta predeterminada.</small>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Usar los servidores predeterminados</label>
        </div>
        <div class="col-md-9">
            <div class="form-check m-0 mb-1">
                <input class="form-check-input" type="checkbox" name="peerdns" id="editIfacePeerdnsAdvanced" value="1">
            </div>
            <small style="color: var(--text-muted); font-size: 0.75rem;">El servidor local usará los servidores DNS proporcionados por DHCP.</small>
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Usar métrica de puerta de enlace</label>
        </div>
        <div class="col-md-9">
            <input type="text" name="metric" id="editIfaceMetricDhcp" class="form-control w-50" value="">
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">ID de cliente</label>
        </div>
        <div class="col-md-9">
            <input type="text" name="clientid" id="editIfaceClientid"
                class="form-control w-50" placeholder="ID a enviar al solicitar DHCP">
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Clase de vendedor</label>
        </div>
        <div class="col-md-9">
            <input type="text" name="vendorid" id="editIfaceVendorid"
                class="form-control w-50" placeholder="Clase a enviar cuando solicite DHCP">
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Reemplazar dirección MAC</label>
        </div>
        <div class="col-md-9">
            <input type="text" name="macaddr" id="editIfaceMacaddrDhcp"
                class="form-control w-50" placeholder="Ej: 98:BA:5F:C5:83:71" value="">
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Reemplazar MTU</label>
        </div>
        <div class="col-md-9">
            <input type="text" name="mtu" id="editIfaceMtuDhcp"
                class="form-control w-50" value="">
        </div>
    </div>

</div>

<!-- Opciones exclusivas de PPP -->
<div id="protoPppAdvancedFields" class="d-none">
    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Utilizar la gestión integrada de IPv6</label>
        </div>
        <div class="col-md-9">
            <div class="form-check m-0">
                <input class="form-check-input" type="checkbox" name="delegate" id="editIfaceDelegatePpp" value="1">
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Forzar enlace</label>
        </div>
        <div class="col-md-9">
            <div class="form-check m-0 mb-1">
                <input class="form-check-input" type="checkbox" name="force_link" id="editIfaceForceLinkPpp" value="1">
            </div>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Configura las propiedades de la interfaz independientemente del operador de enlace.</small>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Utilizar la puerta de enlace predeterminada</label>
        </div>
        <div class="col-md-9">
            <div class="form-check m-0 mb-1">
                <input class="form-check-input" type="checkbox" name="defaultroute" id="editIfaceDefaultroutePpp" value="1">
            </div>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Si no está marcado, no se mantendrá una ruta predeterminada vía PPP.</small>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 text-md-end pt-1">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Usar los servidores predeterminados</label>
        </div>
        <div class="col-md-9">
            <div class="form-check m-0 mb-1">
                <input class="form-check-input" type="checkbox" name="peerdns" id="editIfacePeerdnsPpp" value="1">
            </div>
            <small style="color: var(--text-muted); font-size: 0.75rem;">El servidor tomará los servidores DNS del par de conexión.</small>
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Usar métrica de puerta de enlace</label>
        </div>
        <div class="col-md-9">
            <input type="number" name="metric" id="editIfaceMetricPpp" class="form-control w-50">
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Umbral de fracaso en eco LCP</label>
        </div>
        <div class="col-md-9">
            <input type="number" name="lcp_echo_failure" id="editIfaceLcpEchoFailure"
                class="form-control w-50" placeholder="0">
            <small style="color: var(--text-muted); font-size: 0.75rem;">Se asume conexión caída tras N intentos fallidos de eco (por defecto 0, deshabilitado)</small>
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Intervalo de eco LCP</label>
        </div>
        <div class="col-md-9">
            <input type="number" name="lcp_echo_interval" id="editIfaceLcpEchoInterval"
                class="form-control w-50" placeholder="5">
            <small style="color: var(--text-muted); font-size: 0.75rem;">Envía un ping LCP cada N segundos (por defecto 5)</small>
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Espera de inactividad</label>
        </div>
        <div class="col-md-9">
            <input type="number" name="demand" id="editIfaceDemand"
                class="form-control w-50" placeholder="0">
            <small style="color: var(--text-muted); font-size: 0.75rem;">Cierra conexión si la inactividad supera N segundos (0 desactiva la demanda)</small>
        </div>
    </div>

    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-md-end">
            <label class="form-label mb-0"
                style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Reemplazar MTU</label>
        </div>
        <div class="col-md-9">
            <input type="number" name="mtu" id="editIfaceMtuPpp"
                class="form-control w-50" value="">
        </div>
    </div>
</div>
