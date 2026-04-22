<div class="row mb-4">
    <div class="col-md-3 text-md-end pt-1">
        <label class="form-label mb-0"
            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Ignorar interfaz</label>
    </div>
    <div class="col-md-9">
        <div class="form-check m-0 mb-1">
            <input class="form-check-input" type="checkbox" name="dhcp_ignore" id="editIfaceDhcpIgnore" value="1">
        </div>
        <small style="color: #e2eaff; font-size: 0.75rem;">Desactivar <a href="#"
                style="color: #5bc0de; text-decoration: none;">DHCP</a> para esta interfaz.</small>
    </div>
</div>

<div class="row align-items-center mb-4">
    <div class="col-md-3 text-md-end">
        <label class="form-label mb-0"
            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Iniciar</label>
    </div>
    <div class="col-md-9">
        <input type="text" name="dhcp_start" id="editIfaceDhcpStart" class="form-control w-50 mb-1" placeholder="Ej: 100">
        <small style="color: #e2eaff; font-size: 0.75rem;">Dirección asignada más baja como compensación de la dirección de red.</small>
    </div>
</div>

<div class="row align-items-center mb-4">
    <div class="col-md-3 text-md-end">
        <label class="form-label mb-0"
            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Límite</label>
    </div>
    <div class="col-md-9">
        <input type="text" name="dhcp_limit" id="editIfaceDhcpLimit" class="form-control w-50 mb-1" placeholder="Ej: 150">
        <small style="color: #e2eaff; font-size: 0.75rem;">Total de direcciones IP máxima para asignar.</small>
    </div>
</div>

<div class="row align-items-center mb-4">
    <div class="col-md-3 text-md-end">
        <label class="form-label mb-0"
            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Tiempo de asignación</label>
    </div>
    <div class="col-md-9">
        <input type="text" name="dhcp_leasetime" id="editIfaceDhcpLeasetime" class="form-control w-50 mb-1" placeholder="Ej: 12h">
        <small style="color: #e2eaff; font-size: 0.75rem;">Tiempo de expiración de direcciones, con un mínimo de 2m.</small>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 text-md-end pt-1">
        <label class="form-label mb-0"
            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">DHCP dinámico</label>
    </div>
    <div class="col-md-9">
        <div class="form-check m-0 mb-1">
            <input class="form-check-input" type="checkbox" name="dhcp_dynamic" id="editIfaceDhcpDynamic" value="1">
        </div>
        <small style="color: #e2eaff; font-size: 0.75rem;">Repartir direcciones DHCP dinámicamente a clientes.</small>
    </div>
</div>
