<!-- Sub Navigation Tabs for DHCP Server -->
<ul class="nav nav-tabs mb-4" id="dhcpSubTabs" role="tablist" style="border-bottom: 1px solid var(--border-soft);">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="dhcp-general-tab" data-bs-toggle="tab"
            data-bs-target="#dhcp-general" type="button" role="tab"
            style="font-size:0.85rem; padding: 8px 12px; font-weight: 600;">Configuración general</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="dhcp-advanced-tab" data-bs-toggle="tab"
            data-bs-target="#dhcp-advanced" type="button" role="tab"
            style="font-size:0.85rem; padding: 8px 12px; font-weight: 600;">Configuración avanzada</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="dhcp-ipv6-tab" data-bs-toggle="tab"
            data-bs-target="#dhcp-ipv6" type="button" role="tab"
            style="font-size:0.85rem; padding: 8px 12px; font-weight: 600;">Configuraciones IPv6</button>
    </li>
</ul>

<div class="tab-content" id="dhcpSubTabsContent">
    
    <!-- SUBTAB: Configuración General -->
    <div class="tab-pane fade show active" id="dhcp-general" role="tabpanel">
        <div class="row mb-4">
            <div class="col-md-3 text-md-end pt-1">
                <label class="form-label mb-0"
                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Ignorar interfaz</label>
            </div>
            <div class="col-md-9">
                <div class="form-check m-0 mb-1">
                    <input class="form-check-input" type="checkbox" name="dhcp_ignore" id="editIfaceDhcpIgnore" value="1">
                </div>
                <small style="color: var(--text-muted); font-size: 0.75rem;">Desactivar DHCP para esta interfaz.</small>
            </div>
        </div>

        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-md-end">
                <label class="form-label mb-0"
                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Iniciar</label>
            </div>
            <div class="col-md-9">
                <input type="text" name="dhcp_start" id="editIfaceDhcpStart" class="form-control w-50 mb-1" placeholder="Ej: 100">
                <small style="color: var(--text-muted); font-size: 0.75rem;">Dirección asignada más baja como compensación de la dirección de red.</small>
            </div>
        </div>

        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-md-end">
                <label class="form-label mb-0"
                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Límite</label>
            </div>
            <div class="col-md-9">
                <input type="text" name="dhcp_limit" id="editIfaceDhcpLimit" class="form-control w-50 mb-1" placeholder="Ej: 150">
                <small style="color: var(--text-muted); font-size: 0.75rem;">Total de direcciones IP máxima para asignar.</small>
            </div>
        </div>

        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-md-end">
                <label class="form-label mb-0"
                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Tiempo de asignación</label>
            </div>
            <div class="col-md-9">
                <input type="text" name="dhcp_leasetime" id="editIfaceDhcpLeasetime" class="form-control w-50 mb-1" placeholder="Ej: 12h">
                <small style="color: var(--text-muted); font-size: 0.75rem;">Tiempo de expiración de direcciones, con un mínimo de 2m.</small>
            </div>
        </div>
    </div>

    <!-- SUBTAB: Configuración Avanzada -->
    <div class="tab-pane fade" id="dhcp-advanced" role="tabpanel">
        <div class="row mb-4">
            <div class="col-md-3 text-md-end pt-1">
                <label class="form-label mb-0"
                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">DHCP dinámico</label>
            </div>
            <div class="col-md-9">
                <div class="form-check m-0 mb-1">
                    <input class="form-check-input" type="checkbox" name="dhcp_dynamic" id="editIfaceDhcpDynamic" value="1">
                </div>
                <small style="color: var(--text-muted); font-size: 0.75rem;">Repartir direcciones DHCP dinámicamente a clientes.</small>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 text-md-end pt-1">
                <label class="form-label mb-0"
                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Forzar</label>
            </div>
            <div class="col-md-9">
                <div class="form-check m-0 mb-1">
                    <input class="form-check-input" type="checkbox" name="dhcp_force" id="editIfaceDhcpForce" value="1">
                </div>
                <small style="color: var(--text-muted); font-size: 0.75rem;">Forzar DHCP en esta red, incluso si se detecta otro servidor.</small>
            </div>
        </div>

        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-md-end">
                <label class="form-label mb-0"
                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Máscara de red IPv4</label>
            </div>
            <div class="col-md-9">
                <input type="text" name="dhcp_netmask" id="editIfaceDhcpNetmask" class="form-control w-50 mb-1" placeholder="Ej: 255.255.255.0">
                <small style="color: var(--text-muted); font-size: 0.75rem;">Forzar una máscara en el servidor DHCP.</small>
            </div>
        </div>

        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-md-end">
                <label class="form-label mb-0"
                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Opciones de DHCP</label>
            </div>
            <div class="col-md-9">
                <input type="text" name="dhcp_options" id="editIfaceDhcpOptions" class="form-control w-75 mb-1" placeholder="Ej: 3,192.168.1.1 6,8.8.8.8">
                <small style="color: var(--text-muted); font-size: 0.75rem;">Opciones extras separadas por espacio. Ej: <code>3,192.168.1.1</code> (Puerta enlace) o <code>6,8.8.8.8</code> (DNS).</small>
            </div>
        </div>
    </div>

    <!-- SUBTAB: Configuraciones IPv6 -->
    <div class="tab-pane fade" id="dhcp-ipv6" role="tabpanel">
        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-md-end">
                <label class="form-label mb-0"
                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Servicio de anuncio de enrutador</label>
            </div>
            <div class="col-md-9">
                <select name="dhcp_ra" id="editIfaceDhcpRa" class="form-select w-50">
                    <option value="">Desactivado</option>
                    <option value="server">Modo servidor</option>
                    <option value="relay">Modo relé</option>
                    <option value="hybrid">Modo híbrido</option>
                </select>
            </div>
        </div>

        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-md-end">
                <label class="form-label mb-0"
                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Servicio DHCPv6</label>
            </div>
            <div class="col-md-9">
                <select name="dhcp_dhcpv6" id="editIfaceDhcpDhcpv6" class="form-select w-50">
                    <option value="">Desactivado</option>
                    <option value="server">Modo servidor</option>
                    <option value="relay">Modo relé</option>
                    <option value="hybrid">Modo híbrido</option>
                </select>
            </div>
        </div>

        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-md-end">
                <label class="form-label mb-0"
                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Proxy NDP</label>
            </div>
            <div class="col-md-9">
                <select name="dhcp_ndp" id="editIfaceDhcpNdp" class="form-select w-50">
                    <option value="">Desactivado</option>
                    <option value="relay">Modo relé</option>
                    <option value="hybrid">Modo híbrido</option>
                </select>
            </div>
        </div>

        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-md-end">
                <label class="form-label mb-0"
                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Servidores DNS anunciados</label>
            </div>
            <div class="col-md-9">
                <input type="text" name="dhcp_dns" id="editIfaceDhcpDns" class="form-control w-75 mb-1" placeholder="Ej: 2001:4860:4860::8888">
                <small style="color: var(--text-muted); font-size: 0.75rem;">Direcciones separadas por espacio.</small>
            </div>
        </div>

        <div class="row align-items-center mb-4">
            <div class="col-md-3 text-md-end">
                <label class="form-label mb-0"
                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Dominios DNS anunciados</label>
            </div>
            <div class="col-md-9">
                <input type="text" name="dhcp_domain" id="editIfaceDhcpDomain" class="form-control w-75 mb-1" placeholder="Ej: local">
                <small style="color: var(--text-muted); font-size: 0.75rem;">Dominios separados por espacio.</small>
            </div>
        </div>
    </div>
</div>
