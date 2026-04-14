@extends('layouts.dashboard')
@section('page-title', 'Red')
@section('content')

    <style>
        /* Corrección de visibilidad para las opciones de los menús desplegables */
        select option {
            background-color: var(--card-bg, #2b2b2b);
            color: var(--text-main, #e2eaff);
        }

        /* Añadir icono de flecha visible (blanco/claro) para identificar los selects */
        select.form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23e2eaff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }
    </style>

    <div class="container-fluid">

        <ul class="nav nav-tabs mb-4" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-interfaces-btn" data-bs-toggle="tab"
                    data-bs-target="#tab-interfaces" type="button" role="tab">Interfaces</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-global-btn" data-bs-toggle="tab" data-bs-target="#tab-global" type="button"
                    role="tab">Opciones globales de red</button>
            </li>
        </ul>

        <div class="tab-content" id="mainTabsContent">
            <div class="tab-pane fade show active" id="tab-interfaces" role="tabpanel">
                <h3 class="page-title mb-4">Interfaces</h3>

                <div class="panel-card mb-4 p-0 overflow-hidden" style="border-radius: 4px;">

                    @forelse($interfaces as $iface)
                    <!-- Interface -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between p-3"
                        style="border-bottom: 1px solid var(--border-soft); background: rgba(255,255,255,0.02);">
                        <div class="d-flex align-items-center gap-5" style="width: 60%;">

                            <div class="text-center" style="width: 100px;">
                                <div class="mb-1"
                                    style="background-color: {{ isset($iface['up']) && $iface['up'] ? (stripos($iface['interface'], 'wan') !== false ? '#d9534f' : '#51a351') : '#777' }}; color: white; padding: 4px; font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">
                                    {{ $iface['interface'] ?? 'N/A' }}</div>
                                <div>
                                    <i class="bi bi-{{ stripos($iface['interface'] ?? '', 'wan') !== false ? 'hdd-network' : 'diagram-3' }}" style="font-size: 1.2rem; color: {{ isset($iface['up']) && $iface['up'] ? '#5bc0de' : '#777' }};"></i>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-soft);">{{ $iface['device'] ?? 'N/A' }}</div>
                            </div>

                            <div style="font-size: 0.8rem; color: var(--text-main); font-weight: 600; line-height: 1.3;">
                                @if(isset($iface['up']) && $iface['up'])
                                    Protocolo: <span style="font-weight: 400;">{{ ucfirst($iface['proto'] ?? 'Desconocido') }}</span><br>
                                    @if(isset($iface['uptime']))
                                        Tiempo de actividad: <span style="font-weight: 400;">{{ gmdate("G\h i\m s\s", $iface['uptime']) }}</span><br>
                                    @endif
                                    @if(isset($iface['mac']))
                                        MAC: <span style="font-weight: 400;">{{ strtoupper($iface['mac']) }}</span><br>
                                    @endif
                                    @if(isset($iface['statistics']))
                                        @php
                                            $rx = $iface['statistics']['rx_bytes'] ?? 0;
                                            $tx = $iface['statistics']['tx_bytes'] ?? 0;
                                            $rxStr = $rx > 1048576 ? round($rx/1048576, 2) . ' MB' : round($rx/1024, 2) . ' KB';
                                            $txStr = $tx > 1048576 ? round($tx/1048576, 2) . ' MB' : round($tx/1024, 2) . ' KB';
                                        @endphp
                                        RX: <span style="font-weight: 400;">{{ $rxStr }} ({{ $iface['statistics']['rx_packets'] ?? 0 }} Paq.)</span><br>
                                        TX: <span style="font-weight: 400;">{{ $txStr }} ({{ $iface['statistics']['tx_packets'] ?? 0 }} Paq.)</span><br>
                                    @endif
                                    @if(!empty($iface['ipv4-address']))
                                        IPv4: <span style="font-weight: 400;">{{ $iface['ipv4-address'][0]['address'] }}/{{ $iface['ipv4-address'][0]['mask'] }}</span>
                                    @endif
                                @else
                                    <span style="font-weight: 400; color: #a94442;">La interfaz está detenida</span>
                                @endif
                            </div>

                        </div>

                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <button class="btn btn-sm"
                                style="background: #e6e6e6; color: #333; font-weight: 700; font-size: 0.75rem; border-radius: 2px;">REINICIAR</button>
                            <button class="btn btn-sm"
                                style="background: #e6e6e6; color: {{ isset($iface['up']) && $iface['up'] ? '#333' : '#b3b3b3' }}; font-weight: 700; font-size: 0.75rem; border-radius: 2px;"
                                {{ isset($iface['up']) && $iface['up'] ? '' : 'disabled' }}>DETENER</button>
                            <button class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditarInterfaz{{ stripos($iface['interface'], 'wan') !== false ? 'WAN' : '' }}"
                                style="background: #5bc0de; color: white; font-weight: 700; border: none; font-size: 0.75rem; border-radius: 2px;">EDITAR</button>
                            <button class="btn btn-sm"
                                style="background: #d9534f; color: white; font-weight: 700; border: none; font-size: 0.75rem; border-radius: 2px;">ELIMINAR</button>
                        </div>
                    </div>
                    @empty
                        <div class="p-4 text-center" style="color: var(--text-soft);">
                            No se encontraron interfaces configuradas o conectadas en el router.
                        </div>
                    @endforelse

                    <div class="p-3" style="background-color: var(--card-bg);">
                        <button class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarInterfaz"
                            style="font-weight: 700; font-size: 0.75rem; background: #337ab7; color: white; border: none; border-radius: 2px;">
                            AÑADIR NUEVA INTERFAZ...
                        </button>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-global" role="tabpanel">
                <div class="panel-card mb-4"
                    style="background: var(--card-bg); border: 1px solid var(--border-soft); border-radius: 4px; padding: 12px 16px;">
                    <span style="font-style: italic; color: var(--text-main); font-size: 0.9rem;">Esta sección aún no
                        contiene valores</span>
                </div>
            </div>
        </div>

        <!-- Bottom Actions -->
        <div class="d-flex justify-content-end gap-2 mt-4">
            <div class="btn-group">
                <button class="btn btn-sm"
                    style="color: white; font-weight: 700; font-size: 0.75rem; background-color: #5bc0de; border: none; border-radius: 2px 0 0 2px;">GUARDAR
                    Y APLICAR</button>
                <button type="button" class="btn btn-sm dropdown-toggle dropdown-toggle-split"
                    style="color: white; background-color: #5bc0de; border: none; border-radius: 0 2px 2px 0;"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                    <li><a class="dropdown-item" style="font-size: 0.85rem;" href="#">Aplicar sin guardar</a></li>
                </ul>
            </div>
            <button class="btn btn-sm"
                style="font-weight: 700; font-size: 0.75rem; background-color: #337ab7; color: white; border: none; border-radius: 2px;">GUARDAR</button>
            <button class="btn btn-sm"
                style="font-weight: 700; font-size: 0.75rem; background-color: #d9534f; color: white; border: none; border-radius: 2px;">RESTABLECER</button>
        </div>

    </div>

    {{-- Modal Agregar Interfaz --}}
    <div class="modal fade" id="modalAgregarInterfaz" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content"
                style="background: var(--card-bg); border: 1px solid var(--border-soft); border-radius: var(--radius-lg);">
                <div class="modal-header" style="border-bottom: 1px solid var(--border-soft);">
                    <h5 class="modal-title" style="font-weight:700; color:#e2eaff;">
                        Añadir nueva interfaz...
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('network.interfaces.store') }}">
                    @csrf
                    <div class="modal-body" style="padding: 30px;">

                        <div class="row align-items-center mb-4">
                            <div class="col-md-3 text-md-end">
                                <label class="form-label mb-0"
                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Nombre</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" name="name"
                                    class="form-control @error('name', 'createInterface') is-invalid @enderror"
                                    placeholder="Nuevo nombre de interfaz..." value="{{ old('name') }}">
                                @error('name', 'createInterface')
                                    <div class="invalid-feedback text-start mt-1" style="font-size: 0.8rem; display: block;">
                                        {{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row align-items-center mb-4">
                            <div class="col-md-3 text-md-end">
                                <label class="form-label mb-0"
                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Protocolo</label>
                            </div>
                            <div class="col-md-9">
                                <select name="protocol"
                                    class="form-select @error('protocol', 'createInterface') is-invalid @enderror">
                                    <option value="dhcp" {{ old('protocol', 'dhcp') == 'dhcp' ? 'selected' : '' }}>Cliente
                                        DHCP</option>
                                    <option value="unmanaged" {{ old('protocol') == 'unmanaged' ? 'selected' : '' }}>No
                                        administrado</option>
                                    <option value="ppp" {{ old('protocol') == 'ppp' ? 'selected' : '' }}>PPP</option>
                                    <option value="pppoe" {{ old('protocol') == 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                                    <option value="static" {{ old('protocol') == 'static' ? 'selected' : '' }}>Dirección
                                        estática</option>
                                </select>
                                @error('protocol', 'createInterface')
                                    <div class="invalid-feedback text-start mt-1" style="font-size: 0.8rem; display: block;">
                                        {{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-3 text-md-end pt-1">
                                <label class="form-label mb-0"
                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Puentear
                                    interfaces</label>
                            </div>
                            <div class="col-md-9">
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" name="bridge" id="bridgeCheckbox"
                                        value="1" {{ old('bridge') ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2" for="bridgeCheckbox"
                                        style="color: var(--text-muted); font-size: 0.85rem;">
                                        Crea un puente sobre la interfaz o interfaces asociadas
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center mb-2">
                            <div class="col-md-3 text-md-end">
                                <label class="form-label mb-0"
                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Interfaz</label>
                            </div>
                            <div class="col-md-9">
                                <select name="interface"
                                    class="form-select @error('interface', 'createInterface') is-invalid @enderror"
                                    style="font-style: italic;">
                                    <option value="" {{ old('interface') == '' ? 'selected' : '' }}>Sin especificar</option>
                                    <option value="br-lan" {{ old('interface') == 'br-lan' ? 'selected' : '' }}>Puente:
                                        "br-lan" (lan)</option>
                                    <option value="eth0" {{ old('interface') == 'eth0' ? 'selected' : '' }}>Conmutador
                                        ethernet: "eth0"</option>
                                    <option value="eth0.1" {{ old('interface') == 'eth0.1' ? 'selected' : '' }}>Switch VLAN:
                                        "eth0.1" (lan)</option>
                                    <option value="eth0.2" {{ old('interface') == 'eth0.2' ? 'selected' : '' }}>Switch VLAN:
                                        "eth0.2" (wan)</option>
                                    <option value="wifi" {{ old('interface') == 'wifi' ? 'selected' : '' }}>Red Wi-Fi: Master
                                        "usuario_remoto" (lan)</option>
                                    <option value="@lan" {{ old('interface')=='@lan' ? 'selected' : '' }}>Apodo de interfaz:
                                        "@lan"</option>
                                    <option value="@wan" {{ old('interface')=='@wan' ? 'selected' : '' }}>Apodo de interfaz:
                                        "@wan"</option>
                                    <option value="custom" {{ old('interface') == 'custom' ? 'selected' : '' }}>Personalizado
                                    </option>
                                </select>
                                @error('interface', 'createInterface')
                                    <div class="invalid-feedback text-start mt-1" style="font-size: 0.8rem; display: block;">
                                        {{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer"
                        style="border-top: 1px solid var(--border-soft); background: rgba(255,255,255,0.01);">
                        <button type="button" class="btn btn-sm" data-bs-dismiss="modal"
                            style="background:#e6e6e6; color:#333; font-weight:700; font-size:0.75rem; border-radius:2px; padding:6px 14px;">
                            CANCELAR
                        </button>
                        <button type="submit" class="btn btn-sm"
                            style="background:#337ab7; color:white; font-weight:700; font-size:0.75rem; border:none; border-radius:2px; padding:6px 14px;">
                            CREAR INTERFAZ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Editar Interfaz (Solo Estructura Base) --}}
    <div class="modal fade" id="modalEditarInterfaz" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content"
                style="background: var(--card-bg); border: 1px solid var(--border-soft); border-radius: var(--radius-lg);">
                <div class="modal-header" style="border-bottom: 1px solid var(--border-soft);">
                    <h5 class="modal-title" style="font-weight:700; color:#e2eaff;">
                        Interfaces » LAN
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('network.interfaces.lan.update') }}">
                    @csrf

                    <div class="modal-body p-0">
                        <!-- Navigation Tabs -->
                        <ul class="nav nav-tabs px-3 pt-3"
                            style="border-bottom: 1px solid var(--border-soft); background: rgba(255,255,255,0.02);"
                            id="editTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                    data-bs-target="#general" type="button" role="tab"
                                    style="font-size:0.85rem; padding: 10px 16px;">Configuración general</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced"
                                    type="button" role="tab" style="font-size:0.85rem; padding: 10px 16px;">Configuración
                                    avanzada</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="physical-tab" data-bs-toggle="tab" data-bs-target="#physical"
                                    type="button" role="tab" style="font-size:0.85rem; padding: 10px 16px;">Configuración
                                    física</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="firewall-tab" data-bs-toggle="tab" data-bs-target="#firewall"
                                    type="button" role="tab" style="font-size:0.85rem; padding: 10px 16px;">Configuración
                                    del cortafuegos</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="dhcp-tab" data-bs-toggle="tab" data-bs-target="#dhcp"
                                    type="button" role="tab" style="font-size:0.85rem; padding: 10px 16px;">Servidor
                                    DHCP</button>
                            </li>
                        </ul>

                        <div class="tab-content p-4" id="editTabsContent">
                            <div class="tab-pane fade show active" id="general" role="tabpanel">

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
                                                <strong>Dispositivo: br-lan</strong><br>
                                                <strong>Tiempo de actividad:</strong> 1h 8m 36s<br>
                                                <strong>MAC:</strong> 90:EA:5F:C5:83:71<br>
                                                <strong>RX:</strong> 2.14 MB (13337 Paq.)<br>
                                                <strong>TX:</strong> 7.69 MB (11567 Paq.)<br>
                                                <strong>IPv4:</strong> 192.168.10.1/24
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
                                        <select class="form-select w-50">
                                            <option value="dhcp">Cliente DHCP</option>
                                            <option value="unmanaged">No administrado</option>
                                            <option value="ppp">PPP</option>
                                            <option value="pppoe">PPPoE</option>
                                            <option value="static" selected>Dirección estática</option>
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
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Dirección
                                            IPv4</label>
                                    </div>
                                    <div class="col-md-9 gap-2">
                                        <div class="d-flex gap-2">
                                            <input type="text" name="lan_ipv4_address"
                                                class="form-control w-50 @error('lan_ipv4_address', 'updateLan') is-invalid @enderror"
                                                value="{{ old('lan_ipv4_address', '192.168.10.1') }}">
                                            <button type="button" class="btn btn-sm"
                                                style="background: rgba(255,255,255,0.1); color: var(--text-main); border: 1px solid var(--border-soft);"
                                                title="Cambiar a la notación de lista CIDR">...</button>
                                        </div>
                                        @error('lan_ipv4_address', 'updateLan')
                                            <div class="invalid-feedback text-start mt-1"
                                                style="font-size: 0.8rem; display: block;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Máscara de
                                            red IPv4</label>
                                    </div>
                                    <div class="col-md-9">
                                        <select name="lan_ipv4_netmask"
                                            class="form-select w-50 @error('lan_ipv4_netmask', 'updateLan') is-invalid @enderror">
                                            <option value="" style="font-style: italic;">Sin especificar</option>
                                            <option value="255.255.255.0" {{ old('lan_ipv4_netmask', '255.255.255.0') == '255.255.255.0' ? 'selected' : '' }}>255.255.255.0
                                            </option>
                                            <option value="255.255.0.0" {{ old('lan_ipv4_netmask') == '255.255.0.0' ? 'selected' : '' }}>255.255.0.0</option>
                                            <option value="255.0.0.0" {{ old('lan_ipv4_netmask') == '255.0.0.0' ? 'selected' : '' }}>255.0.0.0</option>
                                            <option value="custom" {{ old('lan_ipv4_netmask') == 'custom' ? 'selected' : '' }}>-- Personalizado --</option>
                                        </select>
                                        @error('lan_ipv4_netmask', 'updateLan')
                                            <div class="invalid-feedback text-start mt-1"
                                                style="font-size: 0.8rem; display: block;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Puerta de
                                            enlace IPv4</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="lan_ipv4_gateway"
                                            class="form-control w-50 @error('lan_ipv4_gateway', 'updateLan') is-invalid @enderror"
                                            value="{{ old('lan_ipv4_gateway') }}">
                                        @error('lan_ipv4_gateway', 'updateLan')
                                            <div class="invalid-feedback text-start mt-1"
                                                style="font-size: 0.8rem; display: block;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Difusión
                                            IPv4</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" value="192.168.10.255">
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Usar
                                            servidores DNS personalizados</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="d-flex w-50">
                                            <input type="text" class="form-control form-control-sm me-2"
                                                style="border-radius: 2px;">
                                            <button type="button" class="btn btn-sm"
                                                style="background: #337ab7; color: white; width: 32px;"><i
                                                    class="bi bi-plus"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Longitud
                                            de asignación de IPv6</label>
                                    </div>
                                    <div class="col-md-9">
                                        <select class="form-select w-50 mb-1">
                                            <option value="disabled" selected>Desactivado</option>
                                            <option value="64">64</option>
                                            <option value="custom">-- Personalizado --</option>
                                        </select>
                                        <small style="color: var(--text-muted); font-size: 0.75rem;">Asigna una parte de la
                                            longitud dada de cada prefijo IPv6 público a esta interfaz.</small>
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Dirección
                                            IPv6</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="d-flex w-50">
                                            <input type="text" class="form-control form-control-sm me-2"
                                                placeholder="Añadir dirección IPv6..." style="border-radius: 2px;">
                                            <button type="button" class="btn btn-sm"
                                                style="background: #337ab7; color: white; width: 32px;"><i
                                                    class="bi bi-plus"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Puerta de
                                            enlace IPv6</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Prefijo
                                            IPv6 enrutado</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50 mb-1">
                                        <small style="color: var(--text-muted); font-size: 0.75rem;">Prefijo público
                                            enrutado a este dispositivo para su distribución a los clientes.</small>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Sufijo
                                            IPv6</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50 mb-1" value="::1">
                                        <small style="color: var(--text-muted); font-size: 0.75rem;">Opcional. Valores
                                            permitidos: 'eui64', 'random', valor fijo como '::1' o '::1:2'. Cuando se recibe
                                            un prefijo IPv6 (como 'a:b:c:d::') desde un servidor delegante, use el sufijo
                                            (como '::1') para formar la dirección IPv6 ('a:b:c:d::1') para la
                                            interfaz.</small>
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane fade" id="advanced" role="tabpanel">

                                <div class="row mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Utilizar
                                            la gestión integrada de IPv6</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-check m-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Forzar
                                            enlace</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-check m-0 mb-1">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                        <small style="color: var(--text-muted); font-size: 0.75rem;">Configura las
                                            propiedades de la interfaz independientemente del operador de enlace (si está
                                            configurado, los eventos de detección de operador no invocan los controladores
                                            de conexión en caliente).</small>
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Reemplazar
                                            dirección MAC</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="lan_mac"
                                            class="form-control w-50 @error('lan_mac', 'updateLan') is-invalid @enderror"
                                            placeholder="98:BA:5F:C5:83:71" value="{{ old('lan_mac') }}">
                                        @error('lan_mac', 'updateLan')
                                            <div class="invalid-feedback text-start mt-1"
                                                style="font-size: 0.8rem; display: block;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Reemplazar
                                            MTU</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="lan_mtu"
                                            class="form-control w-50 @error('lan_mtu', 'updateLan') is-invalid @enderror"
                                            value="{{ old('lan_mtu', '1500') }}">
                                        @error('lan_mtu', 'updateLan')
                                            <div class="invalid-feedback text-start mt-1"
                                                style="font-size: 0.8rem; display: block;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Usar
                                            métrica de puerta de enlace</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" value="0">
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane fade" id="physical" role="tabpanel">

                                <div class="row mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Puentear
                                            interfaces</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-check m-0 mb-1">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                        <small style="color: var(--text-muted); font-size: 0.75rem;">Crea un puente sobre la
                                            interfaz o interfaces asociadas</small>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Activar <a
                                                href="#"
                                                style="text-decoration: underline dotted; color: #5bc0de;">STP</a></label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-check m-0 mb-1">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                        <small style="color: var(--text-muted); font-size: 0.75rem;">Activa el protocolo
                                            Spanning Tree en este puente</small>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Activar <a
                                                href="#" style="text-decoration: underline dotted; color: #5bc0de;">IGMP</a>
                                            Snooping</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-check m-0 mb-1">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                        <small style="color: var(--text-muted); font-size: 0.75rem;">Activa el protocolo
                                            IGMP Snooping en este puente</small>
                                    </div>
                                </div>

                                <div class="row align-items-start mb-4">
                                    <div class="col-md-3 text-md-end pt-2">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Interfaz</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="dropdown">
                                            <button class="form-select text-start d-flex align-items-center gap-4 w-75"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                data-bs-auto-close="outside"
                                                style="min-height: 38px; color: var(--text-main); background-color: transparent;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-hdd-network" style="color: #cfcfcf;"></i> eth0.1
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-wifi" style="color: #5bc0de;"></i> wlan0
                                                </div>
                                            </button>
                                            <ul class="dropdown-menu w-75 shadow"
                                                style="background-color: var(--card-bg, #2b2b2b); border: 1px solid var(--border-soft);">
                                                <li class="dropdown-item px-2 py-1" style="color: var(--text-main);">
                                                    <div class="form-check m-0 d-flex align-items-center gap-2">
                                                        <input class="form-check-input m-0" type="checkbox">
                                                        <i class="bi bi-hdd-network"
                                                            style="color: #cfcfcf; font-size: 0.9rem;"></i>
                                                        <span style="font-size: 0.85rem;">Conmutador ethernet: "eth0"</span>
                                                    </div>
                                                </li>
                                                <li class="dropdown-item px-2 py-1"
                                                    style="background-color: rgba(51, 122, 183, 0.4); color: var(--text-main);">
                                                    <div class="form-check m-0 d-flex align-items-center gap-2">
                                                        <input class="form-check-input m-0" type="checkbox" checked>
                                                        <i class="bi bi-hdd-network"
                                                            style="color: #cfcfcf; font-size: 0.9rem;"></i>
                                                        <span style="font-size: 0.85rem;">Switch VLAN: "eth0.1" (lan)</span>
                                                    </div>
                                                </li>
                                                <li class="dropdown-item px-2 py-1" style="color: var(--text-main);">
                                                    <div class="form-check m-0 d-flex align-items-center gap-2">
                                                        <input class="form-check-input m-0" type="checkbox">
                                                        <i class="bi bi-hdd-network"
                                                            style="color: #cfcfcf; font-size: 0.9rem;"></i>
                                                        <span style="font-size: 0.85rem;">Switch VLAN: "eth0.2" (wan)</span>
                                                    </div>
                                                </li>
                                                <li class="dropdown-item px-2 py-1"
                                                    style="background-color: rgba(51, 122, 183, 0.4); color: var(--text-main);">
                                                    <div class="form-check m-0 d-flex align-items-center gap-2">
                                                        <input class="form-check-input m-0" type="checkbox" checked>
                                                        <i class="bi bi-wifi"
                                                            style="color: #5bc0de; font-size: 0.9rem;"></i>
                                                        <span style="font-size: 0.85rem;">Red Wi-Fi: Master "usuario_remoto"
                                                            (lan)</span>
                                                    </div>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider my-1"
                                                        style="border-color: var(--border-soft);">
                                                </li>
                                                <li class="dropdown-item px-2 py-1" style="color: var(--text-muted);">
                                                    <div class="form-check m-0 d-flex align-items-center gap-2">
                                                        <input class="form-check-input m-0" type="checkbox" disabled>
                                                        <span style="font-size: 0.85rem; font-style: italic;">--
                                                            Personalizado --</span>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane fade" id="firewall" role="tabpanel">

                                <div class="row align-items-start mb-4">
                                    <div class="col-md-3 text-md-end pt-2">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Crear /
                                            Asignar zona de cortafuegos</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="dropdown w-50">
                                            <button
                                                class="form-select text-start d-flex align-items-center justify-content-between p-1 w-100"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                style="min-height: 38px; background-color: var(--card-bg, #2b2b2b); border: 1px solid var(--border-soft);">
                                                <div class="d-inline-flex align-items-center px-2 py-1 flex-grow-1"
                                                    style="background-color: #92e592; color: #2e6c2e; font-weight: bold; border-radius: 2px;">
                                                    lan
                                                    <div class="d-inline-flex align-items-center gap-1 ms-2"
                                                        style="background-color: #f8f9fa; border: 1px solid #ced4da; padding: 1px 4px; font-weight: normal; font-size: 0.8rem; border-radius: 2px;">
                                                        lan:
                                                        <i class="bi bi-hdd-network text-secondary"></i>
                                                        <i class="bi bi-wifi text-secondary"></i>
                                                    </div>
                                                </div>
                                            </button>
                                            <ul class="dropdown-menu shadow w-100 p-0"
                                                style="background-color: var(--card-bg, #2b2b2b); border: 1px solid var(--border-soft);">
                                                <li>
                                                    <a class="dropdown-item py-2 px-3" href="#"
                                                        style="color: var(--text-muted); font-style: italic; background: transparent;">
                                                        Sin especificar
                                                    </a>
                                                </li>
                                                <li style="background-color: rgba(51, 122, 183, 0.4);">
                                                    <a class="dropdown-item p-1" href="#" style="background: transparent;">
                                                        <div class="d-inline-flex align-items-center px-2 py-1 w-100"
                                                            style="background-color: #92e592; color: #2e6c2e; font-weight: bold; border-radius: 2px;">
                                                            lan
                                                            <div class="d-inline-flex align-items-center gap-1 ms-2"
                                                                style="background-color: #f8f9fa; border: 1px solid #ced4da; padding: 1px 4px; font-weight: normal; font-size: 0.8rem; border-radius: 2px;">
                                                                lan:
                                                                <i class="bi bi-hdd-network text-secondary"></i>
                                                                <i class="bi bi-wifi text-secondary"></i>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item p-1" href="#" style="background: transparent;">
                                                        <div class="d-inline-flex align-items-center px-2 py-1 w-100"
                                                            style="background-color: #e59292; color: #6c2e2e; font-weight: bold; border-radius: 2px;">
                                                            wan
                                                            <div class="d-inline-flex align-items-center gap-1 ms-2"
                                                                style="background-color: #f8f9fa; border: 1px solid #ced4da; padding: 1px 4px; font-weight: normal; font-size: 0.8rem; border-radius: 2px;">
                                                                wan:
                                                                <i class="bi bi-hdd-network text-secondary"></i>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2 px-3" href="#"
                                                        style="color: var(--text-muted); background: transparent;">
                                                        -- Personalizado --
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <small class="d-block mt-2" style="color: #e2eaff; font-size: 0.75rem;">
                                            Elija la zona del cortafuegos a la que quiere asignar esta interfaz. Seleccione
                                            <em>Sin especificar</em> para remover la interfaz de la zona asociada o rellene
                                            el campo <em>Personalizado</em> para definir una zona nueva a la que asignarla.
                                        </small>
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane fade" id="dhcp" role="tabpanel">

                                <!-- Sub-Tabs for DHCP -->
                                <ul class="nav nav-tabs mb-4 border-0" id="dhcpTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="dhcp-general-tab" data-bs-toggle="tab"
                                            data-bs-target="#dhcp-general" type="button" role="tab"
                                            style="font-size:0.85rem; padding: 10px 16px;">Configuración general</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="dhcp-advanced-tab" data-bs-toggle="tab"
                                            data-bs-target="#dhcp-advanced" type="button" role="tab"
                                            style="font-size:0.85rem; padding: 10px 16px;">Configuración avanzada</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="dhcp-ipv6-tab" data-bs-toggle="tab"
                                            data-bs-target="#dhcp-ipv6" type="button" role="tab"
                                            style="font-size:0.85rem; padding: 10px 16px;">Configuraciones IPv6</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="dhcpTabsContent"
                                    style="border-top: 1px solid var(--border-soft); padding-top: 20px;">
                                    <!-- DHCP: General -->
                                    <div class="tab-pane fade show active" id="dhcp-general" role="tabpanel">

                                        <div class="row mb-4">
                                            <div class="col-md-3 text-md-end pt-1">
                                                <label class="form-label mb-0"
                                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Desactivar
                                                    DHCP</label>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="form-check m-0 mb-1">
                                                    <input class="form-check-input" type="checkbox">
                                                </div>
                                                <small style="color: #e2eaff; font-size: 0.75rem;">Desactivar <a href="#"
                                                        style="color: #5bc0de; text-decoration: none;">DHCP</a> para esta
                                                    interfaz.</small>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-4">
                                            <div class="col-md-3 text-md-end">
                                                <label class="form-label mb-0"
                                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Iniciar</label>
                                            </div>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control w-50 mb-1" value="100">
                                                <small style="color: #e2eaff; font-size: 0.75rem;">Dirección asignada más
                                                    baja como compensación de la dirección de red.</small>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-4">
                                            <div class="col-md-3 text-md-end">
                                                <label class="form-label mb-0"
                                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Límite</label>
                                            </div>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control w-50 mb-1" value="150">
                                                <small style="color: #e2eaff; font-size: 0.75rem;">IP máxima para
                                                    asignar.</small>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-4">
                                            <div class="col-md-3 text-md-end">
                                                <label class="form-label mb-0"
                                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Tiempo
                                                    de asignación</label>
                                            </div>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control w-50 mb-1" value="12h">
                                                <small style="color: #e2eaff; font-size: 0.75rem;">Tiempo de expiración de
                                                    direcciones asignadas, con un mínimo de dos minutos (<code
                                                        style="background: rgba(255,255,255,0.1); padding: 2px 4px; border-radius: 4px; color: var(--text-main);">2m</code>).</small>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- DHCP: Avanzada -->
                                    <div class="tab-pane fade" id="dhcp-advanced" role="tabpanel">

                                        <div class="row mb-4">
                                            <div class="col-md-3 text-md-end pt-1">
                                                <label class="form-label mb-0"
                                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">DHCP
                                                    dinámico</label>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="form-check m-0 mb-1">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                </div>
                                                <small style="color: #e2eaff; font-size: 0.75rem;">Reparte direcciones DHCP
                                                    dinámicamente a los clientes. Si se desactiva, solo se dará a clientes
                                                    con asignaciones estáticas.</small>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-3 text-md-end pt-1">
                                                <label class="form-label mb-0"
                                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Forzar</label>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="form-check m-0 mb-1">
                                                    <input class="form-check-input" type="checkbox">
                                                </div>
                                                <small style="color: #e2eaff; font-size: 0.75rem;">Forzar DHCP en esta red
                                                    aunque se detecte otro servidor.</small>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-4">
                                            <div class="col-md-3 text-md-end">
                                                <label class="form-label mb-0"
                                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Máscara
                                                    de red <a href="#"
                                                        style="text-decoration: underline dotted; color: #5bc0de;">IPv4</a></label>
                                            </div>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control w-50 mb-1" value="255.255.255.0">
                                                <small style="color: #e2eaff; font-size: 0.75rem;">Anula la máscara de red
                                                    enviada a los clientes. Normalmente se calcula a partir de la subred que
                                                    se sirve.</small>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-4">
                                            <div class="col-md-3 text-md-end">
                                                <label class="form-label mb-0"
                                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Opciones
                                                    de DHCP</label>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="d-flex w-50 mb-1">
                                                    <input type="text" class="form-control form-control-sm me-2"
                                                        style="border-radius: 2px;">
                                                    <button type="button" class="btn btn-sm"
                                                        style="background: #337ab7; color: white; width: 32px;"><i
                                                            class="bi bi-plus"></i></button>
                                                </div>
                                                <small class="d-block" style="color: #e2eaff; font-size: 0.75rem;">Define
                                                    opciones adicionales de DHCP, por ejemplo <code
                                                        style="background: rgba(255,255,255,0.1); padding: 2px 4px; border-radius: 4px; color: var(--text-main);">6,192.168.2.1,192.168.2.2</code>
                                                    que publica diferentes servidores DNS a los clientes.</small>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- DHCP: IPv6 -->
                                    <div class="tab-pane fade" id="dhcp-ipv6" role="tabpanel">

                                        <div class="row align-items-center mb-4">
                                            <div class="col-md-3 text-md-end">
                                                <label class="form-label mb-0"
                                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Servicio
                                                    de anuncio de enrutador</label>
                                            </div>
                                            <div class="col-md-9">
                                                <select class="form-select w-50">
                                                    <option value="disabled" selected>Desactivado</option>
                                                    <option value="server">Modo servidor</option>
                                                    <option value="relay">Modo relé</option>
                                                    <option value="hybrid">Modo híbrido</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-4">
                                            <div class="col-md-3 text-md-end">
                                                <label class="form-label mb-0"
                                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Servicio
                                                    DHCPv6</label>
                                            </div>
                                            <div class="col-md-9">
                                                <select class="form-select w-50">
                                                    <option value="disabled" selected>Desactivado</option>
                                                    <option value="server">Modo servidor</option>
                                                    <option value="relay">Modo relé</option>
                                                    <option value="hybrid">Modo híbrido</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-4">
                                            <div class="col-md-3 text-md-end">
                                                <label class="form-label mb-0"
                                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Proxy
                                                    NDP</label>
                                            </div>
                                            <div class="col-md-9">
                                                <select class="form-select w-50">
                                                    <option value="disabled" selected>Desactivado</option>
                                                    <option value="relay">Modo relé</option>
                                                    <option value="hybrid">Modo híbrido</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-4">
                                            <div class="col-md-3 text-md-end">
                                                <label class="form-label mb-0"
                                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Servidores
                                                    DNS anunciados</label>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="d-flex w-50">
                                                    <input type="text" class="form-control form-control-sm me-2"
                                                        style="border-radius: 2px;">
                                                    <button type="button" class="btn btn-sm"
                                                        style="background: #337ab7; color: white; width: 32px;"><i
                                                            class="bi bi-plus"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-4">
                                            <div class="col-md-3 text-md-end">
                                                <label class="form-label mb-0"
                                                    style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Dominios
                                                    DNS anunciados</label>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="d-flex w-50">
                                                    <input type="text" class="form-control form-control-sm me-2"
                                                        style="border-radius: 2px;">
                                                    <button type="button" class="btn btn-sm"
                                                        style="background: #337ab7; color: white; width: 32px;"><i
                                                            class="bi bi-plus"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="modal-footer"
                        style="border-top: 1px solid var(--border-soft); background: rgba(255,255,255,0.01);">
                        <button type="button" class="btn btn-sm" data-bs-dismiss="modal"
                            style="background:#e6e6e6; color:#333; font-weight:700; font-size:0.75rem; border-radius:2px; padding:6px 14px;">
                            DESCARTAR
                        </button>
                        <button type="submit" class="btn btn-sm"
                            style="background:#337ab7; color:white; font-weight:700; font-size:0.75rem; border:none; border-radius:2px; padding:6px 14px;">
                            GUARDAR
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Modal Editar Interfaz WAN (Estructura Vacía) --}}
    <div class="modal fade" id="modalEditarInterfazWAN" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content"
                style="background: var(--card-bg); border: 1px solid var(--border-soft); border-radius: var(--radius-lg);">
                <div class="modal-header" style="border-bottom: 1px solid var(--border-soft);">
                    <h5 class="modal-title" style="font-weight:700; color:#e2eaff;">
                        Interfaces » WAN
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('network.interfaces.wan.update') }}">
                    @csrf

                    <div class="modal-body p-0">
                        <!-- Navigation Tabs -->
                        <ul class="nav nav-tabs px-3 pt-3"
                            style="border-bottom: 1px solid var(--border-soft); background: rgba(255,255,255,0.02);"
                            id="editTabsWAN" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="wan-general-tab" data-bs-toggle="tab"
                                    data-bs-target="#wan-general" type="button" role="tab"
                                    style="font-size:0.85rem; padding: 10px 16px;">Configuración general</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="wan-advanced-tab" data-bs-toggle="tab"
                                    data-bs-target="#wan-advanced" type="button" role="tab"
                                    style="font-size:0.85rem; padding: 10px 16px;">Configuración avanzada</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="wan-physical-tab" data-bs-toggle="tab"
                                    data-bs-target="#wan-physical" type="button" role="tab"
                                    style="font-size:0.85rem; padding: 10px 16px;">Configuración física</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="wan-firewall-tab" data-bs-toggle="tab"
                                    data-bs-target="#wan-firewall" type="button" role="tab"
                                    style="font-size:0.85rem; padding: 10px 16px;">Configuración del cortafuegos</button>
                            </li>
                        </ul>

                        <div class="tab-content p-4" id="editTabsContentWAN">
                            <div class="tab-pane fade show active" id="wan-general" role="tabpanel">

                                <div class="row align-items-start mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Estado</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="d-inline-flex p-3"
                                            style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-soft); border-radius: 8px;">
                                            <div class="me-2 text-center">
                                                <i class="bi bi-hdd-network" style="font-size: 1.5rem; color: #cfcfcf;"></i>
                                            </div>
                                            <div style="font-size: 0.8rem; color: var(--text-main); line-height: 1.3;">
                                                <strong>Dispositivo: eth0.2</strong><br>
                                                MAC: <span style="font-weight: 400;">28:EE:52:29:4C:DF</span><br>
                                                RX: <span style="font-weight: 400;">0 B (0 Paq.)</span><br>
                                                TX: <span style="font-weight: 400;">1.00 MB (2927 Paq.)</span>
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
                                        <select class="form-select w-50">
                                            <option value="dhcp" selected>Cliente DHCP</option>
                                            <option value="unmanaged">No administrado</option>
                                            <option value="ppp">PPP</option>
                                            <option value="pppoe">PPPoE</option>
                                            <option value="static">Dirección estática</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Iniciar en
                                            el arranque</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-check m-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>

                                <div class="row align-items-baseline mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Nombre del
                                            host a enviar cuando se solicite una IP</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50" value="NuupNet">
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane fade" id="wan-advanced" role="tabpanel">

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Utilizar
                                            la gestión integrada de IPv6</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-check m-0">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Forzar
                                            enlace</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-check m-0 mb-1">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                        <small style="color: var(--text-muted); font-size: 0.75rem;">Configura las
                                            propiedades de la interfaz independientemente del operador de enlace (si está
                                            configurado, los eventos de detección de operador no invocan los controladores
                                            de conexión en caliente).</small>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Usar marca
                                            de difusión</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-check m-0 mb-1">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                        <small style="color: var(--text-muted); font-size: 0.75rem;">Requerido para ciertos
                                            ISPs, por ejemplo Charter con DOCSIS 3</small>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Utilizar
                                            la puerta de enlace predeterminada</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-check m-0 mb-1">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                        <small style="color: var(--text-muted); font-size: 0.75rem;">Si no está marcado, no
                                            se configurará ninguna ruta predeterminada</small>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Usar los
                                            servidores predeterminados</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-check m-0 mb-1">
                                            <input class="form-check-input" type="checkbox" checked>
                                        </div>
                                        <small style="color: var(--text-muted); font-size: 0.75rem;">Si no está marcado, las
                                            direcciones anunciadas del servidor DNS se ignoran</small>
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Usar
                                            métrica de puerta de enlace</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="wan_metric"
                                            class="form-control w-50 @error('wan_metric', 'updateWan') is-invalid @enderror"
                                            value="{{ old('wan_metric', '0') }}">
                                        @error('wan_metric', 'updateWan')
                                            <div class="invalid-feedback text-start mt-1"
                                                style="font-size: 0.8rem; display: block;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">ID de
                                            cliente que se enviará al solicitar DHCP</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50">
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Clase de
                                            vendedor a enviar cuando solicite DHCP</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control w-50">
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Reemplazar
                                            dirección MAC</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="wan_mac"
                                            class="form-control w-50 @error('wan_mac', 'updateWan') is-invalid @enderror"
                                            value="{{ old('wan_mac', '28:EE:52:29:4C:DF') }}">
                                        @error('wan_mac', 'updateWan')
                                            <div class="invalid-feedback text-start mt-1"
                                                style="font-size: 0.8rem; display: block;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Reemplazar
                                            MTU</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="wan_mtu"
                                            class="form-control w-50 @error('wan_mtu', 'updateWan') is-invalid @enderror"
                                            value="{{ old('wan_mtu', '1500') }}">
                                        @error('wan_mtu', 'updateWan')
                                            <div class="invalid-feedback text-start mt-1"
                                                style="font-size: 0.8rem; display: block;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane fade" id="wan-physical" role="tabpanel">

                                <div class="row align-items-center mb-4">
                                    <div class="col-md-3 text-md-end pt-1">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Puentear
                                            interfaces</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-check m-0 mb-1">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                        <small style="color: var(--text-muted); font-size: 0.75rem;">Crea un puente sobre la
                                            interfaz o interfaces asociadas</small>
                                    </div>
                                </div>

                                <div class="row align-items-start mb-4">
                                    <div class="col-md-3 text-md-end pt-2">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Interfaz</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="dropdown">
                                            <button
                                                class="form-select text-start d-flex align-items-center justify-content-between p-1 w-75"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                style="min-height: 38px; color: var(--text-main); background-color: var(--card-bg, #2b2b2b);">
                                                <div class="d-flex align-items-center gap-2 px-2">
                                                    <i class="bi bi-hdd-network" style="color: #cfcfcf;"></i> eth0.2
                                                </div>
                                            </button>
                                            <ul class="dropdown-menu w-75 shadow p-0"
                                                style="background-color: var(--card-bg, #2b2b2b); border: 1px solid var(--border-soft);">
                                                <li class="dropdown-item px-3 py-1" style="color: var(--text-main);">
                                                    <div class="form-check m-0 d-flex align-items-center gap-2">
                                                        <input class="form-check-input m-0 rounded-circle" type="radio"
                                                            name="wanInterfaceRadio">
                                                        <i class="bi bi-diagram-3"
                                                            style="color: #cfcfcf; font-size: 0.9rem;"></i>
                                                        <span style="font-size: 0.85rem;">Puente: "br-lan" (lan)</span>
                                                    </div>
                                                </li>
                                                <li class="dropdown-item px-3 py-1" style="color: var(--text-main);">
                                                    <div class="form-check m-0 d-flex align-items-center gap-2">
                                                        <input class="form-check-input m-0 rounded-circle" type="radio"
                                                            name="wanInterfaceRadio">
                                                        <i class="bi bi-hdd-network"
                                                            style="color: #cfcfcf; font-size: 0.9rem;"></i>
                                                        <span style="font-size: 0.85rem;">Conmutador ethernet: "eth0"</span>
                                                    </div>
                                                </li>
                                                <li class="dropdown-item px-3 py-1" style="color: var(--text-main);">
                                                    <div class="form-check m-0 d-flex align-items-center gap-2">
                                                        <input class="form-check-input m-0 rounded-circle" type="radio"
                                                            name="wanInterfaceRadio">
                                                        <i class="bi bi-hdd-network"
                                                            style="color: #cfcfcf; font-size: 0.9rem;"></i>
                                                        <span style="font-size: 0.85rem;">Switch VLAN: "eth0.1" (lan)</span>
                                                    </div>
                                                </li>
                                                <li class="dropdown-item px-3 py-1"
                                                    style="background-color: rgba(51, 122, 183, 0.4); color: var(--text-main);">
                                                    <div class="form-check m-0 d-flex align-items-center gap-2">
                                                        <input class="form-check-input m-0 rounded-circle" type="radio"
                                                            name="wanInterfaceRadio" checked>
                                                        <i class="bi bi-hdd-network"
                                                            style="color: #cfcfcf; font-size: 0.9rem;"></i>
                                                        <span style="font-size: 0.85rem;">Switch VLAN: "eth0.2" (wan)</span>
                                                    </div>
                                                </li>
                                                <li class="dropdown-item px-3 py-1" style="color: var(--text-main);">
                                                    <div class="form-check m-0 d-flex align-items-center gap-2">
                                                        <input class="form-check-input m-0 rounded-circle" type="radio"
                                                            name="wanInterfaceRadio">
                                                        <i class="bi bi-wifi"
                                                            style="color: #5bc0de; font-size: 0.9rem;"></i>
                                                        <span style="font-size: 0.85rem;">Red Wi-Fi: Master "usuario_remoto"
                                                            (lan)</span>
                                                    </div>
                                                </li>
                                                <li class="dropdown-item px-3 py-1" style="color: var(--text-main);">
                                                    <div class="form-check m-0 d-flex align-items-center gap-2">
                                                        <input class="form-check-input m-0 rounded-circle" type="radio"
                                                            name="wanInterfaceRadio">
                                                        <i class="bi bi-person"
                                                            style="color: #cfcfcf; font-size: 0.9rem;"></i>
                                                        <span style="font-size: 0.85rem;">Apodo de interfaz: "@lan"</span>
                                                    </div>
                                                </li>
                                                <li class="dropdown-item px-3 py-1" style="color: var(--text-main);">
                                                    <div class="form-check m-0 d-flex align-items-center gap-2">
                                                        <input class="form-check-input m-0 rounded-circle" type="radio"
                                                            name="wanInterfaceRadio">
                                                        <i class="bi bi-person"
                                                            style="color: #cfcfcf; font-size: 0.9rem;"></i>
                                                        <span style="font-size: 0.85rem;">Apodo de interfaz: "@wan"</span>
                                                    </div>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider my-1"
                                                        style="border-color: var(--border-soft);">
                                                </li>
                                                <li class="dropdown-item px-3 py-2" style="color: var(--text-muted);">
                                                    <span style="font-size: 0.85rem; font-style: italic;">-- Personalizado
                                                        --</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="tab-pane fade" id="wan-firewall" role="tabpanel">

                                <div class="row align-items-start mb-4">
                                    <div class="col-md-3 text-md-end pt-2">
                                        <label class="form-label mb-0"
                                            style="color: var(--text-main); font-weight: 600; font-size: 0.9rem;">Crear /
                                            Asignar zona de cortafuegos</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="dropdown">
                                            <button
                                                class="form-select text-start d-flex align-items-center justify-content-between p-1 w-50"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                style="min-height: 38px; color: var(--text-main); background-color: var(--card-bg, #2b2b2b);">
                                                <div class="d-inline-flex align-items-center px-2 py-1"
                                                    style="background-color: #e59292; color: #6c2e2e; font-weight: bold; border-radius: 2px;">
                                                    wan
                                                    <div class="d-inline-flex align-items-center gap-1 ms-2"
                                                        style="background-color: #f8f9fa; border: 1px solid #ced4da; padding: 1px 4px; font-weight: normal; font-size: 0.8rem; border-radius: 2px;">
                                                        wan:
                                                        <i class="bi bi-hdd-network text-secondary"></i>
                                                    </div>
                                                </div>
                                            </button>
                                            <ul class="dropdown-menu shadow w-50 p-0"
                                                style="background-color: var(--card-bg, #2b2b2b); border: 1px solid var(--border-soft);">
                                                <li>
                                                    <a class="dropdown-item py-2 px-3" href="#"
                                                        style="color: var(--text-muted); font-style: italic; background: transparent;">
                                                        Sin especificar
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item p-1" href="#" style="background: transparent;">
                                                        <div class="d-inline-flex align-items-center px-2 py-1 w-100"
                                                            style="background-color: #92e592; color: #2e6c2e; font-weight: bold; border-radius: 2px;">
                                                            lan
                                                            <div class="d-inline-flex align-items-center gap-1 ms-2"
                                                                style="background-color: #f8f9fa; border: 1px solid #ced4da; padding: 1px 4px; font-weight: normal; font-size: 0.8rem; border-radius: 2px;">
                                                                lan:
                                                                <i class="bi bi-hdd-network text-secondary"></i>
                                                                <i class="bi bi-wifi text-secondary"></i>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li style="background-color: rgba(51, 122, 183, 0.4);">
                                                    <a class="dropdown-item p-1" href="#" style="background: transparent;">
                                                        <div class="d-inline-flex align-items-center px-2 py-1 w-100"
                                                            style="background-color: #e59292; color: #6c2e2e; font-weight: bold; border-radius: 2px;">
                                                            wan
                                                            <div class="d-inline-flex align-items-center gap-1 ms-2"
                                                                style="background-color: #f8f9fa; border: 1px solid #ced4da; padding: 1px 4px; font-weight: normal; font-size: 0.8rem; border-radius: 2px;">
                                                                wan:
                                                                <i class="bi bi-hdd-network text-secondary"></i>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2 px-3" href="#"
                                                        style="color: var(--text-muted); background: transparent;">
                                                        -- Personalizado --
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <small class="d-block mt-2" style="color: var(--text-muted); font-size: 0.75rem;">
                                            Elija la zona del cortafuegos a la que quiere asignar esta interfaz. Seleccione
                                            <em>Sin especificar</em> para remover la interfaz de la zona asociada o rellene
                                            el campo <em>Personalizado</em> para definir una zona nueva a la que asignarla.
                                        </small>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="modal-footer"
                        style="border-top: 1px solid var(--border-soft); background: rgba(255,255,255,0.01);">
                        <button type="button" class="btn btn-sm" data-bs-dismiss="modal"
                            style="background:#e6e6e6; color:#333; font-weight:700; font-size:0.75rem; border-radius:2px; padding:6px 14px;">
                            DESCARTAR
                        </button>
                        <button type="submit" class="btn btn-sm"
                            style="background:#337ab7; color:white; font-weight:700; font-size:0.75rem; border:none; border-radius:2px; padding:6px 14px;">
                            GUARDAR
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if($errors->hasBag('createInterface'))
                var modalCrear = new bootstrap.Modal(document.getElementById('modalAgregarInterfaz'));
                modalCrear.show();
            @endif

                @if($errors->hasBag('updateLan'))
                    var modalLan = new bootstrap.Modal(document.getElementById('modalEditarInterfaz'));
                    modalLan.show();
                @endif

                @if($errors->hasBag('updateWan'))
                    var modalWan = new bootstrap.Modal(document.getElementById('modalEditarInterfazWAN'));
                    modalWan.show();
                @endif
        });
    </script>

@endsection