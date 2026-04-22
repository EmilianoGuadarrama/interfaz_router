@extends('layouts.dashboard')

@section('title', 'DHCP y DNS - Archivos Resolv y Hosts')

@section('content')
    @php
        $config = $config ?? [
            'use_ethers' => true,
            'ignore_resolv' => false,
            'ignore_hosts' => false,
            'lease_file' => '/tmp/dhcp.leases',
            'resolv_file' => '/tmp/resolv.conf.auto',
            'additional_hosts' => '',
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
                    <a class="nav-link active" href="{{ route('red.dhcpdns.resolv') }}">Archivos Resolv y Hosts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('red.dhcpdns.tftp') }}">Configuración TFTP</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('red.dhcpdns.advanced') }}">Configuración avanzada</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('red.dhcpdns.static') }}">Asignaciones estáticas</a>
                </li>
            </ul>

            <form id="resolvForm" action="{{ route('red.dhcpdns.resolv.update') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="form-check form-switch mb-4 d-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="use_ethers" id="use_ethers" style="width: 2.5em; height: 1.25em; cursor: pointer;"
                                {{ !empty($config['use_ethers']) ? 'checked' : '' }}>
                            <label class="form-check-label text-light fw-semibold m-0" for="use_ethers" style="cursor: pointer;">Usar /etc/ethers</label>
                        </div>

                        <div class="form-check form-switch mb-4 d-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="ignore_resolv" id="ignore_resolv" style="width: 2.5em; height: 1.25em; cursor: pointer;"
                                {{ !empty($config['ignore_resolv']) ? 'checked' : '' }}>
                            <label class="form-check-label text-light fw-semibold m-0" for="ignore_resolv" style="cursor: pointer;">Ignorar archivo resolve</label>
                        </div>

                        <div class="form-check form-switch mb-4 d-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="ignore_hosts" id="ignore_hosts" style="width: 2.5em; height: 1.25em; cursor: pointer;"
                                {{ !empty($config['ignore_hosts']) ? 'checked' : '' }}>
                            <label class="form-check-label text-light fw-semibold m-0" for="ignore_hosts" style="cursor: pointer;">Ignorar /etc/hosts</label>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="mb-4">
                            <label for="lease_file" class="form-label text-light">Archivo de asignación</label>
                            <input type="text" class="form-control custom-input" id="lease_file" name="lease_file"
                                   value="{{ old('lease_file', $config['lease_file'] ?? '/tmp/dhcp.leases') }}">
                        </div>

                        <div class="mb-4">
                            <label for="resolv_file" class="form-label text-light">Archivo de resolución</label>
                            <input type="text" class="form-control custom-input" id="resolv_file" name="resolv_file"
                                   value="{{ old('resolv_file', $config['resolv_file'] ?? '/tmp/resolv.conf.auto') }}">
                        </div>

                        <div class="mb-4">
                            <label for="additional_hosts" class="form-label text-light">Archivos de hosts adicionales</label>
                            <input type="text" class="form-control custom-input" id="additional_hosts" name="additional_hosts"
                                   value="{{ old('additional_hosts', $config['additional_hosts'] ?? '') }}">
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" name="submit_action" value="apply" class="btn btn-main">Guardar y aplicar</button>
                    <button type="submit" name="submit_action" value="save" class="btn btn-outline-light">Guardar</button>
                </div>
            </form>
        </div>

        @include('network.partials.result')
    </div>

    <style>
        .custom-input {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.08);
            color: #fff;
            border-radius: 18px;
            padding: 12px 16px;
            font-size: 1rem;
        }

        .custom-input:focus {
            background: rgba(255,255,255,0.10);
            color: #fff;
            border-color: #5b8cff;
            box-shadow: 0 0 0 0.15rem rgba(91, 140, 255, 0.25);
        }

        .custom-input::placeholder {
            color: rgba(255,255,255,0.55);
        }

        /* form-switch active state */
        .form-switch .form-check-input:checked {
            background-color: #5b8cff;
            border-color: #5b8cff;
        }

        .form-check-label,
        .form-label {
            font-size: 1rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('resolvForm');
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
        });
    </script>
@endsection
