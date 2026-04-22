@extends('layouts.dashboard')

@section('title', 'Conmutador - VLANs')

@section('content')
    @php
        $vlans = $vlans ?? [];
        $availablePorts = $availablePorts ?? [];
    @endphp

    <div class="container-fluid">
        <h2 class="page-title mb-3">Conmutador</h2>

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

        @if($errors->any())
            <div class="alert alert-danger rounded-4 mb-3">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="panel-card">
            <ul class="nav nav-tabs mb-4 border-0">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('red.conmutador.general') }}">Configuración general</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('red.conmutador.vlans') }}">VLANs</a>
                </li>
            </ul>

            <p class="text-light mb-4">
                Configure las VLANs del conmutador. Cada VLAN define un grupo de puertos que pueden comunicarse entre sí. Use etiquetado para trunk ports y desetiquetado para puertos de acceso.
            </p>

            {{-- Tabla de VLANs existentes --}}
            <div class="table-responsive mb-4">
                <table class="table-dark-custom">
                    <thead>
                    <tr>
                        <th>VLAN ID</th>
                        <th>Dispositivo</th>
                        <th>Puertos</th>
                        <th style="width: 180px;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($vlans as $vlan)
                        <tr>
                            <td>
                                <span class="soft-badge">{{ $vlan['vid'] ?? $vlan['vlan'] ?? '-' }}</span>
                            </td>
                            <td>{{ $vlan['device'] ?? 'switch0' }}</td>
                            <td>
                                @php
                                    $portsStr = $vlan['ports'] ?? '';
                                    $portsList = array_filter(explode(' ', $portsStr));
                                @endphp
                                @foreach($portsList as $p)
                                    @php
                                        $isTagged = str_ends_with($p, 't');
                                        $portNum = rtrim($p, 't');
                                    @endphp
                                    <span class="badge {{ $isTagged ? 'bg-warning text-dark' : 'bg-primary' }} me-1">
                                        P{{ $portNum }}{{ $isTagged ? ' (T)' : '' }}
                                    </span>
                                @endforeach
                                @if(empty($portsList))
                                    <span class="text-muted">Sin puertos</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalEditVlan{{ $vlan['index'] ?? $loop->index }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('red.conmutador.vlans.destroy', $vlan['index'] ?? $loop->index) }}" method="POST" onsubmit="return confirm('¿Eliminar esta VLAN?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No hay VLANs configuradas</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Botón para agregar VLAN --}}
            <button type="button" class="btn btn-main mb-4" data-bs-toggle="modal" data-bs-target="#modalAgregarVlan">
                <i class="bi bi-plus-lg me-1"></i> Añadir VLAN
            </button>

            {{-- Tabla de configuración de puertos por VLAN --}}
            @if(count($vlans) > 0 && count($availablePorts) > 0)
                <h4 class="text-light mb-3 mt-4">Configuración de puertos por VLAN</h4>

                @foreach($vlans as $vlan)
                    @php
                        $vlanIdx = $vlan['index'] ?? $loop->index;
                        $portsStr = $vlan['ports'] ?? '';
                        $portsList = array_filter(explode(' ', $portsStr));
                        $portModes = [];
                        foreach ($portsList as $p) {
                            $isTagged = str_ends_with($p, 't');
                            $portNum = (int) rtrim($p, 't');
                            $portModes[$portNum] = $isTagged ? 'tagged' : 'untagged';
                        }
                    @endphp

                    <div class="panel-card mb-3" style="background: rgba(255,255,255,0.03);">
                        <h6 class="text-light fw-bold mb-3">
                            VLAN {{ $vlan['vid'] ?? $vlan['vlan'] ?? '-' }}
                        </h6>

                        <form action="{{ route('red.conmutador.ports.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="vlan_index" value="{{ $vlanIdx }}">

                            <div class="table-responsive">
                                <table class="table-dark-custom">
                                    <thead>
                                    <tr>
                                        @foreach($availablePorts as $port)
                                            <th class="text-center" style="min-width: 100px;">{{ $port['label'] }}</th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        @foreach($availablePorts as $port)
                                            @php $mode = $portModes[$port['number']] ?? 'off'; @endphp
                                            <td class="text-center">
                                                <select name="port_config[{{ $port['number'] }}]" class="form-select form-select-sm port-select" style="min-width: 120px;" onchange="updateSelectColor(this)">
                                                    <option value="off" {{ $mode === 'off' ? 'selected' : '' }}>Apagado</option>
                                                    <option value="untagged" {{ $mode === 'untagged' ? 'selected' : '' }}>Desetiquetado</option>
                                                    <option value="tagged" {{ $mode === 'tagged' ? 'selected' : '' }}>Etiquetado</option>
                                                </select>
                                            </td>
                                        @endforeach
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-sm btn-main">
                                    <i class="bi bi-check-lg me-1"></i> Aplicar puertos
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            @endif

            <div class="d-flex gap-2 mt-4">
                <a href="{{ route('red.conmutador.vlans') }}" class="btn btn-outline-light">Refrescar</a>
            </div>
        </div>
    </div>

    {{-- Modal: Agregar VLAN --}}
    <div class="modal fade" id="modalAgregarVlan" tabindex="-1" aria-labelledby="modalAgregarVlanLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4" style="background: #243b6b; color: #fff;">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="modalAgregarVlanLabel">
                        <i class="bi bi-plus-circle me-2" style="color:#4a86f7;"></i>Añadir VLAN
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form action="{{ route('red.conmutador.vlans.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="vlan_id" class="form-label text-light fw-semibold">ID de VLAN</label>
                            <input type="number" class="form-control @error('vlan_id') is-invalid @enderror"
                                   id="vlan_id" name="vlan_id"
                                   min="1" max="4094"
                                   value="{{ old('vlan_id') }}"
                                   placeholder="Ej: 10"
                                   required>
                            @error('vlan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Rango válido: 1–4094</small>
                        </div>

                        <div class="mb-3">
                            <label for="ports" class="form-label text-light fw-semibold">Puertos (opcional)</label>
                            <input type="text" class="form-control @error('ports') is-invalid @enderror"
                                   id="ports" name="ports"
                                   value="{{ old('ports') }}"
                                   placeholder="Ej: 0 1 2 3 5t">
                            @error('ports')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Formato: números separados por espacio, añadir 't' para etiquetado.</small>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-main">
                            <i class="bi bi-check-lg me-1"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modales: Editar VLAN --}}
    @foreach($vlans as $vlan)
        @php $vlanIdx = $vlan['index'] ?? $loop->index; @endphp
        <div class="modal fade" id="modalEditVlan{{ $vlanIdx }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4" style="background: #243b6b; color: #fff;">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-pencil me-2" style="color:#4a86f7;"></i>Editar VLAN {{ $vlan['vid'] ?? $vlan['vlan'] ?? '' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <form action="{{ route('red.conmutador.vlans.update', $vlanIdx) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label text-light fw-semibold">Puertos</label>
                                <input type="text" class="form-control" name="ports"
                                       value="{{ $vlan['ports'] ?? '' }}"
                                       placeholder="Ej: 0 1 2 3 5t">
                                <small class="text-muted">Formato: números separados por espacio, añadir 't' para etiquetado.</small>
                            </div>
                        </div>

                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" name="submit_action" value="apply" class="btn btn-main">
                                <i class="bi bi-check-lg me-1"></i> Guardar y aplicar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                new bootstrap.Modal(document.getElementById('modalAgregarVlan')).show();
            });
        </script>
    @endif

    <style>
        .port-select {
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        .port-select.mode-off {
            background-color: rgba(220, 53, 69, 0.2); /* Red tint */
            border-color: #dc3545;
        }
        .port-select.mode-untagged {
            background-color: rgba(13, 110, 253, 0.2); /* Blue tint */
            border-color: #0d6efd;
        }
        .port-select.mode-tagged {
            background-color: rgba(255, 193, 7, 0.2); /* Yellow tint */
            border-color: #ffc107;
            color: #ffc107;
        }
        .port-select option {
            background-color: #243b6b; /* Base dark theme bg */
            color: #fff;
        }
    </style>

    <script>
        function updateSelectColor(selectElement) {
            // Remove previous classes
            selectElement.classList.remove('mode-off', 'mode-untagged', 'mode-tagged');
            // Add new class based on value
            const val = selectElement.value;
            if (val === 'off') selectElement.classList.add('mode-off');
            else if (val === 'untagged') selectElement.classList.add('mode-untagged');
            else if (val === 'tagged') selectElement.classList.add('mode-tagged');
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Initialize colors
            document.querySelectorAll('.port-select').forEach(function(select) {
                updateSelectColor(select);
            });
        });
    </script>
@endsection
