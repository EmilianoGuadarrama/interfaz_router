@extends('layouts.dashboard')
@section('page-title', 'Sistema')
@section('content')
    <div class="container-fluid">

        @if(session('result_title'))
            <div class="alert {{ session('result_success') ? 'alert-success' : 'alert-danger' }} d-flex align-items-center mb-4">
                <i class="bi {{ session('result_success') ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-2"></i>
                <strong>{{ session('result_title') }}</strong>
            </div>
        @endif

        <div class="mb-4">
            <h3 class="fw-normal mb-1">Sistema</h3>
            <p class="text-muted small">Aquí puede configurar los aspectos básicos de su dispositivo, como el nombre del host o la zona horaria.</p>
        </div>

        <form action="{{ route('system.general.update') }}" method="POST" id="form-system-general">
            @csrf

            <div class="panel-card  border mb-4">
                <ul class="nav nav-tabs bg-light pt-2 px-2 border-bottom-0" id="systemTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-dark fw-semibold border-bottom-0 rounded-top" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">Configuración general</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-muted border-0 bg-transparent" data-bs-toggle="tab" data-bs-target="#logging" type="button" role="tab">Inicio de sesión</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-muted border-0 bg-transparent" data-bs-toggle="tab" data-bs-target="#ntp" type="button" role="tab">Sincronización horaria</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-muted border-0 bg-transparent" data-bs-toggle="tab" data-bs-target="#theme" type="button" role="tab">Idioma y Estilo</button>
                    </li>
                </ul>

                <div class="tab-content border-top" id="systemTabsContent">

                    <div class="tab-pane fade show active p-4" id="general" role="tabpanel">
                        <div class="row mb-4">
                            <label class="col-sm-3 col-form-label text-sm-end">Hora local</label>
                            <div class="col-sm-9">
                                <div class="mb-2" id="current-local-time">{{ \Carbon\Carbon::now()->format('d/m/Y, h:i:s a') }}</div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-info btn-sm text-white px-3" onclick="syncBrowserTime()">SINCRONIZAR CON EL NAVEGADOR</button>
                                    <button type="button" class="btn btn-info btn-sm text-white px-3">SINCRONIZAR CON EL SERVIDOR NTP</button>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="hostname" class="col-sm-3 col-form-label text-sm-end">Nombre de host</label>
                            <div class="col-sm-9">
                                <input type="text" name="hostname" id="hostname" class="form-control border-bottom" value="{{ old('hostname', $hostname) }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="description" class="col-sm-3 col-form-label text-sm-end">Descripción</label>
                            <div class="col-sm-9">
                                <input type="text" name="description" id="description" class="form-control border-bottom" value="{{ old('description', $description) }}">
                                <div class="form-text text-muted">Una breve descripción opcional de este dispositivo</div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="notes" class="col-sm-3 col-form-label text-sm-end">Notas</label>
                            <div class="col-sm-9">
                                <textarea name="notes" id="notes" rows="6" class="form-control">{{ old('notes', $notes) }}</textarea>
                                <div class="form-text text-muted">Notas opcionales de forma libre sobre este dispositivo</div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label for="timezone" class="col-sm-3 col-form-label text-sm-end">Zona horaria</label>
                            <div class="col-sm-9">
                                <select name="timezone" id="timezone" class="form-select border-bottom">
                                    <option value="UTC" {{ old('timezone', $timezone) == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="CST6CDT,M4.1.0,M10.5.0" {{ old('timezone', $timezone) == 'CST6CDT,M4.1.0,M10.5.0' ? 'selected' : '' }}>América/Ciudad de México</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade p-4" id="logging" role="tabpanel">
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label text-sm-end">Tamaño del buffer de registro del sistema</label>
                            <div class="col-sm-8 d-flex align-items-center">
                                <input type="number" name="log_size" class="form-control border-bottom w-50" value="{{ old('log_size', $log_size) }}">
                                <span class="ms-2 text-muted">kiB</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label text-sm-end">Servidor externo de registro del sistema</label>
                            <div class="col-sm-8">
                                <input type="text" name="log_ip" class="form-control border-bottom w-50" placeholder="0.0.0.0" value="{{ old('log_ip', $log_ip) }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label text-sm-end">Puerto del servidor externo de registro del sistema</label>
                            <div class="col-sm-8">
                                <input type="number" name="log_port" class="form-control border-bottom w-50" value="{{ old('log_port', $log_port) }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label text-sm-end">Protocolo de servidor de registro de sistema externo</label>
                            <div class="col-sm-8">
                                <select name="log_proto" class="form-select border-bottom w-50">
                                    <option value="udp" {{ old('log_proto', $log_proto) == 'udp' ? 'selected' : '' }}>UDP</option>
                                    <option value="tcp" {{ old('log_proto', $log_proto) == 'tcp' ? 'selected' : '' }}>TCP</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label text-sm-end">Escribe el registro del sistema al archivo</label>
                            <div class="col-sm-8">
                                <input type="text" name="log_file" class="form-control border-bottom w-50" value="{{ old('log_file', $log_file) }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label text-sm-end">Nivel de registro</label>
                            <div class="col-sm-8">
                                <select name="conloglevel" class="form-select border-bottom w-50">
                                    <option value="8" {{ old('conloglevel', $conloglevel) == '8' ? 'selected' : '' }}>Depurar</option>
                                    <option value="7" {{ old('conloglevel', $conloglevel) == '7' ? 'selected' : '' }}>Información</option>
                                    <option value="6" {{ old('conloglevel', $conloglevel) == '6' ? 'selected' : '' }}>Aviso</option>
                                    <option value="4" {{ old('conloglevel', $conloglevel) == '4' ? 'selected' : '' }}>Advertencia</option>
                                    <option value="3" {{ old('conloglevel', $conloglevel) == '3' ? 'selected' : '' }}>Error</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label text-sm-end">Nivel de registro de cron</label>
                            <div class="col-sm-8">
                                <select name="cronloglevel" class="form-select border-bottom w-50">
                                    <option value="8" {{ old('cronloglevel', $cronloglevel) == '8' ? 'selected' : '' }}>Depurar</option>
                                    <option value="5" {{ old('cronloglevel', $cronloglevel) == '5' ? 'selected' : '' }}>Normal</option>
                                    <option value="9" {{ old('cronloglevel', $cronloglevel) == '9' ? 'selected' : '' }}>Advertencia</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade p-4" id="ntp" role="tabpanel">
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label text-sm-end">Activar cliente NTP</label>
                            <div class="col-sm-8 d-flex align-items-center">
                                <input class="form-check-input" type="checkbox" name="ntp_client" value="1" {{ old('ntp_client', $ntp_client) ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label text-sm-end">Dar servicio NTP</label>
                            <div class="col-sm-8 d-flex align-items-center">
                                <input class="form-check-input" type="checkbox" name="ntp_server" value="1" {{ old('ntp_server', $ntp_server) ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label text-sm-end">Usar servidores anunciados por DHCP</label>
                            <div class="col-sm-8 d-flex align-items-center">
                                <input class="form-check-input" type="checkbox" name="ntp_dhcp" value="1" {{ old('ntp_dhcp', $ntp_dhcp) ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label text-sm-end">Servidores NTP a consultar</label>
                            <div class="col-sm-8">
                                <div id="ntp-servers-container" class="w-50">
                                    @foreach(old('ntp_servers', $ntp_servers) as $index => $server)
                                        <div class="input-group mb-2 ntp-row">
                                            <input type="text" name="ntp_servers[]" class="form-control border-bottom" value="{{ $server }}">
                                            <button class="btn btn-danger btn-sm rounded-1 ms-2" type="button" onclick="removeNtpRow(this)"><i class="bi bi-x"></i></button>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="btn btn-primary btn-sm rounded-1 mt-1" type="button" onclick="addNtpRow()"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade p-4" id="theme" role="tabpanel">
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label text-sm-end">Idioma</label>
                            <div class="col-sm-8">
                                <select name="lang" class="form-select border-bottom w-50">
                                    <option value="auto" {{ old('lang', $lang) == 'auto' ? 'selected' : '' }}>auto</option>
                                    <option value="es" {{ old('lang', $lang) == 'es' ? 'selected' : '' }}>Español</option>
                                    <option value="en" {{ old('lang', $lang) == 'en' ? 'selected' : '' }}>Inglés</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label text-sm-end">Diseño</label>
                            <div class="col-sm-8">
                                <select name="theme" class="form-select border-bottom w-50">
                                    <option value="material" {{ old('theme', $theme) == 'material' ? 'selected' : '' }}>Material</option>
                                    <option value="bootstrap" {{ old('theme', $theme) == 'bootstrap' ? 'selected' : '' }}>Bootstrap</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-light d-flex justify-content-end p-3 border-top gap-2">
                    <button type="submit" name="action" value="save_apply" class="btn btn-info text-white">GUARDAR Y APLICAR <i class="bi bi-caret-down-fill ms-1" style="font-size: 0.8em;"></i></button>
                    <button type="submit" name="action" value="save" class="btn btn-secondary" style="background-color: #337ab7; border-color: #2e6da4;">GUARDAR</button>
                    <button type="reset" class="btn btn-danger" style="background-color: #d9534f; border-color: #d43f3a;">RESTABLECER</button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Lógica para añadir filas dinámicas de servidores NTP
            function addNtpRow() {
                const container = document.getElementById('ntp-servers-container');
                const row = document.createElement('div');
                row.className = 'input-group mb-2 ntp-row';
                row.innerHTML = `
            <input type="text" name="ntp_servers[]" class="form-control border-bottom" placeholder="0.pool.ntp.org">
            <button class="btn btn-danger btn-sm rounded-1 ms-2" type="button" onclick="removeNtpRow(this)"><i class="bi bi-x"></i></button>
        `;
                container.appendChild(row);
            }

            function removeNtpRow(button) {
                button.closest('.ntp-row').remove();
            }

            // Sincronizar tiempo con el navegador
            function syncBrowserTime() {
                const now = new Date();
                const formatted = now.toLocaleString('es-MX');
                document.getElementById('current-local-time').innerText = formatted;
                // Si quisieras aplicar la hora del navagador al router de inmediato:
                // tendrías que hacer un fetch/axios a una ruta de tu controlador que envíe el comando 'date -s'
            }
        </script>
    @endpush
@endsection
