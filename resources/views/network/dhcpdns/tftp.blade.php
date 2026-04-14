@extends('layouts.dashboard')

@section('title', 'DHCP y DNS - Configuración TFTP')

@section('content')
    @php
        $config = $config ?? [
            'enable_tftp' => false,
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
                    <a class="nav-link active" href="{{ route('network.dhcpdns.tftp') }}">Configuración TFTP</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('network.dhcpdns.advanced') }}">Configuración avanzada</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('network.dhcpdns.static') }}">Asignaciones estáticas</a>
                </li>
            </ul>

            <form id="tftpForm" action="{{ route('network.dhcpdns.tftp.update') }}" method="POST">
                @csrf

                <div class="form-check mb-4">
                    <input
                        class="form-check-input custom-check"
                        type="checkbox"
                        name="enable_tftp"
                        id="enable_tftp"
                        {{ !empty($config['enable_tftp']) ? 'checked' : '' }}
                    >
                    <label class="form-check-label text-light" for="enable_tftp">
                        Activar servidor TFTP
                    </label>
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
        .custom-check {
            width: 1.1rem;
            height: 1.1rem;
            margin-top: 0.2rem;
        }

        .form-check-label {
            font-size: 1rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
        });
    </script>
@endsection
