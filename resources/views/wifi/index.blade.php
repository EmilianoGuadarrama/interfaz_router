@extends('layouts.dashboard')

@section('title', 'Wi-Fi')
@section('page-title', 'Wi-Fi')

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show bg-success text-white border-0" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show bg-danger text-white border-0" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="panel-card mb-4 p-0 bg-dark shadow-sm" style="border: 1px solid var(--secondary);">
        <div class="p-4 border-bottom border-secondary d-flex align-items-center">
            <h4 class="m-0 text-white fw-normal fs-5"><i class="bi bi-wifi me-2"></i>Vista general de Wi-Fi</h4>
        </div>

        <div class="list-group list-group-flush bg-transparent">
            @if(isset($wifi['data']['radios']) && count($wifi['data']['radios']) > 0)
                @foreach($wifi['data']['radios'] as $radio)
                    <div class="list-group-item bg-transparent border-secondary py-3 text-white">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-auto text-center mb-3 mb-md-0 d-flex justify-content-center align-items-center"
                                style="width: 80px;">
                                <span class="badge border border-secondary p-2 d-inline-block text-center shadow-sm"
                                    style="background: rgba(255,255,255,0.05); min-width: 60px;">
                                    <i class="bi bi-broadcast text-info" style="font-size: 1.2rem;"></i><br>
                                    <small class="text-muted fw-normal"
                                        style="font-size: 0.75rem;">{{ $radio['id'] ?? 'radio0' }}</small>
                                </span>
                            </div>
                            <div class="col-12 col-md mb-3 mb-md-0 text-center text-md-start">
                                <div class="fw-bold fs-6">MediaTek MT76x8 {{ $radio['hwmode'] ?? '802.11bgn' }}</div>
                                <div class="text-soft small fw-bold">Canal: <span class="fw-normal">{{ $radio['channel'] ?? '?' }}
                                        (2.462 GHz)</span> <span class="text-muted mx-1">|</span> Tasa de bits: <span
                                        class="fw-normal">? Mbit/s</span></div>
                            </div>
                            <div class="col-12 col-lg-auto d-flex gap-2 justify-content-center">
                                <form action="{{ route('red.wifi.restart') }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary btn-sm fw-bold text-dark px-3"
                                        style="background: #e0e0e0; font-size:0.75rem;"
                                        onclick="return confirm('¿Seguro que deseas reiniciar la red Wi-Fi? Perderás la conexión momentáneamente.')">REINICIAR</button>
                                </form>
                                <button type="button" class="btn btn-sm text-white fw-bold px-3"
                                    style="background: #3eb2cc; font-size:0.75rem;" id="btnScanWifi">ESCANEAR</button>
                                <button type="button" class="btn btn-primary btn-sm fw-bold px-3"
                                    style="background: #397cbd; font-size:0.75rem;" data-bs-toggle="modal"
                                    data-bs-target="#addWifiModal" data-device="{{ $radio['id'] ?? 'radio0' }}">AÑADIR</button>
                            </div>
                        </div>
                    </div>

                    <!-- Interfaces asociadas a este radio -->
                    @if(isset($wifi['data']['interfaces']))
                        @foreach($wifi['data']['interfaces'] as $interface)
                            @if(($interface['device'] ?? 'radio0') === ($radio['id'] ?? 'radio0'))
                                <div class="list-group-item border-secondary py-3 text-white"
                                    style="background-color: rgba(255,255,255,0.02) !important;">
                                    <div class="row align-items-center">
                                        <div class="col-12 col-md-auto text-center mb-3 mb-md-0 d-flex justify-content-center align-items-center"
                                            style="width: 80px;">
                                            <span class="badge border border-secondary p-2 d-inline-block text-center shadow-sm"
                                                style="background: rgba(255,255,255,0.05); min-width: 60px;">
                                                <i class="bi bi-bar-chart-fill text-muted" style="font-size: 1.2rem;"></i><br>
                                                <small class="text-muted fw-normal" style="font-size: 0.75rem;">-- dBm</small>
                                            </span>
                                        </div>
                                        <div class="col-12 col-md mb-3 mb-md-0 text-center text-md-start">
                                            <div class="fw-bold fs-6">SSID: {{ $interface['ssid'] ?? '?' }} <span
                                                    class="fw-normal text-muted mx-1">|</span> Modo: <span
                                                    class="fw-normal">{{ ucfirst($interface['mode'] ?? '?') }}</span></div>
                                            <div class="text-soft small fw-bold">BSSID: <span
                                                    class="fw-normal">{{ $interface['bssid'] ?? '98:BA:5F:C5:XX:XX' }}</span> <span
                                                    class="text-muted mx-1">|</span> Encriptación: <span
                                                    class="fw-normal">{{ $interface['encryption'] ?? '-' }}</span></div>
                                        </div>
                                        <div class="col-12 col-lg-auto d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-secondary btn-sm fw-bold text-dark px-3"
                                                style="background: #e0e0e0; font-size:0.75rem;">DESACTIVAR</button>
                                            <button type="button" class="btn btn-sm text-white fw-bold px-3 btnEditInterface"
                                                style="background: #3eb2cc; font-size:0.75rem;" data-bs-toggle="modal"
                                                data-bs-target="#editWifiModal" data-id="{{ $interface['id'] }}"
                                                data-ssid="{{ $interface['ssid'] ?? '' }}" data-mode="{{ $interface['mode'] ?? 'ap' }}"
                                                data-network="{{ $interface['network'] ?? 'lan' }}"
                                                data-hidden="{{ $interface['hidden'] ?? '0' }}" data-wmm="{{ $interface['wmm'] ?? '1' }}"
                                                data-encryption="{{ $interface['encryption'] ?? 'none' }}"
                                                data-key="{{ $interface['key'] ?? '' }}"
                                                data-macfilter="{{ $interface['macfilter'] ?? 'disable' }}"
                                                data-maclist="{{ isset($interface['maclist']) ? (is_array($interface['maclist']) ? implode('\n', $interface['maclist']) : str_replace(' ', '\n', $interface['maclist'])) : '' }}">EDITAR</button>
                                            <form action="{{ route('red.wifi.delete') }}" method="POST" class="m-0 p-0">
                                                @csrf
                                                <input type="hidden" name="interface_id" value="{{ $interface['id'] }}">
                                                <button type="submit" class="btn btn-danger btn-sm fw-bold px-3"
                                                    style="background: #db4444; font-size:0.75rem;"
                                                    onclick="return confirm('¿Seguro que deseas eliminar esta interfaz ({{ $interface['ssid'] ?? $interface['id'] }})? Se removerá la configuración de la red.')">ELIMINAR</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @else
                <div class="p-4 text-center text-muted">No se encontró información de Wi-Fi en el router.</div>
            @endif
        </div>
    </div>

    <div class="panel-card mb-4 p-0 bg-dark shadow-sm" style="border: 1px solid var(--secondary);">
        <div class="p-4 border-bottom border-secondary d-flex align-items-center">
            <h4 class="m-0 text-white fw-normal fs-5"><i class="bi bi-laptop me-2"></i>Estaciones asociadas</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead class="border-secondary text-soft" style="font-size: 0.85rem;">
                    <tr>
                        <th class="py-3 px-4 fw-normal border-secondary">Red</th>
                        <th class="py-3 fw-normal border-secondary">Dirección MAC</th>
                        <th class="py-3 fw-normal border-secondary">Host / IPv4</th>
                        <th class="py-3 fw-normal border-secondary">Señal / Ruido</th>
                        <th class="py-3 fw-normal border-secondary">Tasa RX / TX</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($connectedDevices['data']) && count($connectedDevices['data']) > 0)
                        @foreach($connectedDevices['data'] as $device)
                            <tr class="align-middle">
                                <td class="px-4 border-secondary">
                                    <span class="badge bg-secondary p-2">{{ $device['network'] }}</span>
                                </td>
                                <td class="fw-bold border-secondary">{{ $device['mac'] }}</td>
                                <td class="border-secondary">
                                    <div class="fw-bold">{{ $device['hostname'] }}</div>
                                    <div class="small fw-normal text-muted">{{ $device['ip'] }}</div>
                                </td>
                                <td class="border-secondary">
                                    <div class="fw-bold text-white">{{ $device['signal'] }} dBm</div>
                                    <div class="small text-muted">SNR: {{ $device['snr'] }}</div>
                                </td>
                                <td class="border-secondary">
                                    <div class="fw-bold text-white">{{ $device['rx_tx'] }}</div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5 border-secondary">
                                <i class="bi bi-exclamation-circle text-muted fs-4 d-block mb-2"></i>
                                No hay estaciones conectadas a las interfaces inalambricas en este momento.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-end mt-4">
        <button type="button" class="btn btn-danger px-4">RESTABLECER</button>
        <button type="button" class="btn btn-secondary px-4">GUARDAR</button>
        <button type="button" class="btn btn-main px-4">GUARDAR Y APLICAR</button>
    </div>

    <!-- Edit WiFi Modal -->
    <div class="modal fade" id="editWifiModal" tabindex="-1" aria-labelledby="editWifiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="editWifiModalLabel"><i class="bi bi-pencil-square me-2"></i>Editar
                        Configuración Wi-Fi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <form action="{{ route('red.wifi.edit') }}" method="POST" id="formEditWifi">
                        @csrf
                        <input type="hidden" name="interface_id" id="editInterfaceName" value="">

                        <!-- Tabs Nav -->
                        <ul class="nav nav-tabs border-secondary bg-secondary bg-opacity-25" id="editWifiTabs"
                            role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active text-white border-secondary border-bottom-0"
                                    id="edit-general-tab" data-bs-toggle="tab" data-bs-target="#edit-general-pane"
                                    type="button" role="tab" aria-controls="edit-general-pane" aria-selected="true"
                                    style="background-color: transparent;">Configuración general</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-soft border-secondary border-bottom-0" id="edit-security-tab"
                                    data-bs-toggle="tab" data-bs-target="#edit-security-pane" type="button" role="tab"
                                    aria-controls="edit-security-pane" aria-selected="false"
                                    style="background-color: transparent;">Seguridad Wi-Fi</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-soft border-secondary border-bottom-0" id="edit-mac-tab"
                                    data-bs-toggle="tab" data-bs-target="#edit-mac-pane" type="button" role="tab"
                                    aria-controls="edit-mac-pane" aria-selected="false"
                                    style="background-color: transparent;">Filtro por MAC</button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content custom-tabs-content p-4" id="editWifiTabsContent">

                            <!-- Pestaña Configuración general -->
                            <div class="tab-pane fade show active" id="edit-general-pane" role="tabpanel"
                                aria-labelledby="edit-general-tab" tabindex="0">
                                <div class="row mb-3 align-items-center">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">Modo</label>
                                    <div class="col-sm-9">
                                        <select name="mode" id="editMode"
                                            class="form-select bg-dark text-white border-secondary">
                                            <option value="ap">Punto de acceso (AP)</option>
                                            <option value="sta">Cliente (STA)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3 align-items-center">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">ESSID</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="ssid" id="editSsid"
                                            class="form-control bg-dark text-white border-secondary" required>
                                    </div>
                                </div>

                                <div class="row mb-3 align-items-center">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">Red</label>
                                    <div class="col-sm-9">
                                        <select name="network" id="editNetwork"
                                            class="form-select bg-dark text-white border-secondary">
                                            <option value="lan">lan (Red local)</option>
                                            <option value="wan">wan (Red externa)</option>
                                            <option value="wwan">wwan (Modem/Wifi extendido)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3 align-items-center">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="hidden"
                                                id="editHiddenSSID">
                                            <label class="form-check-label text-soft" for="editHiddenSSID">
                                                Ocultar ESSID
                                            </label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="wmm" id="editActivateWMM">
                                            <label class="form-check-label text-soft" for="editActivateWMM">
                                                Activar WMM
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Seguridad Wi-Fi -->
                            <div class="tab-pane fade" id="edit-security-pane" role="tabpanel"
                                aria-labelledby="edit-security-tab" tabindex="0">
                                <div class="row mb-3 align-items-center">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">Encriptación</label>
                                    <div class="col-sm-9">
                                        <select name="encryption" id="editEncryption"
                                            class="form-select bg-dark text-white border-secondary">
                                            <option value="none">Sin encriptación (red abierta)</option>
                                            <option value="psk2">WPA2-PSK (seguridad fuerte)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3 align-items-center d-none" id="editPasswordContainer">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">Clave</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="password" id="editPassword"
                                            class="form-control bg-dark text-white border-secondary" minlength="8" disabled>
                                        <div class="form-text text-muted">La clave debe tener un mínimo de 8 caracteres.
                                            Nota: la contraseña se muestra en texto plano para tu conveniencia.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Filtro por MAC -->
                            <div class="tab-pane fade" id="edit-mac-pane" role="tabpanel" aria-labelledby="edit-mac-tab"
                                tabindex="0">
                                <div class="row mb-3 align-items-center">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">Filtro MAC</label>
                                    <div class="col-sm-9">
                                        <select name="macfilter" id="editMacFilter"
                                            class="form-select bg-dark text-white border-secondary">
                                            <option value="disable">Desactivar</option>
                                            <option value="allow">Permitir a los pertenecientes a la lista</option>
                                            <option value="deny">Permitir a todos excepto a los de la lista</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3 align-items-start d-none" id="editMacListContainer">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">Direcciones MAC</label>
                                    <div class="col-sm-9">
                                        <textarea name="maclist" id="editMacList"
                                            class="form-control bg-dark text-white border-secondary" rows="3"
                                            placeholder="Ej: 00:11:22:33:44:55&#10;AA:BB:CC:DD:EE:FF" disabled></textarea>
                                        <div class="form-text text-muted">Ingrese múltiples direcciones MAC separadas por
                                            salto de línea o coma.</div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-secondary fw-bold"
                                data-bs-dismiss="modal">DESCARTAR</button>
                            <button type="submit" class="btn btn-primary fw-bold" style="background-color: #397cbd;">GUARDAR
                                CONFIGURACIÓN</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scan WiFi Modal -->
    <div class="modal fade" id="scanWifiModal" tabindex="-1" aria-labelledby="scanWifiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="scanWifiModalLabel"><i class="bi bi-search me-2"></i>Redes WiFi disponibles
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead class="border-secondary">
                                <tr>
                                    <th>SSID / MAC</th>
                                    <th>Canal</th>
                                    <th>Señal / Calidad</th>
                                    <th>Seguridad</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="wifiScanResults">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Iniciando escaneo...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btnScan = document.getElementById('btnScanWifi');
            const modalScanElement = document.getElementById('scanWifiModal');
            let modalScan;

            if (modalScanElement) {
                modalScan = new bootstrap.Modal(modalScanElement);
            }

            const tableBody = document.getElementById('wifiScanResults');

            if (btnScan && modalScanElement) {
                btnScan.addEventListener('click', function () {
                    modalScan.show();

                    tableBody.innerHTML = `
                                                    <tr>
                                                        <td colspan="5" class="text-center py-4 text-soft border-0">
                                                            <div class="spinner-border spinner-border-sm me-2 text-primary" role="status"></div>
                                                            Obteniendo redes disponibles, por favor espera...
                                                        </td>
                                                    </tr>
                                                `;

                    fetch('{{ route('red.wifi.scan') }}')
                        .then(response => {
                            if (!response.ok) throw new Error('Network falló');
                            return response.json();
                        })
                        .then(data => {
                            if (data.success && data.data && data.data.length > 0) {
                                tableBody.innerHTML = '';
                                data.data.forEach(network => {
                                    const ssid = network.ssid || '<em>Desconocida / Oculta</em>';
                                    const row = `
                                                                    <tr>
                                                                        <td class="align-middle fw-bold border-secondary">${ssid} <br><small class="text-muted fw-normal">${network.bssid}</small></td>
                                                                        <td class="align-middle border-secondary">${network.channel}</td>
                                                                        <td class="align-middle border-secondary">${network.signal} <br><small class="text-muted">${network.quality}</small></td>
                                                                        <td class="align-middle border-secondary"><small>${network.encryption}</small></td>
                                                                        <td class="text-end align-middle border-secondary">
                                                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 btnConnectWifi" data-ssid="${network.ssid || ''}" data-bssid="${network.bssid}">Conectar</button>
                                                                        </td>
                                                                    </tr>
                                                                `;
                                    tableBody.insertAdjacentHTML('beforeend', row);
                                });
                            } else {
                                tableBody.innerHTML = `
                                                                <tr>
                                                                    <td colspan="5" class="text-center py-4 text-warning border-0">
                                                                        No se encontraron redes WiFi cercanas.
                                                                    </td>
                                                                </tr>
                                                            `;
                            }
                        })
                        .catch(error => {
                            tableBody.innerHTML = `
                                                            <tr>
                                                                <td colspan="5" class="text-center py-4 text-danger border-0">
                                                                    <i class="bi bi-exclamation-triangle me-2"></i> Ocurrió un error al intentar escanear. Inténtalo más tarde.
                                                                </td>
                                                            </tr>
                                                        `;
                            console.error('Scan Error:', error);
                        });
                });
            }

            // Modal Añadir Red
            const modalAddElement = document.getElementById('addWifiModal');
            if (modalAddElement) {
                modalAddElement.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    if (!button) return;
                    const device = button.getAttribute('data-device') || 'radio0';
                    const inputDevice = modalAddElement.querySelector('#addDeviceName');
                    if (inputDevice) inputDevice.value = device;
                });

                // Tabs UI logic fix
                const tabButtons = modalAddElement.querySelectorAll('button[data-bs-toggle="tab"]');
                tabButtons.forEach(btn => {
                    btn.addEventListener('show.bs.tab', function (e) {
                        tabButtons.forEach(b => {
                            b.classList.remove('text-white');
                            b.classList.add('text-soft');
                        });
                        e.target.classList.remove('text-soft');
                        e.target.classList.add('text-white');
                    });
                });

                const addEncryptionSelect = document.getElementById('addEncryption');
                const addPasswordContainer = document.getElementById('addPasswordContainer');
                const addPasswordInput = document.getElementById('addPassword');

                if (addEncryptionSelect) {
                    addEncryptionSelect.addEventListener('change', function () {
                        if (this.value === 'psk2') {
                            addPasswordContainer.classList.remove('d-none');
                            addPasswordInput.disabled = false;
                            addPasswordInput.required = true;
                        } else {
                            addPasswordContainer.classList.add('d-none');
                            addPasswordInput.disabled = true;
                            addPasswordInput.required = false;
                            addPasswordInput.value = '';
                        }
                    });
                }

                const addMacFilterSelect = document.getElementById('addMacFilter');
                const addMacListContainer = document.getElementById('addMacListContainer');
                const addMacListInput = document.getElementById('addMacList');

                if (addMacFilterSelect) {
                    addMacFilterSelect.addEventListener('change', function () {
                        if (this.value === 'allow' || this.value === 'deny') {
                            addMacListContainer.classList.remove('d-none');
                            addMacListInput.disabled = false;
                        } else {
                            addMacListContainer.classList.add('d-none');
                            addMacListInput.disabled = true;
                            addMacListInput.value = '';
                        }
                    });
                }
            }

            // Asignar dinámicamente SSID al modal de edición
            // Modal Editar Red
            const modalEditElement = document.getElementById('editWifiModal');
            if (modalEditElement) {
                modalEditElement.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    if (!button) return;

                    // Campos Ocultos/General
                    modalEditElement.querySelector('#editInterfaceName').value = button.getAttribute('data-id') || '';
                    modalEditElement.querySelector('#editSsid').value = button.getAttribute('data-ssid') || '';
                    modalEditElement.querySelector('#editMode').value = button.getAttribute('data-mode') || 'ap';
                    modalEditElement.querySelector('#editNetwork').value = button.getAttribute('data-network') || 'lan';
                    modalEditElement.querySelector('#editHiddenSSID').checked = (button.getAttribute('data-hidden') === '1');
                    modalEditElement.querySelector('#editActivateWMM').checked = (button.getAttribute('data-wmm') !== '0');

                    // Seguridad
                    const enc = button.getAttribute('data-encryption') || 'none';
                    const authSelect = modalEditElement.querySelector('#editEncryption');
                    authSelect.value = enc.includes('psk') ? 'psk2' : (enc === 'none' ? 'none' : 'none');
                    authSelect.dispatchEvent(new Event('change'));

                    const keyVal = button.getAttribute('data-key') || '';
                    modalEditElement.querySelector('#editPassword').value = keyVal;

                    // MAC Filter
                    const macf = button.getAttribute('data-macfilter') || 'disable';
                    const filterSelect = modalEditElement.querySelector('#editMacFilter');
                    filterSelect.value = macf;
                    filterSelect.dispatchEvent(new Event('change'));

                    const macl = button.getAttribute('data-maclist') || '';
                    modalEditElement.querySelector('#editMacList').value = macl;
                });

                // Tabs UI logic fix
                const tabButtonsEdit = modalEditElement.querySelectorAll('button[data-bs-toggle="tab"]');
                tabButtonsEdit.forEach(btn => {
                    btn.addEventListener('show.bs.tab', function (e) {
                        tabButtonsEdit.forEach(b => {
                            b.classList.remove('text-white');
                            b.classList.add('text-soft');
                        });
                        e.target.classList.remove('text-soft');
                        e.target.classList.add('text-white');
                    });
                });

                const editEncryptionSelect = document.getElementById('editEncryption');
                const editPasswordContainer = document.getElementById('editPasswordContainer');
                const editPasswordInput = document.getElementById('editPassword');

                if (editEncryptionSelect) {
                    editEncryptionSelect.addEventListener('change', function () {
                        if (this.value === 'psk2') {
                            editPasswordContainer.classList.remove('d-none');
                            editPasswordInput.disabled = false;
                            editPasswordInput.required = true;
                        } else {
                            editPasswordContainer.classList.add('d-none');
                            editPasswordInput.disabled = true;
                            editPasswordInput.required = false;
                        }
                    });
                }

                const editMacFilterSelect = document.getElementById('editMacFilter');
                const editMacListContainer = document.getElementById('editMacListContainer');
                const editMacListInput = document.getElementById('editMacList');

                if (editMacFilterSelect) {
                    editMacFilterSelect.addEventListener('change', function () {
                        if (this.value === 'allow' || this.value === 'deny') {
                            editMacListContainer.classList.remove('d-none');
                            editMacListInput.disabled = false;
                        } else {
                            editMacListContainer.classList.add('d-none');
                            editMacListInput.disabled = true;
                        }
                    });
                }
            }

            const modalConnectElement = document.getElementById('connectWifiModal');
            let modalConnect;
            if (modalConnectElement) {
                modalConnect = new bootstrap.Modal(modalConnectElement);
            }

            // Delegación de eventos para botones "Conectar"
            if (tableBody) {
                tableBody.addEventListener('click', function (e) {
                    const btn = e.target.closest('.btnConnectWifi');
                    if (btn && modalConnect) {
                        const ssid = btn.getAttribute('data-ssid') || 'Oculta';
                        const bssid = btn.getAttribute('data-bssid');

                        document.getElementById('connectModalSsidStr').textContent = ssid;
                        document.getElementById('connectSsid').value = ssid;
                        document.getElementById('connectBssid').value = bssid;

                        document.getElementById('connectAlertError').classList.add('d-none');
                        document.getElementById('connectAlertSuccess').classList.add('d-none');
                        document.getElementById('connectPassword').value = '';

                        if (modalScan) modalScan.hide();
                        modalConnect.show();
                    }
                });
            }

            // Manejo del submit del formulario de conexión
            const formConnect = document.getElementById('connectWifiForm');
            if (formConnect) {
                formConnect.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const btnSubmit = document.getElementById('btnSubmitConnect');
                    const errAlert = document.getElementById('connectAlertError');
                    const sucAlert = document.getElementById('connectAlertSuccess');

                    errAlert.classList.add('d-none');
                    sucAlert.classList.add('d-none');

                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div> Conectando...';

                    const formData = new FormData(formConnect);
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.set('lock_bssid', document.getElementById('lockBssid').checked ? 'true' : 'false');

                    fetch('{{ route('red.wifi.connect') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                sucAlert.textContent = data.message || 'Conexión exitosa';
                                sucAlert.classList.remove('d-none');
                                setTimeout(() => { modalConnect.hide(); }, 3500);
                            } else {
                                errAlert.textContent = data.message || 'Error desconocido';
                                errAlert.classList.remove('d-none');
                            }
                        })
                        .catch(error => {
                            errAlert.textContent = 'Tráfico de red abortado. Asegúrese de que la configuración sea correcta.';
                            errAlert.classList.remove('d-none');
                        })
                        .finally(() => {
                            if (btnSubmit && !sucAlert.classList.contains('d-none') === false) {
                                btnSubmit.disabled = false;
                                btnSubmit.innerHTML = 'Enviar';
                            }
                        });
                });
            }
        });
    </script>

    <!-- Connect WiFi Modal -->
    <div class="modal fade" id="connectWifiModal" tabindex="-1" aria-labelledby="connectWifiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="connectWifiModalLabel">Conectarse a: <span id="connectModalSsidStr"
                            class="text-info"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="connectWifiForm">
                        <input type="hidden" id="connectSsid" name="ssid">
                        <input type="hidden" id="connectBssid" name="bssid">

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="replaceConfig" name="replace_config" checked
                                disabled>
                            <label class="form-check-label text-soft" for="replaceConfig">Reemplazar la configuración Wi-Fi
                                actual</label>
                        </div>

                        <div class="mb-3">
                            <label for="connectNetwork" class="form-label text-soft">Nombre de la nueva red lógica</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="connectNetwork"
                                name="network" value="wwan" required>
                        </div>

                        <div class="mb-3">
                            <label for="connectPassword" class="form-label text-soft">Contraseña WPA</label>
                            <input type="password" class="form-control bg-dark text-white border-secondary"
                                id="connectPassword" name="password" minlength="8" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="lockBssid" name="lock_bssid">
                            <label class="form-check-label text-soft" for="lockBssid">Bloquear a BSSID</label>
                        </div>

                        <div class="mb-3">
                            <label for="connectZone" class="form-label text-soft">Zona de firewall</label>
                            <select class="form-select bg-dark text-white border-secondary" id="connectZone" name="zone">
                                <option value="lan">lan</option>
                                <option value="wan" selected>wan</option>
                                <option value="wwan">wwan</option>
                            </select>
                        </div>

                        <div class="alert alert-danger d-none" id="connectAlertError"></div>
                        <div class="alert alert-success d-none" id="connectAlertSuccess"></div>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary" id="btnSubmitConnect">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add WiFi Modal -->
    <div class="modal fade" id="addWifiModal" tabindex="-1" aria-labelledby="addWifiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="addWifiModalLabel"><i class="bi bi-plus-circle me-2"></i>Añadir red Wi-Fi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <form action="{{ route('red.wifi.add') }}" method="POST" id="formAddWifi">
                        @csrf
                        <input type="hidden" name="device" id="addDeviceName" value="radio0">

                        <!-- Tabs Nav -->
                        <ul class="nav nav-tabs border-secondary bg-secondary bg-opacity-25" id="addWifiTabs"
                            role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active text-white border-secondary border-bottom-0" id="general-tab"
                                    data-bs-toggle="tab" data-bs-target="#general-pane" type="button" role="tab"
                                    aria-controls="general-pane" aria-selected="true"
                                    style="background-color: transparent;">Configuración general</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-soft border-secondary border-bottom-0" id="security-tab"
                                    data-bs-toggle="tab" data-bs-target="#security-pane" type="button" role="tab"
                                    aria-controls="security-pane" aria-selected="false"
                                    style="background-color: transparent;">Seguridad Wi-Fi</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-soft border-secondary border-bottom-0" id="mac-tab"
                                    data-bs-toggle="tab" data-bs-target="#mac-pane" type="button" role="tab"
                                    aria-controls="mac-pane" aria-selected="false"
                                    style="background-color: transparent;">Filtro por MAC</button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content custom-tabs-content p-4" id="addWifiTabsContent">

                            <!-- Pestaña Configuración general -->
                            <div class="tab-pane fade show active" id="general-pane" role="tabpanel"
                                aria-labelledby="general-tab" tabindex="0">
                                <div class="row mb-3 align-items-center">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">Modo</label>
                                    <div class="col-sm-9">
                                        <select name="mode" class="form-select bg-dark text-white border-secondary">
                                            <option value="ap" selected>Punto de acceso (AP)</option>
                                            <option value="sta">Cliente (STA)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3 align-items-center">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">ESSID</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="ssid"
                                            class="form-control bg-dark text-white border-secondary" required
                                            placeholder="Ej: OpenWrt_Nuevo">
                                    </div>
                                </div>

                                <div class="row mb-3 align-items-center">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">Red</label>
                                    <div class="col-sm-9">
                                        <select name="network" class="form-select bg-dark text-white border-secondary">
                                            <option value="lan" selected>lan (Red local)</option>
                                            <option value="wan">wan (Red externa)</option>
                                            <option value="wwan">wwan (Modem/Wifi extendido)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3 align-items-center">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="hidden" id="hiddenSSID">
                                            <label class="form-check-label text-soft" for="hiddenSSID">
                                                Ocultar ESSID
                                            </label>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="wmm" id="activateWMM"
                                                checked>
                                            <label class="form-check-label text-soft" for="activateWMM">
                                                Activar WMM
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Seguridad Wi-Fi -->
                            <div class="tab-pane fade" id="security-pane" role="tabpanel" aria-labelledby="security-tab"
                                tabindex="0">
                                <div class="row mb-3 align-items-center">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">Encriptación</label>
                                    <div class="col-sm-9">
                                        <select name="encryption" id="addEncryption"
                                            class="form-select bg-dark text-white border-secondary">
                                            <option value="none" selected>Sin encriptación (red abierta)</option>
                                            <option value="psk2">WPA2-PSK (seguridad fuerte)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3 align-items-center d-none" id="addPasswordContainer">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">Clave</label>
                                    <div class="col-sm-9">
                                        <input type="password" name="password" id="addPassword"
                                            class="form-control bg-dark text-white border-secondary" minlength="8" disabled>
                                        <div class="form-text text-muted">La clave debe tener un mínimo de 8 caracteres.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pestaña Filtro por MAC -->
                            <div class="tab-pane fade" id="mac-pane" role="tabpanel" aria-labelledby="mac-tab" tabindex="0">
                                <div class="row mb-3 align-items-center">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">Filtro MAC</label>
                                    <div class="col-sm-9">
                                        <select name="macfilter" id="addMacFilter"
                                            class="form-select bg-dark text-white border-secondary">
                                            <option value="disable" selected>Desactivar</option>
                                            <option value="allow">Permitir a los pertenecientes a la lista</option>
                                            <option value="deny">Permitir a todos excepto a los de la lista</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3 align-items-start d-none" id="addMacListContainer">
                                    <label class="col-sm-3 col-form-label text-md-end text-soft">Direcciones MAC</label>
                                    <div class="col-sm-9">
                                        <textarea name="maclist" id="addMacList"
                                            class="form-control bg-dark text-white border-secondary" rows="3"
                                            placeholder="Ej: 00:11:22:33:44:55&#10;AA:BB:CC:DD:EE:FF" disabled></textarea>
                                        <div class="form-text text-muted">Ingrese múltiples direcciones MAC separadas por
                                            salto de línea o coma.</div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-secondary fw-bold"
                                data-bs-dismiss="modal">DESCARTAR</button>
                            <button type="submit" class="btn btn-primary fw-bold"
                                style="background-color: #397cbd;">GUARDAR</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection