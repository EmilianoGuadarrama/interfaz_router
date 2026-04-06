@extends('layouts.dashboard')

@section('title', 'DHCP y DNS - Configuración avanzada')

@section('content')
    @php
        $config = $config ?? [
            'suppress_log' => false,
            'bogus_filter' => false,
            'sequential_ip' => false,
            'localise_queries' => true,
            'private_filter' => true,
            'expand_hosts' => true,
            'additional_servers_file' => '',
            'bogus_nxdomain' => '67.215.65.132',
            'dns_port' => 53,
            'dns_query_port' => 'cualquiera',
            'dhcp_max' => 'ilimitado',
            'edns_packet_max' => 1280,
            'dns_forward_max' => 150,
            'cache_size' => 150,
        ];
    @endphp

    <div class="container-fluid">
        <h2 class="page-title mb-3">DHCP y DNS</h2>

        @if(session('success'))
            <div class="alert alert-success rounded-4 mb-3">
                {{ session('success') }}
            </div>
        @endif

        <div id="changeAlert" class="alert alert-warning rounded-4 mb-3 d-none">
            Se detectaron cambios sin guardar.
        </div>

        <div class="panel-card">
            <ul class="nav nav-tabs mb-4 border-0">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('network.dhcpdns.general') }}">Configuración general</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('network.dhcpdns.resolv') }}">Archivos Resolv y Hosts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('network.dhcpdns.tftp') }}">Configuración TFTP</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('network.dhcpdns.advanced') }}">Configuración avanzada</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('network.dhcpdns.static') }}">Asignaciones estáticas</a>
                </li>
            </ul>

            <form id="advancedForm" action="{{ route('network.dhcpdns.advanced.update') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="form-check mb-4">
                            <input class="form-check-input custom-check" type="checkbox" name="suppress_log" id="suppress_log"
                                {{ !empty($config['suppress_log']) ? 'checked' : '' }}>
                            <label class="form-check-label text-light" for="suppress_log">Suprimir el registro</label>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input custom-check" type="checkbox" name="bogus_filter" id="bogus_filter"
                                {{ !empty($config['bogus_filter']) ? 'checked' : '' }}>
                            <label class="form-check-label text-light" for="bogus_filter">Filtro inútil</label>
                        </div>

                        <div class="mb-4">
                            <label for="additional_servers_file" class="form-label text-light">Archivo de servidores adicionales</label>
                            <input type="text" class="form-control custom-input" id="additional_servers_file" name="additional_servers_file"
                                   value="{{ old('additional_servers_file', $config['additional_servers_file'] ?? '') }}">
                        </div>

                        <div class="mb-4">
                            <label for="dns_port" class="form-label text-light">Puerto del servidor DNS</label>
                            <input type="number" class="form-control custom-input" id="dns_port" name="dns_port"
                                   value="{{ old('dns_port', $config['dns_port'] ?? 53) }}">
                        </div>

                        <div class="mb-4">
                            <label for="edns_packet_max" class="form-label text-light">Máx. tamaño del paquete EDNS0</label>
                            <input type="number" class="form-control custom-input" id="edns_packet_max" name="edns_packet_max"
                                   value="{{ old('edns_packet_max', $config['edns_packet_max'] ?? 1280) }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-check mb-4">
                            <input class="form-check-input custom-check" type="checkbox" name="sequential_ip" id="sequential_ip"
                                {{ !empty($config['sequential_ip']) ? 'checked' : '' }}>
                            <label class="form-check-label text-light" for="sequential_ip">Asignar IPs secuencialmente</label>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input custom-check" type="checkbox" name="localise_queries" id="localise_queries"
                                {{ !empty($config['localise_queries']) ? 'checked' : '' }}>
                            <label class="form-check-label text-light" for="localise_queries">Localizar consultas</label>
                        </div>

                        <div class="mb-4">
                            <label for="bogus_nxdomain" class="form-label text-light">Ignorar dominio falso NX</label>
                            <input type="text" class="form-control custom-input" id="bogus_nxdomain" name="bogus_nxdomain"
                                   value="{{ old('bogus_nxdomain', $config['bogus_nxdomain'] ?? '67.215.65.132') }}">
                        </div>

                        <div class="mb-4">
                            <label for="dns_query_port" class="form-label text-light">Puerto de consultas al DNS</label>
                            <input type="text" class="form-control custom-input" id="dns_query_port" name="dns_query_port"
                                   value="{{ old('dns_query_port', $config['dns_query_port'] ?? 'cualquiera') }}">
                        </div>

                        <div class="mb-4">
                            <label for="dns_forward_max" class="form-label text-light">Máx. consultas simultáneas</label>
                            <input type="number" class="form-control custom-input" id="dns_forward_max" name="dns_forward_max"
                                   value="{{ old('dns_forward_max', $config['dns_forward_max'] ?? 150) }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-check mb-4">
                            <input class="form-check-input custom-check" type="checkbox" name="private_filter" id="private_filter"
                                {{ !empty($config['private_filter']) ? 'checked' : '' }}>
                            <label class="form-check-label text-light" for="private_filter">Filtro privado</label>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input custom-check" type="checkbox" name="expand_hosts" id="expand_hosts"
                                {{ !empty($config['expand_hosts']) ? 'checked' : '' }}>
                            <label class="form-check-label text-light" for="expand_hosts">Expandir hosts</label>
                        </div>

                        <div class="mb-4">
                            <label for="dhcp_max" class="form-label text-light">Máximo de asignaciones DHCP</label>
                            <input type="text" class="form-control custom-input" id="dhcp_max" name="dhcp_max"
                                   value="{{ old('dhcp_max', $config['dhcp_max'] ?? 'ilimitado') }}">
                        </div>

                        <div class="mb-4">
                            <label for="cache_size" class="form-label text-light">Tamaño de la caché DNS</label>
                            <input type="number" class="form-control custom-input" id="cache_size" name="cache_size"
                                   value="{{ old('cache_size', $config['cache_size'] ?? 150) }}">
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

        .custom-check {
            width: 1.1rem;
            height: 1.1rem;
            margin-top: 0.2rem;
        }

        .form-check-label {
            font-size: 1rem;
        }

        .form-label {
            font-size: 1rem;
            margin-bottom: .5rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('advancedForm');
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
