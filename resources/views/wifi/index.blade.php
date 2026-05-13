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
                                                data-maclist="{{ isset($interface['maclist']) ? (is_array($interface['maclist']) ? implode('\n', $interface['maclist']) : str_replace(' ', '\n', $interface['maclist'])) : '' }}"
                                                data-isolate="{{ $interface['isolate'] ?? '0' }}"
                                                data-ifname="{{ $interface['ifname'] ?? '' }}"
                                                data-short-preamble="{{ $interface['short_preamble'] ?? '1' }}"
                                                data-dtim-period="{{ $interface['dtim_period'] ?? '' }}"
                                                data-wpa-group-rekey="{{ $interface['wpa_group_rekey'] ?? '' }}"
                                                data-disassoc-low-ack-check="{{ $interface['disassoc_low_ack'] ?? '1' }}"
                                                data-disassoc-low-ack="{{ $interface['skip_inactivity_poll'] ?? '0' }}"
                                                data-maxassoc="{{ $interface['maxassoc'] ?? '' }}"
                                                data-max-listen-int="{{ $interface['max_listen_interval'] ?? '' }}"
                                                data-radio-mode="{{ $radio['hwmode'] ?? '' }}"
                                                data-radio-channel="{{ $radio['channel'] ?? '' }}"
                                                data-radio-bandwidth="{{ $radio['htmode'] ?? '' }}"
                                                data-radio-txpower="{{ $radio['txpower'] ?? '' }}"
                                                data-radio-country="{{ $radio['country'] ?? '' }}"
                                                data-radio-legacy-rates="{{ $radio['legacy_rates'] ?? '0' }}"
                                                data-radio-distance="{{ $radio['distance'] ?? '' }}"
                                                data-radio-frag="{{ $radio['frag'] ?? '' }}"
                                                data-radio-rts="{{ $radio['rts'] ?? '' }}"
                                                data-radio-force-40="{{ $radio['noscan'] ?? '0' }}"
                                                data-radio-beacon="{{ $radio['beacon_int'] ?? '' }}">EDITAR</button>
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

                        <!-- Top Box: Radio Config -->
                        <div class="border-bottom border-secondary mb-4 bg-dark">
                            <!-- Tabs Nav Radio -->
                            <ul class="nav nav-tabs border-secondary bg-secondary bg-opacity-25 px-3 pt-2" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active text-white border-secondary border-bottom-0" data-bs-toggle="tab" data-bs-target="#edit-radio-general-pane" type="button" role="tab" aria-selected="true" style="background-color: transparent;">Configuración general</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-soft border-secondary border-bottom-0" data-bs-toggle="tab" data-bs-target="#edit-radio-adv-pane" type="button" role="tab" aria-selected="false" style="background-color: transparent;">Configuración avanzada</button>
                                </li>
                            </ul>
                            <!-- Tabs Content Radio -->
                            <div class="tab-content p-4">
                                <!-- Pestaña Configuración general (Radio) -->
                                <div class="tab-pane fade show active" id="edit-radio-general-pane" role="tabpanel" tabindex="0">
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Estado</label>
                                        <div class="col-sm-8">
                                            <div class="d-flex align-items-center p-2 rounded" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); max-width: 300px;">
                                                <i class="bi bi-bar-chart-fill text-muted me-3 fs-4"></i>
                                                <div>
                                                    <div class="fw-bold" style="font-size: 0.85rem;">Modo: Master | SSID: OpenWrt</div>
                                                    <div class="text-soft" style="font-size: 0.75rem;">--- dBm Red Wi-Fi no asociada</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Red Wi-Fi activada</label>
                                        <div class="col-sm-8">
                                            <button type="button" class="btn btn-danger btn-sm px-3 fw-bold" style="background: #db4444; font-size: 0.75rem;">DESACTIVAR</button>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Frecuencia de operación</label>
                                        <div class="col-sm-8 d-flex gap-2">
                                            <div>
                                                <label class="form-label text-soft mb-1" style="font-size: 0.75rem;">Modo</label>
                                                <select name="radio_mode" class="form-select form-select-sm bg-dark text-white border-secondary">
                                                    <option value="11n">N</option>
                                                    <option value="11g">Legacy</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label text-soft mb-1" style="font-size: 0.75rem;">Canal</label>
                                                <select name="radio_channel" class="form-select form-select-sm bg-dark text-white border-secondary">
                                                    <option value="auto">auto</option>
                                                    <option value="1">1 (2412 MHz)</option>
                                                    <option value="2">2 (2417 MHz)</option>
                                                    <option value="3">3 (2422 MHz)</option>
                                                    <option value="4">4 (2427 MHz)</option>
                                                    <option value="5">5 (2432 MHz)</option>
                                                    <option value="6">6 (2437 MHz)</option>
                                                    <option value="7">7 (2442 MHz)</option>
                                                    <option value="8">8 (2447 MHz)</option>
                                                    <option value="9">9 (2452 MHz)</option>
                                                    <option value="10">10 (2457 MHz)</option>
                                                    <option value="11">11 (2462 MHz)</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label text-soft mb-1" style="font-size: 0.75rem;">Ancho de banda</label>
                                                <select name="radio_bandwidth" class="form-select form-select-sm bg-dark text-white border-secondary">
                                                    <option value="HT20">20 MHz</option>
                                                    <option value="HT40">40 MHz</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-0 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Máxima potencia de transmisión</label>
                                        <div class="col-sm-8">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <select name="radio_txpower" class="form-select form-select-sm bg-dark text-white border-secondary" style="max-width: 250px;">
                                                    <option value="Predeterminado por el controlador">Predeterminado por el controlador</option>
                                                    <option value="0">0 dBm (1 mW)</option>
                                                    <option value="1">1 dBm (1 mW)</option>
                                                    <option value="2">2 dBm (1 mW)</option>
                                                    <option value="3">3 dBm (1 mW)</option>
                                                    <option value="4">4 dBm (2 mW)</option>
                                                    <option value="5">5 dBm (3 mW)</option>
                                                    <option value="6">6 dBm (3 mW)</option>
                                                    <option value="7">7 dBm (5 mW)</option>
                                                    <option value="8">8 dBm (6 mW)</option>
                                                    <option value="9">9 dBm (7 mW)</option>
                                                    <option value="10">10 dBm (10 mW)</option>
                                                    <option value="11">11 dBm (12 mW)</option>
                                                    <option value="12">12 dBm (15 mW)</option>
                                                    <option value="13">13 dBm (19 mW)</option>
                                                    <option value="14">14 dBm (25 mW)</option>
                                                    <option value="15">15 dBm (31 mW)</option>
                                                    <option value="16">16 dBm (39 mW)</option>
                                                    <option value="17">17 dBm (50 mW)</option>
                                                    <option value="18">18 dBm (63 mW)</option>
                                                    <option value="19">19 dBm (79 mW)</option>
                                                    <option value="20">20 dBm (100 mW)</option>
                                                </select>
                                                <span class="text-soft" style="font-size: 0.85rem;">- Potencia actual: <em class="text-muted">Desconocido</em></span>
                                            </div>
                                            <div class="form-text text-muted mt-2" style="font-size: 0.75rem;">Especifique la potencia de transmisión máxima que puede usar la radio inalámbrica. Dependiendo de los requisitos reglamentarios y el uso inalámbrico, el controlador puede reducir la potencia de transmisión real.</div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Pestaña Configuración avanzada (Radio) -->
                                <div class="tab-pane fade" id="edit-radio-adv-pane" role="tabpanel" tabindex="0">
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Código de país</label>
                                        <div class="col-sm-8">
                                            <select name="radio_country" class="form-select form-select-sm bg-dark text-white border-secondary">
                                                <option value="00">Predeterminado por el controlador</option>
                                                <option value="MR">MR - Mauritania</option>
                                                <option value="MS">MS - Montserrat</option>
                                                <option value="MT">MT - Malta</option>
                                                <option value="MU">MU - Mauritius</option>
                                                <option value="MV">MV - Maldives</option>
                                                <option value="MW">MW - Malawi</option>
                                                <option value="MX">MX - Mexico</option>
                                                <option value="MY">MY - Malaysia</option>
                                                <option value="MZ">MZ - Mozambique</option>
                                                <option value="NA">NA - Namibia</option>
                                                <option value="NC">NC - New Caledonia</option>
                                                <option value="NE">NE - Niger</option>
                                                <option value="NF">NF - Norfolk Island</option>
                                                <option value="NG">NG - Nigeria</option>
                                                <option value="NI">NI - Nicaragua</option>
                                                <option value="NL">NL - Netherlands</option>
                                                <option value="NO">NO - Norway</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Permitir tasas de 802.11b heredadas</label>
                                        <div class="col-sm-8">
                                            <input type="checkbox" class="form-check-input mt-2" name="radio_legacy_rates" checked>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-start">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Optimización de distancia</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="radio_distance" placeholder="auto">
                                            <div class="form-text text-muted" style="font-size: 0.75rem;">Distancia en metros al miembro más lejano de la red.</div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Umbral de fragmentación</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="radio_frag" placeholder="Apagado">
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Umbral RTS/CTS</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="radio_rts" placeholder="Apagado">
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-start">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Forzar modo 40MHz</label>
                                        <div class="col-sm-8">
                                            <input type="checkbox" class="form-check-input mt-2" name="radio_force_40">
                                            <div class="form-text text-muted" style="font-size: 0.75rem;">Usará siempre canales de 40MHz incluso si el canal secundario se superpone. ¡El uso de esta opción no cumple con IEEE 802.11n-2009!</div>
                                        </div>
                                    </div>
                                    <div class="row mb-0 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Intervalo de baliza</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="radio_beacon" placeholder="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabs Nav -->
                        <ul class="nav nav-tabs border-secondary bg-secondary bg-opacity-25 px-3" id="editWifiTabs"
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
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-soft border-secondary border-bottom-0" id="edit-adv-tab"
                                    data-bs-toggle="tab" data-bs-target="#edit-adv-pane" type="button" role="tab"
                                    aria-controls="edit-adv-pane" aria-selected="false"
                                    style="background-color: transparent;">Configuración avanzada</button>
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
                                            <option value="sta">Cliente</option>
                                            <option value="adhoc">Ad-Hoc</option>
                                            <option value="mesh">802.11s</option>
                                            <option value="ahdemo">Pseudo Ad-Hoc (ahdemo)</option>
                                            <option value="monitor">Monitor</option>
                                            <option value="ap-wds">AP (WDS)</option>
                                            <option value="sta-wds">Cliente (WDS)</option>
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
                                    <div class="form-text text-muted">Ingrese múltiples direcciones MAC separadas por salto de
                                        línea o coma.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Pestaña Configuración avanzada -->
                        <div class="tab-pane fade" id="edit-adv-pane" role="tabpanel" aria-labelledby="edit-adv-tab" tabindex="0">
                            <div class="row mb-3 align-items-start">
                                <label class="col-sm-4 col-form-label text-md-end text-soft">Aislar clientes</label>
                                <div class="col-sm-8">
                                    <input type="checkbox" class="form-check-input mt-2" name="isolate">
                                    <div class="form-text text-muted" style="font-size: 0.75rem;">Impide la comunicación entre los clientes</div>
                                </div>
                            </div>
                            <div class="row mb-3 align-items-start">
                                <label class="col-sm-4 col-form-label text-md-end text-soft">Nombre de interfaz</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="ifname">
                                    <div class="form-text text-muted" style="font-size: 0.75rem;">Reemplaza el nombre de interfaz predeterminado</div>
                                </div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-4 col-form-label text-md-end text-soft">Preámbulo corto</label>
                                <div class="col-sm-8">
                                    <input type="checkbox" class="form-check-input mt-2" name="short_preamble" checked>
                                </div>
                            </div>
                            <div class="row mb-3 align-items-start">
                                <label class="col-sm-4 col-form-label text-md-end text-soft">Intervalo DTIM</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="dtim_period" placeholder="2">
                                    <div class="form-text text-muted" style="font-size: 0.75rem;">Intervalo de mensaje de indicación de tráfico de entrega</div>
                                </div>
                            </div>
                            <div class="row mb-3 align-items-start">
                                <label class="col-sm-4 col-form-label text-md-end text-soft">Intervalo de tiempo para reprogramar GTK</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="wpa_group_rekey" placeholder="600">
                                    <div class="form-text text-muted" style="font-size: 0.75rem;">Seg</div>
                                </div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-4 col-form-label text-md-end text-soft">Desactivar sondeo de inactividad</label>
                                <div class="col-sm-8">
                                    <input type="checkbox" class="form-check-input mt-2" name="disassoc_low_ack">
                                </div>
                            </div>
                            <div class="row mb-3 align-items-start">
                                <label class="col-sm-4 col-form-label text-md-end text-soft">Límite de inactividad de la estación</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="maxassoc" placeholder="300">
                                    <div class="form-text text-muted" style="font-size: 0.75rem;">Seg</div>
                                </div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <label class="col-sm-4 col-form-label text-md-end text-soft">Máximo permitido de intervalo de escucha</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="max_listen_int" placeholder="65535">
                                </div>
                            </div>
                            <div class="row mb-0 align-items-start">
                                <label class="col-sm-4 col-form-label text-md-end text-soft">Desasociarse en un reconocimiento bajo</label>
                                <div class="col-sm-8">
                                    <input type="checkbox" class="form-check-input mt-2" name="disassoc_low_ack_check" checked>
                                    <div class="form-text text-muted" style="font-size: 0.75rem;">Permitir que el modo AP desconecte los clientes por una condición de ACK bajo</div>
                                </div>
                            </div>
                        </div>                                   </div>
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

                    // Avanzado Interfaz
                    modalEditElement.querySelector('input[name="isolate"]').checked = (button.getAttribute('data-isolate') === '1');
                    modalEditElement.querySelector('input[name="ifname"]').value = button.getAttribute('data-ifname') || '';
                    modalEditElement.querySelector('input[name="short_preamble"]').checked = (button.getAttribute('data-short-preamble') !== '0');
                    modalEditElement.querySelector('input[name="dtim_period"]').value = button.getAttribute('data-dtim-period') || '';
                    modalEditElement.querySelector('input[name="wpa_group_rekey"]').value = button.getAttribute('data-wpa-group-rekey') || '';
                    modalEditElement.querySelector('input[name="disassoc_low_ack_check"]').checked = (button.getAttribute('data-disassoc-low-ack-check') !== '0');
                    modalEditElement.querySelector('input[name="disassoc_low_ack"]').checked = (button.getAttribute('data-disassoc-low-ack') === '1');
                    modalEditElement.querySelector('input[name="maxassoc"]').value = button.getAttribute('data-maxassoc') || '';
                    modalEditElement.querySelector('input[name="max_listen_int"]').value = button.getAttribute('data-max-listen-int') || '';

                    // Avanzado Radio
                    const selRadioMode = modalEditElement.querySelector('select[name="radio_mode"]');
                    if (selRadioMode) {
                        const v = button.getAttribute('data-radio-mode') || '11g';
                        if (!Array.from(selRadioMode.options).some(o => o.value === v)) {
                            selRadioMode.add(new Option(v, v));
                        }
                        selRadioMode.value = v;
                    }

                    const selRadioChannel = modalEditElement.querySelector('select[name="radio_channel"]');
                    if (selRadioChannel) {
                        const v = button.getAttribute('data-radio-channel') || 'auto';
                        if (!Array.from(selRadioChannel.options).some(o => o.value === v)) {
                            selRadioChannel.add(new Option(v, v));
                        }
                        selRadioChannel.value = v;
                    }

                    const selRadioBw = modalEditElement.querySelector('select[name="radio_bandwidth"]');
                    if (selRadioBw) {
                        const v = button.getAttribute('data-radio-bandwidth') || 'HT20';
                        if (!Array.from(selRadioBw.options).some(o => o.value === v)) {
                            selRadioBw.add(new Option(v, v));
                        }
                        selRadioBw.value = v;
                    }

                    const selRadioTx = modalEditElement.querySelector('select[name="radio_txpower"]');
                    if (selRadioTx) {
                        const v = button.getAttribute('data-radio-txpower');
                        if (v && v !== 'Predeterminado por el controlador' && !Array.from(selRadioTx.options).some(o => o.value === v)) {
                            selRadioTx.add(new Option(v + ' dBm', v));
                            selRadioTx.value = v;
                        } else if (v) {
                            selRadioTx.value = v;
                        }
                    }

                    const selRadioCountry = modalEditElement.querySelector('select[name="radio_country"]');
                    if (selRadioCountry) {
                        const v = button.getAttribute('data-radio-country');
                        if (v && !Array.from(selRadioCountry.options).some(o => o.value === v)) {
                            selRadioCountry.add(new Option(v, v));
                        }
                        selRadioCountry.value = v || '00';
                    }

                    modalEditElement.querySelector('input[name="radio_legacy_rates"]').checked = (button.getAttribute('data-radio-legacy-rates') === '1');
                    modalEditElement.querySelector('input[name="radio_distance"]').value = button.getAttribute('data-radio-distance') || '';
                    modalEditElement.querySelector('input[name="radio_frag"]').value = button.getAttribute('data-radio-frag') || '';
                    modalEditElement.querySelector('input[name="radio_rts"]').value = button.getAttribute('data-radio-rts') || '';
                    modalEditElement.querySelector('input[name="radio_force_40"]').checked = (button.getAttribute('data-radio-force-40') === '1');
                    modalEditElement.querySelector('input[name="radio_beacon"]').value = button.getAttribute('data-radio-beacon') || '';
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

                        <!-- Top Box: Radio Config -->
                        <div class="border-bottom border-secondary mb-4 bg-dark">
                            <!-- Tabs Nav Radio -->
                            <ul class="nav nav-tabs border-secondary bg-secondary bg-opacity-25 px-3 pt-2" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active text-white border-secondary border-bottom-0" data-bs-toggle="tab" data-bs-target="#add-radio-general-pane" type="button" role="tab" aria-selected="true" style="background-color: transparent;">Configuración general</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link text-soft border-secondary border-bottom-0" data-bs-toggle="tab" data-bs-target="#add-radio-adv-pane" type="button" role="tab" aria-selected="false" style="background-color: transparent;">Configuración avanzada</button>
                                </li>
                            </ul>
                            <!-- Tabs Content Radio -->
                            <div class="tab-content p-4">
                                <!-- Pestaña Configuración general (Radio) -->
                                <div class="tab-pane fade show active" id="add-radio-general-pane" role="tabpanel" tabindex="0">
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Estado</label>
                                        <div class="col-sm-8">
                                            <div class="d-flex align-items-center p-2 rounded" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); max-width: 300px;">
                                                <i class="bi bi-bar-chart-fill text-muted me-3 fs-4"></i>
                                                <div>
                                                    <div class="fw-bold" style="font-size: 0.85rem;">Modo: Master | SSID: OpenWrt</div>
                                                    <div class="text-soft" style="font-size: 0.75rem;">--- dBm Red Wi-Fi no asociada</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Red Wi-Fi activada</label>
                                        <div class="col-sm-8">
                                            <button type="button" class="btn btn-danger btn-sm px-3 fw-bold" style="background: #db4444; font-size: 0.75rem;">DESACTIVAR</button>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Frecuencia de operación</label>
                                        <div class="col-sm-8 d-flex gap-2">
                                            <div>
                                                <label class="form-label text-soft mb-1" style="font-size: 0.75rem;">Modo</label>
                                                <select name="add_radio_mode" class="form-select form-select-sm bg-dark text-white border-secondary">
                                                    <option value="11n">N</option>
                                                    <option value="11g">Legacy</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label text-soft mb-1" style="font-size: 0.75rem;">Canal</label>
                                                <select name="add_radio_channel" class="form-select form-select-sm bg-dark text-white border-secondary">
                                                    <option value="auto">auto</option>
                                                    <option value="1">1 (2412 MHz)</option>
                                                    <option value="2">2 (2417 MHz)</option>
                                                    <option value="3">3 (2422 MHz)</option>
                                                    <option value="4">4 (2427 MHz)</option>
                                                    <option value="5">5 (2432 MHz)</option>
                                                    <option value="6">6 (2437 MHz)</option>
                                                    <option value="7">7 (2442 MHz)</option>
                                                    <option value="8">8 (2447 MHz)</option>
                                                    <option value="9">9 (2452 MHz)</option>
                                                    <option value="10">10 (2457 MHz)</option>
                                                    <option value="11">11 (2462 MHz)</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label text-soft mb-1" style="font-size: 0.75rem;">Ancho de banda</label>
                                                <select name="add_radio_bandwidth" class="form-select form-select-sm bg-dark text-white border-secondary">
                                                    <option value="HT20">20 MHz</option>
                                                    <option value="HT40">40 MHz</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-0 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Máxima potencia de transmisión</label>
                                        <div class="col-sm-8">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <select name="add_radio_txpower" class="form-select form-select-sm bg-dark text-white border-secondary" style="max-width: 250px;">
                                                    <option value="Predeterminado por el controlador">Predeterminado por el controlador</option>
                                                    <option value="0">0 dBm (1 mW)</option>
                                                    <option value="1">1 dBm (1 mW)</option>
                                                    <option value="2">2 dBm (1 mW)</option>
                                                    <option value="3">3 dBm (1 mW)</option>
                                                    <option value="4">4 dBm (2 mW)</option>
                                                    <option value="5">5 dBm (3 mW)</option>
                                                    <option value="6">6 dBm (3 mW)</option>
                                                    <option value="7">7 dBm (5 mW)</option>
                                                    <option value="8">8 dBm (6 mW)</option>
                                                    <option value="9">9 dBm (7 mW)</option>
                                                    <option value="10">10 dBm (10 mW)</option>
                                                    <option value="11">11 dBm (12 mW)</option>
                                                    <option value="12">12 dBm (15 mW)</option>
                                                    <option value="13">13 dBm (19 mW)</option>
                                                    <option value="14">14 dBm (25 mW)</option>
                                                    <option value="15">15 dBm (31 mW)</option>
                                                    <option value="16">16 dBm (39 mW)</option>
                                                    <option value="17">17 dBm (50 mW)</option>
                                                    <option value="18">18 dBm (63 mW)</option>
                                                    <option value="19">19 dBm (79 mW)</option>
                                                    <option value="20">20 dBm (100 mW)</option>
                                                </select>
                                                <span class="text-soft" style="font-size: 0.85rem;">- Potencia actual: <em class="text-muted">Desconocido</em></span>
                                            </div>
                                            <div class="form-text text-muted mt-2" style="font-size: 0.75rem;">Especifique la potencia de transmisión máxima que puede usar la radio inalámbrica. Dependiendo de los requisitos reglamentarios y el uso inalámbrico, el controlador puede reducir la potencia de transmisión real.</div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Pestaña Configuración avanzada (Radio) -->
                                <div class="tab-pane fade" id="add-radio-adv-pane" role="tabpanel" tabindex="0">
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Código de país</label>
                                        <div class="col-sm-8">
                                            <select name="radio_country" class="form-select form-select-sm bg-dark text-white border-secondary">
                                                <option value="00">Predeterminado por el controlador</option>
                                                <option value="MR">MR - Mauritania</option>
                                                <option value="MS">MS - Montserrat</option>
                                                <option value="MT">MT - Malta</option>
                                                <option value="MU">MU - Mauritius</option>
                                                <option value="MV">MV - Maldives</option>
                                                <option value="MW">MW - Malawi</option>
                                                <option value="MX">MX - Mexico</option>
                                                <option value="MY">MY - Malaysia</option>
                                                <option value="MZ">MZ - Mozambique</option>
                                                <option value="NA">NA - Namibia</option>
                                                <option value="NC">NC - New Caledonia</option>
                                                <option value="NE">NE - Niger</option>
                                                <option value="NF">NF - Norfolk Island</option>
                                                <option value="NG">NG - Nigeria</option>
                                                <option value="NI">NI - Nicaragua</option>
                                                <option value="NL">NL - Netherlands</option>
                                                <option value="NO">NO - Norway</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Permitir tasas de 802.11b heredadas</label>
                                        <div class="col-sm-8">
                                            <input type="checkbox" class="form-check-input mt-2" name="radio_legacy_rates" checked>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-start">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Optimización de distancia</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="radio_distance" placeholder="auto">
                                            <div class="form-text text-muted" style="font-size: 0.75rem;">Distancia en metros al miembro más lejano de la red.</div>
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Umbral de fragmentación</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="radio_frag" placeholder="Apagado">
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Umbral RTS/CTS</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="radio_rts" placeholder="Apagado">
                                        </div>
                                    </div>
                                    <div class="row mb-3 align-items-start">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Forzar modo 40MHz</label>
                                        <div class="col-sm-8">
                                            <input type="checkbox" class="form-check-input mt-2" name="radio_force_40">
                                            <div class="form-text text-muted" style="font-size: 0.75rem;">Usará siempre canales de 40MHz incluso si el canal secundario se superpone. ¡El uso de esta opción no cumple con IEEE 802.11n-2009!</div>
                                        </div>
                                    </div>
                                    <div class="row mb-0 align-items-center">
                                        <label class="col-sm-4 col-form-label text-md-end text-soft">Intervalo de baliza</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="radio_beacon" placeholder="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabs Nav -->
                        <ul class="nav nav-tabs border-secondary bg-secondary bg-opacity-25 px-3" id="addWifiTabs"
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
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-soft border-secondary border-bottom-0" id="adv-tab"
                                    data-bs-toggle="tab" data-bs-target="#adv-pane" type="button" role="tab"
                                    aria-controls="adv-pane" aria-selected="false"
                                    style="background-color: transparent;">Configuración avanzada</button>
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
                                            <option value="sta">Cliente</option>
                                            <option value="adhoc">Ad-Hoc</option>
                                            <option value="mesh">802.11s</option>
                                            <option value="ahdemo">Pseudo Ad-Hoc (ahdemo)</option>
                                            <option value="monitor">Monitor</option>
                                            <option value="ap-wds">AP (WDS)</option>
                                            <option value="sta-wds">Cliente (WDS)</option>
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

                            <!-- Pestaña Configuración avanzada -->
                            <div class="tab-pane fade" id="adv-pane" role="tabpanel" aria-labelledby="adv-tab" tabindex="0">
                                <div class="row mb-3 align-items-start">
                                    <label class="col-sm-4 col-form-label text-md-end text-soft">Aislar clientes</label>
                                    <div class="col-sm-8">
                                        <input type="checkbox" class="form-check-input mt-2" name="isolate">
                                        <div class="form-text text-muted" style="font-size: 0.75rem;">Impide la comunicación entre los clientes</div>
                                    </div>
                                </div>
                                <div class="row mb-3 align-items-start">
                                    <label class="col-sm-4 col-form-label text-md-end text-soft">Nombre de interfaz</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="ifname">
                                        <div class="form-text text-muted" style="font-size: 0.75rem;">Reemplaza el nombre de interfaz predeterminado</div>
                                    </div>
                                </div>
                                <div class="row mb-3 align-items-center">
                                    <label class="col-sm-4 col-form-label text-md-end text-soft">Preámbulo corto</label>
                                    <div class="col-sm-8">
                                        <input type="checkbox" class="form-check-input mt-2" name="short_preamble" checked>
                                    </div>
                                </div>
                                <div class="row mb-3 align-items-start">
                                    <label class="col-sm-4 col-form-label text-md-end text-soft">Intervalo DTIM</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="dtim_period" placeholder="2">
                                        <div class="form-text text-muted" style="font-size: 0.75rem;">Intervalo de mensaje de indicación de tráfico de entrega</div>
                                    </div>
                                </div>
                                <div class="row mb-3 align-items-start">
                                    <label class="col-sm-4 col-form-label text-md-end text-soft">Intervalo de tiempo para reprogramar GTK</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="wpa_group_rekey" placeholder="600">
                                        <div class="form-text text-muted" style="font-size: 0.75rem;">Seg</div>
                                    </div>
                                </div>
                                <div class="row mb-3 align-items-center">
                                    <label class="col-sm-4 col-form-label text-md-end text-soft">Desactivar sondeo de inactividad</label>
                                    <div class="col-sm-8">
                                        <input type="checkbox" class="form-check-input mt-2" name="disassoc_low_ack">
                                    </div>
                                </div>
                                <div class="row mb-3 align-items-start">
                                    <label class="col-sm-4 col-form-label text-md-end text-soft">Límite de inactividad de la estación</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="maxassoc" placeholder="300">
                                        <div class="form-text text-muted" style="font-size: 0.75rem;">Seg</div>
                                    </div>
                                </div>
                                <div class="row mb-3 align-items-center">
                                    <label class="col-sm-4 col-form-label text-md-end text-soft">Máximo permitido de intervalo de escucha</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="max_listen_int" placeholder="65535">
                                    </div>
                                </div>
                                <div class="row mb-0 align-items-start">
                                    <label class="col-sm-4 col-form-label text-md-end text-soft">Desasociarse en un reconocimiento bajo</label>
                                    <div class="col-sm-8">
                                        <input type="checkbox" class="form-check-input mt-2" name="disassoc_low_ack_check" checked>
                                        <div class="form-text text-muted" style="font-size: 0.75rem;">Permitir que el modo AP desconecte los clientes por una condición de ACK bajo</div>
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