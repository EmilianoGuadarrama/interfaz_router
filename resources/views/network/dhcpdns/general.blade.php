@extends('layouts.dashboard')

@section('title', 'DHCP y DNS - Configuración general')

@section('content')
    @php
        $config = $config ?? [
            'local_service' => '/lan/',
            'local_domain' => 'lan',
            'dns_forwardings' => '/example.org/10.1.2.3',
            'domain_whitelist' => 'ihost.netflix.com',
            'require_domain' => true,
            'authoritative' => true,
            'log_queries' => false,
            'local_only' => true,
        ];
    @endphp

    <div class="container-fluid">
        <h2 class="page-title mb-3">DHCP y DNS</h2>

        @if(session('success'))
            <div class="alert alert-success rounded-4 mb-3">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger rounded-4 mb-3">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div id="changeAlert" class="alert alert-warning rounded-4 mb-3 d-none">
            Se detectaron cambios sin guardar.
        </div>

        <div class="panel-card">
            <ul class="nav nav-tabs mb-4 border-0">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('network.dhcpdns.general') }}">Configuración general</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('network.dhcpdns.resolv') }}">Archivos Resolv y Hosts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('network.dhcpdns.tftp') }}">Configuración TFTP</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('network.dhcpdns.advanced') }}">Configuración avanzada</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('network.dhcpdns.static') }}">Asignaciones estáticas</a>
                </li>
            </ul>

            <form id="generalForm" action="{{ route('network.dhcpdns.general.update') }}" method="POST" novalidate>
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="local_service" class="form-label text-light">Servidor local</label>
                            <input
                                type="text"
                                class="form-control custom-input @error('local_service') is-invalid @enderror"
                                id="local_service"
                                name="local_service"
                                maxlength="255"
                                placeholder="/lan/"
                                value="{{ old('local_service', $config['local_service'] ?? '') }}"
                            >
                            @error('local_service')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="dns_forwardings" class="form-label text-light">Reenvíos de DNS</label>
                            <input
                                type="text"
                                class="form-control custom-input @error('dns_forwardings') is-invalid @enderror"
                                id="dns_forwardings"
                                name="dns_forwardings"
                                maxlength="255"
                                placeholder="/example.org/10.1.2.3"
                                value="{{ old('dns_forwardings', $config['dns_forwardings'] ?? '') }}"
                            >
                            @error('dns_forwardings')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="local_domain" class="form-label text-light">Dominio local</label>
                            <input
                                type="text"
                                class="form-control custom-input @error('local_domain') is-invalid @enderror"
                                id="local_domain"
                                name="local_domain"
                                maxlength="255"
                                placeholder="lan"
                                value="{{ old('local_domain', $config['local_domain'] ?? '') }}"
                            >
                            @error('local_domain')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="domain_whitelist" class="form-label text-light">Lista blanca de dominios</label>
                            <input
                                type="text"
                                class="form-control custom-input @error('domain_whitelist') is-invalid @enderror"
                                id="domain_whitelist"
                                name="domain_whitelist"
                                maxlength="255"
                                placeholder="ihost.netflix.com"
                                value="{{ old('domain_whitelist', $config['domain_whitelist'] ?? '') }}"
                            >
                            @error('domain_whitelist')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-3">
                        <div class="form-check mb-3">
                            <input
                                class="form-check-input custom-check"
                                type="checkbox"
                                name="require_domain"
                                id="require_domain"
                                {{ old('require_domain', !empty($config['require_domain'])) ? 'checked' : '' }}
                            >
                            <label class="form-check-label text-light" for="require_domain">
                                Requerir dominio
                            </label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-check mb-3">
                            <input
                                class="form-check-input custom-check"
                                type="checkbox"
                                name="authoritative"
                                id="authoritative"
                                {{ old('authoritative', !empty($config['authoritative'])) ? 'checked' : '' }}
                            >
                            <label class="form-check-label text-light" for="authoritative">
                                Autorizar
                            </label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-check mb-3">
                            <input
                                class="form-check-input custom-check"
                                type="checkbox"
                                name="log_queries"
                                id="log_queries"
                                {{ old('log_queries', !empty($config['log_queries'])) ? 'checked' : '' }}
                            >
                            <label class="form-check-label text-light" for="log_queries">
                                Registrar consultas
                            </label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-check mb-3">
                            <input
                                class="form-check-input custom-check"
                                type="checkbox"
                                name="local_only"
                                id="local_only"
                                {{ old('local_only', !empty($config['local_only'])) ? 'checked' : '' }}
                            >
                            <label class="form-check-label text-light" for="local_only">
                                Solo servicio local
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
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

        .form-check-label,
        .form-label {
            font-size: 1rem;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }

        .invalid-feedback {
            display: block;
            color: #ffb3b3;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('generalForm');
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
