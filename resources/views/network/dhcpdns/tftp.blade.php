@extends('layouts.dashboard')

@section('title', 'DHCP y DNS - Configuración TFTP')

@section('content')
    @php
        $config = $config ?? [
            'enable_tftp' => false,
            'tftp_root' => '/srv/tftp',
            'network_boot_image' => '',
        ];
    @endphp

    <div class="container-fluid">
        <h2 class="page-title mb-3">DHCP y DNS</h2>

        @if(session('success'))
            <div class="alert alert-success rounded-4 mb-3">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger rounded-4 mb-3">
                {{ session('error') }}
            </div>
        @endif

        <div id="changeAlert" class="alert alert-warning rounded-4 mb-3 d-none">
            Se detectaron cambios sin guardar.
        </div>

        <div class="panel-card">
            <ul class="nav nav-tabs mb-4 border-0">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('red.dhcpdns.general') }}">Configuración general</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('red.dhcpdns.resolv') }}">Archivos Resolv y Hosts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('red.dhcpdns.tftp') }}">Configuración TFTP</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('red.dhcpdns.advanced') }}">Configuración avanzada</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('red.dhcpdns.static') }}">Asignaciones estáticas</a>
                </li>
            </ul>

            <form id="tftpForm" action="{{ route('red.dhcpdns.tftp.update') }}" method="POST">
                @csrf

                <!-- Checkbox Activar TFTP -->
                <div class="form-check form-switch mb-4 d-flex align-items-center gap-2">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        name="enable_tftp"
                        id="enable_tftp"
                        style="width: 2.5em; height: 1.25em; cursor: pointer;"
                        {{ !empty($config['enable_tftp']) ? 'checked' : '' }}
                    >
                    <label class="form-check-label text-light fw-semibold m-0" for="enable_tftp" style="cursor: pointer;">
                        Activar servidor TFTP
                    </label>
                </div>

                <!-- Campos Dependientes TFTP -->
                <div id="tftp_fields" class="mt-3 {{ empty($config['enable_tftp']) ? 'd-none' : '' }}">
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-3">
                            <label for="tftp_root" class="form-label text-light mb-0">Raíz del servidor TFTP</label>
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control" id="tftp_root" name="tftp_root"
                                   value="{{ old('tftp_root', $config['tftp_root'] ?? '/srv/tftp') }}">
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Directorio base para archivos TFTP.</small>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-3">
                            <label for="network_boot_image" class="form-label text-light mb-0">Imagen de arranque en red</label>
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control" id="network_boot_image" name="network_boot_image"
                                   value="{{ old('network_boot_image', $config['network_boot_image'] ?? '') }}"
                                   placeholder="Ej: pxelinux.0">
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Archivo de arranque para clientes PXE.</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-5">
                    <button type="submit" name="submit_action" value="apply" class="btn btn-main">
                        Guardar y aplicar
                    </button>
                    <button type="submit" name="submit_action" value="save" class="btn btn-outline-light">
                        Guardar
                    </button>
                </div>
            </form>
        </div>

        @include('network.partials.result')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Manejo del estado modificado
            const form = document.getElementById('tftpForm');
            const alertBox = document.getElementById('changeAlert');
            const fields = form.querySelectorAll('input, select, textarea');
            let changed = false;
            let submitting = false;

            function showChangeAlert() {
                if (!changed) {
                    changed = true;
                    alertBox.classList.remove('d-none');
                }
            }

            fields.forEach(field => {
                field.addEventListener('input', showChangeAlert);
                field.addEventListener('change', showChangeAlert);
            });

            form.addEventListener('submit', function () {
                submitting = true;
                changed = false;
                alertBox.classList.add('d-none');
            });

            window.addEventListener('beforeunload', function (e) {
                if (changed && !submitting) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            // Lógica para mostrar/ocultar campos dependientes TFTP
            const enableTftpCheck = document.getElementById('enable_tftp');
            const tftpFieldsBlock = document.getElementById('tftp_fields');

            function toggleTftpFields() {
                if (enableTftpCheck.checked) {
                    tftpFieldsBlock.classList.remove('d-none');
                } else {
                    tftpFieldsBlock.classList.add('d-none');
                }
            }

            enableTftpCheck.addEventListener('change', toggleTftpFields);
        });
    </script>
@endsection
