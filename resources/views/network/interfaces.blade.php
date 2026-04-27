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
                        @php
                            $bgColor = match($iface['variant'] ?? 'custom') {
                                'lan' => '#51a351',
                                'wan' => '#d9534f',
                                'error' => '#777',
                                default => '#337ab7',
                            };
                            $isUp = ($iface['status'] ?? 'down') === 'up';
                            $iconColor = $isUp ? '#5bc0de' : '#777';
                            $icon = ($iface['variant'] ?? 'custom') === 'wan' ? 'hdd-network' : 'diagram-3';
                        @endphp
                        <div class="d-flex flex-wrap align-items-center justify-content-between p-3"
                            style="border-bottom: 1px solid var(--border-soft); background: rgba(255,255,255,0.02);">
                            <div class="d-flex align-items-center gap-5" style="width: 60%;">

                                <div class="text-center" style="width: 100px;">
                                    <div class="mb-1"
                                        style="background-color: {{ $bgColor }}; color: white; padding: 4px; font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">
                                        {{ $iface['name'] ?? 'N/A' }}
                                    </div>
                                    <div>
                                        <i class="bi bi-{{ $icon }}" style="font-size: 1.2rem; color: {{ $iconColor }};"></i>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-soft);">{{ $iface['device'] ?? 'N/A' }}
                                    </div>
                                </div>

                                <div style="font-size: 0.8rem; color: var(--text-main); font-weight: 600; line-height: 1.3;">
                                    @if(isset($iface['error']) && $iface['error'])
                                        <span style="font-weight: 400; color: #a94442;"><i class="bi bi-exclamation-triangle-fill"></i> {{ $iface['error'] }}</span>
                                    @elseif($isUp)
                                        Protocolo: <span style="font-weight: 400;">{{ $iface['protocol'] }}</span><br>
                                        Tiempo de actividad: <span style="font-weight: 400;">{{ $iface['uptime'] }}</span><br>
                                        MAC: <span style="font-weight: 400;">{{ $iface['mac'] }}</span><br>
                                        @php
                                            $rx = $iface['rx_bytes'] ?? 0;
                                            $tx = $iface['tx_bytes'] ?? 0;
                                            $rxStr = $rx > 1048576 ? round($rx / 1048576, 2) . ' MB' : round($rx / 1024, 2) . ' KB';
                                            $txStr = $tx > 1048576 ? round($tx / 1048576, 2) . ' MB' : round($tx / 1024, 2) . ' KB';
                                        @endphp
                                        RX: <span style="font-weight: 400;">{{ $rxStr }} ({{ $iface['rx_packets'] ?? 0 }} Paq.)</span><br>
                                        TX: <span style="font-weight: 400;">{{ $txStr }} ({{ $iface['tx_packets'] ?? 0 }} Paq.)</span><br>
                                        @if(!empty($iface['ipv4']))
                                            IPv4: <span style="font-weight: 400;">{{ $iface['ipv4'] }}</span>
                                        @endif
                                        @if(!empty($iface['ipv6']))
                                            <br>IPv6: <span style="font-weight: 400;">{{ $iface['ipv6'] }}</span>
                                        @endif
                                    @else
                                        <span style="font-weight: 400; color: #a94442;">La interfaz está detenida</span>
                                    @endif
                                </div>

                            </div>

                            <div class="d-flex gap-2 mt-3 mt-md-0">
                                <form action="{{ route('red.interfaces.restart', ['name' => $iface['name'] ?? 'error']) }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    <button class="btn btn-sm" type="submit"
                                        style="background: #e6e6e6; color: #333; font-weight: 700; font-size: 0.75rem; border-radius: 2px;">REINICIAR</button>
                                </form>

                                <form action="{{ route('red.interfaces.stop', ['name' => $iface['name'] ?? 'error']) }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    <button class="btn btn-sm" type="submit"
                                        style="background: #e6e6e6; color: {{ $isUp ? '#333' : '#b3b3b3' }}; font-weight: 700; font-size: 0.75rem; border-radius: 2px;"
                                        {{ $isUp ? '' : 'disabled' }}>DETENER</button>
                                </form>

                                @php
                                    $lowerName = strtolower($iface['name'] ?? '');
                                    $uConf = $uciConfig[$lowerName] ?? [];
                                    
                                    $dnsRaw = $uConf['dns'] ?? '';
                                    $dnsList = is_array($dnsRaw) ? implode(' ', $dnsRaw) : (string)$dnsRaw;
                                    
                                    $ipv4Raw = $iface['ipv4'] ?? '';
                                    $formattedIpv4 = !empty($ipv4Raw) ? (is_array($ipv4Raw) ? implode(', ', $ipv4Raw) : (string)$ipv4Raw) : '--';

                                    // Match DHCP
                                    $dhcpConf = $uciDhcpConfig[$lowerName] ?? [];
                                    
                                    // Match Firewall
                                    $fzMatched = '';
                                    if(isset($uciFirewallZones) && is_array($uciFirewallZones)) {
                                        foreach($uciFirewallZones as $zone) {
                                            $netRaw = $zone['network'] ?? [];
                                            $networks = is_array($netRaw) ? $netRaw : (empty($netRaw) ? [] : explode(' ', (string)$netRaw));
                                            
                                            if(in_array($lowerName, $networks)) {
                                                $fzMatched = $zone['name'] ?? '';
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <button class="btn btn-sm btn-editar-interfaz" data-bs-toggle="modal"
                                    data-bs-target="#modalEditarInterfaz"
                                    onclick="cargarModalEdicion(this)"
                                    data-raw="{{ $iface['name'] ?? '' }}"
                                    data-name="{{ $lowerName }}"
                                    data-mac="{{ $iface['mac'] ?? '--' }}"
                                    data-rx="{{ $iface['rx_bytes'] ?? '0' }}"
                                    data-tx="{{ $iface['tx_bytes'] ?? '0' }}"
                                    data-ipv4="{{ $formattedIpv4 }}"
                                    data-dev="{{ $iface['device'] ?? '--' }}"
                                    data-proto="{{ $uConf['proto'] ?? 'static' }}"
                                    data-auto="{{ isset($uConf['auto']) ? $uConf['auto'] : '1' }}"
                                    data-ipaddr="{{ $uConf['ipaddr'] ?? '' }}"
                                    data-netmask="{{ $uConf['netmask'] ?? '' }}"
                                    data-gateway="{{ $uConf['gateway'] ?? '' }}"
                                    data-broadcast="{{ $uConf['broadcast'] ?? '' }}"
                                    data-dns="{{ $dnsList }}"
                                    data-ip6assign="{{ $uConf['ip6assign'] ?? '' }}"
                                    data-ip6addr="{{ $uConf['ip6addr'] ?? '' }}"
                                    data-ip6gw="{{ $uConf['ip6gw'] ?? '' }}"
                                    data-ip6prefix="{{ $uConf['ip6prefix'] ?? '' }}"
                                    data-ip6ifaceid="{{ $uConf['ip6ifaceid'] ?? '' }}"
                                    data-metric="{{ $uConf['metric'] ?? '' }}"
                                    data-macaddr="{{ $uConf['macaddr'] ?? '' }}"
                                    data-mtu="{{ $uConf['mtu'] ?? '' }}"
                                    data-delegate="{{ $uConf['delegate'] ?? '1' }}"
                                    data-force_link="{{ $uConf['force_link'] ?? '1' }}"
                                    data-username="{{ $uConf['username'] ?? '' }}"
                                    data-password="{{ $uConf['password'] ?? '' }}"
                                    data-ac="{{ $uConf['ac'] ?? '' }}"
                                    data-service="{{ $uConf['service'] ?? '' }}"
                                    data-defaultroute="{{ $uConf['defaultroute'] ?? '1' }}"
                                    data-peerdns="{{ $uConf['peerdns'] ?? '1' }}"
                                    data-clientid="{{ $uConf['clientid'] ?? '' }}"
                                    data-vendorid="{{ $uConf['vendorid'] ?? '' }}"
                                    data-lcp-failure="{{ $uConf['lcp_echo_failure'] ?? '' }}"
                                    data-lcp-interval="{{ $uConf['lcp_echo_interval'] ?? '' }}"
                                    data-demand="{{ $uConf['demand'] ?? '' }}"
                                    data-firewall-zone="{{ $fzMatched }}"
                                    data-dhcp-ignore="{{ $dhcpConf['ignore'] ?? '0' }}"
                                    data-dhcp-start="{{ $dhcpConf['start'] ?? '' }}"
                                    data-dhcp-limit="{{ $dhcpConf['limit'] ?? '' }}"
                                    data-dhcp-leasetime="{{ $dhcpConf['leasetime'] ?? '' }}"
                                    data-dhcp-dynamic="{{ $dhcpConf['dynamic'] ?? '1' }}"
                                    style="background: #5bc0de; color: white; font-weight: 700; border: none; font-size: 0.75rem; border-radius: 2px;">EDITAR</button>

                                @php
                                    $isCritical = in_array(strtolower($iface['name'] ?? ''), ['lan', 'wan', 'br-lan']);
                                    $confirmScript = "return confirm('¿Está seguro que desea eliminar la interfaz " . ($iface['name'] ?? 'desconocida') . "? Esta acción borrará su configuración en el router real y no se puede deshacer.');";
                                    if ($isCritical) {
                                        $confirmScript = "var res = prompt('ATENCIÓN: Está intentando eliminar una interfaz CRÍTICA (" . ($iface['name'] ?? '') . ") lo cual dañaría la conexión principal.\\n\\nEscriba la letra X para ignorar y forzar la eliminación de todos modos (No recomendado):'); if(res !== 'X'){ return false; } return true;";
                                    }
                                @endphp
                                <form action="{{ route('red.interfaces.destroy', ['name' => $iface['name'] ?? 'error']) }}" method="POST" class="m-0 p-0" onsubmit="{!! $confirmScript !!}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm" type="submit"
                                        style="background: #d9534f; color: white; font-weight: 700; border: none; font-size: 0.75rem; border-radius: 2px;">ELIMINAR</button>
                                </form>
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
                <form method="POST" action="{{ route('red.interfaces.store') }}">
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
                                        {{ $message }}
                                    </div>
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
                                        {{ $message }}
                                    </div>
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
                                    @if(isset($devices))
                                        @foreach($devices as $dev)
                                            <option value="{{ $dev }}" {{ old('interface') == $dev ? 'selected' : '' }}>Dispositivo: "{{ $dev }}"</option>
                                        @endforeach
                                    @endif
                                    <option value="custom" {{ old('interface') == 'custom' ? 'selected' : '' }}>Personalizado</option>
                                </select>
                                @error('interface', 'createInterface')
                                    <div class="invalid-feedback text-start mt-1" style="font-size: 0.8rem; display: block;">
                                        {{ $message }}
                                    </div>
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

    {{-- Modal Editar Interfaz dinámico --}}
    <div class="modal fade" id="modalEditarInterfaz" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content"
                style="background: var(--card-bg); border: 1px solid var(--border-soft); border-radius: var(--radius-lg);">
                <div class="modal-header" style="border-bottom: 1px solid var(--border-soft);">
                    <h5 class="modal-title" style="font-weight:700; color:#e2eaff;" id="editModalTitle">
                        Interfaces » Editar
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="editForm">
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
                                @include('network.partials.tab-general')
                            </div>

                            <div class="tab-pane fade" id="advanced" role="tabpanel">
                                @include('network.partials.tab-advanced')
                            </div>

                            <div class="tab-pane fade" id="physical" role="tabpanel">
                                @include('network.partials.tab-physical')
                            </div>

                            <div class="tab-pane fade" id="firewall" role="tabpanel">
                                @include('network.partials.tab-firewall')
                            </div>

                            <div class="tab-pane fade" id="dhcp" role="tabpanel">
                                @include('network.partials.tab-dhcp')
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
                <form method="POST" action="{{ route('red.interfaces.wan.update') }}">
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
        const dhcpConfigs = @json($uciDhcpConfig);
        const uciConfigs = @json($uciConfig);
        
        document.addEventListener('DOMContentLoaded', function () {
            @if($errors->hasBag('createInterface'))
                var modalCrear = new bootstrap.Modal(document.getElementById('modalAgregarInterfaz'));
                modalCrear.show();
            @endif

            @if(session('reopen_modal'))
                var reopenName = "{{ session('reopen_modal') }}";
                var modalInstance = new bootstrap.Modal(document.getElementById('modalEditarInterfaz'));
                var btn = document.querySelector('.btn-editar-interfaz[data-name="' + reopenName + '"]');
                if (btn) {
                    cargarModalEdicion(btn);
                    modalInstance.show();
                }
            @endif
        });

        // Selectores de los contenedores
        const protoSelect = document.getElementById('editIfaceProto');
        const protoStaticFields = document.getElementById('protoStaticFields');
        const protoDhcpFields = document.getElementById('protoDhcpFields');
        const protoPppFields = document.getElementById('protoPppFields');
        const pppExtra = document.getElementById('pppExtra');
        const protoPppExtra = document.getElementById('pppExtra');
        const pppoeExtra = document.getElementById('pppoeExtra');
        const protoGenericAdvancedFields = document.getElementById('protoGenericAdvancedFields');
        const protoDhcpAdvancedFields = document.getElementById('protoDhcpAdvancedFields');
        const protoPppAdvancedFields = document.getElementById('protoPppAdvancedFields');
        
        // Pestañas enteras a ocultar
        const dhcpTab = document.getElementById('dhcp-tab');
        const dhcpTabContent = document.getElementById('dhcp');
        const physicalTab = document.getElementById('physical-tab');
        const physicalTabContent = document.getElementById('physical');

        if (protoSelect) {
            protoSelect.addEventListener('change', function() {
                toggleProtocolFields(this.value);
            });
        }

        function toggleProtocolFields(proto) {
            // Ocultar primero
            if (protoStaticFields) protoStaticFields.classList.add('d-none');
            if (protoDhcpFields) protoDhcpFields.classList.add('d-none');
            if (protoPppFields) protoPppFields.classList.add('d-none');
            if (pppExtra) pppExtra.classList.add('d-none');
            if (pppoeExtra) pppoeExtra.classList.add('d-none');
            if (protoGenericAdvancedFields) protoGenericAdvancedFields.classList.add('d-none');
            if (protoDhcpAdvancedFields) protoDhcpAdvancedFields.classList.add('d-none');
            if (protoPppAdvancedFields) protoPppAdvancedFields.classList.add('d-none');

            // Deshabilitar todos los inputs de los bloques condicionales
            [protoStaticFields, protoDhcpFields, protoPppFields, protoGenericAdvancedFields, protoDhcpAdvancedFields, protoPppAdvancedFields].forEach(container => {
                if(container) {
                    container.querySelectorAll('input, select').forEach(el => el.disabled = true);
                }
            });

            // Ocultar pestaña completa Servidor DHCP en caso de PPP, PPPoE o DHCP
            if (dhcpTab && dhcpTabContent) {
                if (proto === 'dhcp' || proto === 'ppp' || proto === 'pppoe') {
                    dhcpTab.parentElement.classList.add('d-none');
                    dhcpTabContent.querySelectorAll('input, select').forEach(el => el.disabled = true);
                    // Si la pestaña estaba activa, mover a general
                    if (dhcpTab.classList.contains('active')) {
                        var generalTab = new bootstrap.Tab(document.getElementById('general-tab'));
                        generalTab.show();
                    }
                } else {
                    dhcpTab.parentElement.classList.remove('d-none');
                    dhcpTabContent.querySelectorAll('input, select').forEach(el => el.disabled = false);
                }
            }

            // Ocultar pestaña Física para PPP y PPPoE
            if (physicalTab && physicalTabContent) {
                if (proto === 'ppp' || proto === 'pppoe') {
                    physicalTab.parentElement.classList.add('d-none');
                    physicalTabContent.querySelectorAll('input, select').forEach(el => el.disabled = true);
                    if (physicalTab.classList.contains('active')) {
                        var generalTab = new bootstrap.Tab(document.getElementById('general-tab'));
                        generalTab.show();
                    }
                } else {
                    physicalTab.parentElement.classList.remove('d-none');
                    physicalTabContent.querySelectorAll('input, select').forEach(el => el.disabled = false);
                }
            }

            // Activar solo el bloque corespondiente
            if (proto === 'static') {
                if (protoStaticFields) {
                    protoStaticFields.classList.remove('d-none');
                    protoStaticFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
                }
                if (protoGenericAdvancedFields) {
                    protoGenericAdvancedFields.classList.remove('d-none');
                    protoGenericAdvancedFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
                }
            } else if (proto === 'dhcp') {
                if (protoDhcpFields) {
                    protoDhcpFields.classList.remove('d-none');
                    protoDhcpFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
                }
                if (protoDhcpAdvancedFields) {
                    protoDhcpAdvancedFields.classList.remove('d-none');
                    protoDhcpAdvancedFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
                }
            } else if (proto === 'ppp') {
                if (protoPppFields) {
                    protoPppFields.classList.remove('d-none');
                    protoPppFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
                    if (pppExtra) {
                        pppExtra.classList.remove('d-none');
                        pppExtra.querySelectorAll('input, select').forEach(el => el.disabled = false);
                    }
                }
                if (protoPppAdvancedFields) {
                    protoPppAdvancedFields.classList.remove('d-none');
                    protoPppAdvancedFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
                }
            } else if (proto === 'pppoe') {
                if (protoPppFields) {
                    protoPppFields.classList.remove('d-none');
                    protoPppFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
                    if (pppoeExtra) {
                        pppoeExtra.classList.remove('d-none');
                        pppoeExtra.querySelectorAll('input, select').forEach(el => el.disabled = false);
                    }
                }
                if (protoGenericAdvancedFields) {
                    protoGenericAdvancedFields.classList.remove('d-none');
                    protoGenericAdvancedFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
                }
                if (protoPppAdvancedFields) {
                    protoPppAdvancedFields.classList.remove('d-none');
                    protoPppAdvancedFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
                }
            }
        }

        function cargarModalEdicion(btn) {
            var rawName = btn.getAttribute('data-raw');
            
            document.getElementById('editForm').action = '/red/interfaces/' + rawName + '/update';
            document.getElementById('editModalTitle').innerText = 'Interfaces » ' + rawName.toUpperCase();
            
            document.getElementById('editIfaceDev').innerText = btn.getAttribute('data-dev') || '--';
            document.getElementById('editIfaceMac').innerText = btn.getAttribute('data-mac') || '--';
            document.getElementById('editIfaceRx').innerText = btn.getAttribute('data-rx') || '0';
            document.getElementById('editIfaceTx').innerText = btn.getAttribute('data-tx') || '0';
            document.getElementById('editIfaceIpv4').innerText = btn.getAttribute('data-ipv4') || '--';
            
            var proto = btn.getAttribute('data-proto') || 'static';
            if (protoSelect) {
                 protoSelect.value = proto;
                 toggleProtocolFields(proto);
            }

            var autoInput = document.getElementById('editIfaceAuto');
            if (autoInput) autoInput.checked = (btn.getAttribute('data-auto') !== '0');
            
            // Set input values for static
            setInputValue('editIfaceIpaddr', btn.getAttribute('data-ipaddr'));
            setInputValue('editIfaceGateway', btn.getAttribute('data-gateway'));
            setInputValue('editIfaceBroadcast', btn.getAttribute('data-broadcast'));
            setInputValue('editIfaceDns', btn.getAttribute('data-dns'));
            setInputValue('editIfaceIp6assign', btn.getAttribute('data-ip6assign'));
            setInputValue('editIfaceIp6addr', btn.getAttribute('data-ip6addr'));
            setInputValue('editIfaceIp6gw', btn.getAttribute('data-ip6gw'));
            setInputValue('editIfaceIp6prefix', btn.getAttribute('data-ip6prefix'));
            setInputValue('editIfaceIp6ifaceid', btn.getAttribute('data-ip6ifaceid'));
            
            // DHCP Advanced
            setCheckboxValue('editIfaceBroadcastAdvanced', btn.getAttribute('data-broadcast'));
            setCheckboxValue('editIfaceDefaultroute', btn.getAttribute('data-defaultroute'));
            setCheckboxValue('editIfacePeerdnsAdvanced', btn.getAttribute('data-peerdns'));
            setInputValue('editIfaceClientid', btn.getAttribute('data-clientid'));
            setInputValue('editIfaceVendorid', btn.getAttribute('data-vendorid'));
            
            // DHCP specific overlapping fields
            setInputValue('editIfaceMetricDhcp', btn.getAttribute('data-metric'));
            setCheckboxValue('editIfaceDelegateDhcp', btn.getAttribute('data-delegate'), '1');
            setCheckboxValue('editIfaceForceLinkDhcp', btn.getAttribute('data-force_link'), '1');
            setInputValue('editIfaceMacaddrDhcp', btn.getAttribute('data-macaddr'));
            setInputValue('editIfaceMtuDhcp', btn.getAttribute('data-mtu'));
            
            // PPP Advanced
            var uConf = uciConfigs[lowerName] || {};
            setCheckboxValue('editIfaceDelegatePpp', uConf['delegate'], '1');
            setCheckboxValue('editIfaceForceLinkPpp', uConf['force_link'], '1');
            setCheckboxValue('editIfaceDefaultroutePpp', uConf['defaultroute'], '1');
            setCheckboxValue('editIfacePeerdnsPpp', uConf['peerdns'], '1');
            setInputValue('editIfaceMetricPpp', uConf['metric']);
            setInputValue('editIfaceLcpEchoFailure', uConf['lcp_echo_failure']);
            setInputValue('editIfaceLcpEchoInterval', uConf['lcp_echo_interval']);
            setInputValue('editIfaceDemand', uConf['demand']);
            setInputValue('editIfaceMtuPpp', uConf['mtu']);
            
            var netmask = btn.getAttribute('data-netmask') || '';
            var netSelect = document.getElementById('editIfaceNetmask');
            var customInp = document.getElementById('editIfaceNetmaskCustom');
            if (netSelect && customInp) {
                var foundNet = Array.from(netSelect.options).some(opt => opt.value === netmask);
                if (!foundNet && netmask) {
                    netSelect.value = 'custom';
                    customInp.classList.remove('d-none');
                    customInp.value = netmask;
                } else {
                    netSelect.value = netmask;
                    customInp.classList.add('d-none');
                }
            }

            // Firewall Zone logic
            var fz = btn.getAttribute('data-firewall-zone') || '';
            var fzSelect = document.getElementById('editIfaceFirewallZone');
            var fzCustomInp = document.getElementById('editIfaceFirewallZoneCustom');
            if (fzSelect && fzCustomInp) {
                var foundFz = Array.from(fzSelect.options).some(opt => opt.value === fz);
                if (!foundFz && fz) {
                    fzSelect.value = 'custom';
                    fzCustomInp.classList.remove('d-none');
                    fzCustomInp.value = fz;
                } else {
                    fzSelect.value = fz;
                    fzCustomInp.classList.add('d-none');
                }
            }
            if(fzSelect) {
                fzSelect.onchange = function() {
                    fzCustomInp.classList.toggle('d-none', this.value !== 'custom');
                };
            }
            
            // DHCP/Advanced properties defaults mapping...
            setCheckboxValue('editIfaceDhcpIgnore', btn.getAttribute('data-dhcp-ignore'));
            setInputValue('editIfaceDhcpStart', btn.getAttribute('data-dhcp-start'));
            setInputValue('editIfaceDhcpLimit', btn.getAttribute('data-dhcp-limit'));
            setInputValue('editIfaceDhcpLeasetime', btn.getAttribute('data-dhcp-leasetime'));
            setCheckboxValue('editIfaceDhcpDynamic', btn.getAttribute('data-dhcp-dynamic'), '1'); // Default to 1 usually

            // Extended DHCP Server settings (Advanced & IPv6)
            var lowerName = btn.getAttribute('data-name');
            var uciDhcp = dhcpConfigs[lowerName] || {};
            setCheckboxValue('editIfaceDhcpForce', uciDhcp['force'], '1');
            setInputValue('editIfaceDhcpNetmask', uciDhcp['dhcp_netmask'] || uciDhcp['netmask']);
            
            var dhcpOpt = uciDhcp['dhcp_option'] || '';
            setInputValue('editIfaceDhcpOptions', Array.isArray(dhcpOpt) ? dhcpOpt.join(' ') : dhcpOpt);
            
            setInputValue('editIfaceDhcpRa', uciDhcp['ra']);
            setInputValue('editIfaceDhcpDhcpv6', uciDhcp['dhcpv6']);
            setInputValue('editIfaceDhcpNdp', uciDhcp['ndp']);
            
            var dnsList = uciDhcp['dns'] || '';
            setInputValue('editIfaceDhcpDns', Array.isArray(dnsList) ? dnsList.join(' ') : dnsList);
            
            var domainList = uciDhcp['domain'] || '';
            setInputValue('editIfaceDhcpDomain', Array.isArray(domainList) ? domainList.join(' ') : domainList);

            // PPP inputs
            setInputValue('editIfaceUsername', uConf['username']);
            setInputValue('editIfacePassword', uConf['password']);
            setInputValue('editIfaceAc', uConf['ac']);
            setInputValue('editIfaceService', uConf['service']);
            setInputValue('editIfaceModemDev', uConf['device']);

            // Adv/Physical
            setInputValue('editIfaceMetric', uConf['metric']);
            setInputValue('editIfaceMacaddr', uConf['macaddr']);
            setInputValue('editIfaceMtu', uConf['mtu']);
            setCheckboxValue('editIfaceDelegate', uConf['delegate'], '1');
            setCheckboxValue('editIfaceForceLink', uConf['force_link'], '1');
        }

        function setInputValue(id, val) {
            var el = document.getElementById(id);
            if (el) el.value = val || '';
        }

        function setCheckboxValue(id, val, checkOnVal = '1') {
            var el = document.getElementById(id);
            if (el) el.checked = (val === checkOnVal);
        }
    </script>

@endsection