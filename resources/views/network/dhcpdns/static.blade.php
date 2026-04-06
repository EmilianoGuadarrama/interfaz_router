@extends('layouts.dashboard')

@section('title', 'DHCP y DNS - Asignaciones estáticas')

@section('content')
    @php
        $staticAssignments = $staticAssignments ?? [];
        $activeLeases = $activeLeases ?? [
            [
                'host_name' => 'Susu',
                'ipv4_address' => '192.168.10.180',
                'mac_address' => '50:EB:F6:D1:96:1E',
                'remaining_time' => '11h 56m 51s',
            ]
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
                    <a class="nav-link" href="{{ route('network.dhcpdns.advanced') }}">Configuración avanzada</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('network.dhcpdns.static') }}">Asignaciones estáticas</a>
                </li>
            </ul>

            <p class="text-light">
                Las asignaciones estáticas se usan para asignar direcciones IP fijas y nombres identificativos a dispositivos o clientes DHCP.
            </p>

            <div class="table-responsive mb-4">
                <table class="table-dark-custom">
                    <thead>
                    <tr>
                        <th>Nombre de host</th>
                        <th>Dirección MAC</th>
                        <th>Dirección IPv4</th>
                        <th>Tiempo de asignación</th>
                        <th>DUID</th>
                        <th>Sufijo IPv6</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($staticAssignments as $index => $assignment)
                        <tr>
                            <td>{{ $assignment['host_name'] ?? '' }}</td>
                            <td>{{ $assignment['mac_address'] ?? '' }}</td>
                            <td>{{ $assignment['ipv4_address'] ?? '' }}</td>
                            <td>{{ $assignment['lease_time'] ?? '' }}</td>
                            <td>{{ $assignment['duid'] ?? '' }}</td>
                            <td>{{ $assignment['ipv6_suffix'] ?? '' }}</td>
                            <td>
                                <form action="{{ route('network.dhcpdns.static.destroy', $index) }}" method="POST" onsubmit="return confirm('¿Eliminar esta asignación estática?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Esta sección aún no contiene valores</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <button type="button" class="btn btn-main mb-4" data-bs-toggle="modal" data-bs-target="#modalAgregarAsignacion">
                Añadir
            </button>

            <h4 class="text-light mb-3">Asignaciones DHCP activas</h4>

            <div class="table-responsive">
                <table class="table-dark-custom">
                    <thead>
                    <tr>
                        <th>Nombre de host</th>
                        <th>Dirección IPv4</th>
                        <th>Dirección MAC</th>
                        <th>Tiempo de asignación restante</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($activeLeases as $lease)
                        <tr>
                            <td>{{ $lease['host_name'] ?? '' }}</td>
                            <td>{{ $lease['ipv4_address'] ?? '' }}</td>
                            <td>{{ $lease['mac_address'] ?? '' }}</td>
                            <td>{{ $lease['remaining_time'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No hay asignaciones DHCP activas</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <form action="{{ route('network.dhcpdns.static.update') }}" method="POST" class="d-flex gap-2 mt-4">
                @csrf
                <button type="submit" name="submit_action" value="apply" class="btn btn-main">Guardar y aplicar</button>
                <button type="submit" name="submit_action" value="save" class="btn btn-outline-light">Guardar</button>
                <a href="{{ route('network.dhcpdns.static') }}" class="btn btn-danger">Restablecer</a>
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalAgregarAsignacion" tabindex="-1" aria-labelledby="modalAgregarAsignacionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4" style="background: #243b6b; color: #fff;">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="modalAgregarAsignacionLabel">Añadir asignación estática</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form action="{{ route('network.dhcpdns.static.store') }}" method="POST">
                    @csrf

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="host_name" class="form-label text-light">Nombre de host</label>
                                <input
                                    type="text"
                                    id="host_name"
                                    name="host_name"
                                    class="form-control @error('host_name') is-invalid @enderror"
                                    value="{{ old('host_name') }}"
                                    required
                                >
                                @error('host_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="mac_address" class="form-label text-light">Dirección MAC</label>
                                <input
                                    type="text"
                                    id="mac_address"
                                    name="mac_address"
                                    class="form-control @error('mac_address') is-invalid @enderror"
                                    placeholder="AA:BB:CC:DD:EE:FF"
                                    value="{{ old('mac_address') }}"
                                    required
                                >
                                @error('mac_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="ipv4_address" class="form-label text-light">Dirección IPv4</label>
                                <input
                                    type="text"
                                    id="ipv4_address"
                                    name="ipv4_address"
                                    class="form-control @error('ipv4_address') is-invalid @enderror"
                                    placeholder="192.168.10.50"
                                    value="{{ old('ipv4_address') }}"
                                    required
                                >
                                @error('ipv4_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="lease_time" class="form-label text-light">Tiempo de asignación</label>
                                <input
                                    type="text"
                                    id="lease_time"
                                    name="lease_time"
                                    class="form-control @error('lease_time') is-invalid @enderror"
                                    placeholder="12h"
                                    value="{{ old('lease_time') }}"
                                >
                                @error('lease_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="duid" class="form-label text-light">DUID</label>
                                <input
                                    type="text"
                                    id="duid"
                                    name="duid"
                                    class="form-control @error('duid') is-invalid @enderror"
                                    value="{{ old('duid') }}"
                                >
                                @error('duid')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="ipv6_suffix" class="form-label text-light">Sufijo IPv6</label>
                                <input
                                    type="text"
                                    id="ipv6_suffix"
                                    name="ipv6_suffix"
                                    class="form-control @error('ipv6_suffix') is-invalid @enderror"
                                    value="{{ old('ipv6_suffix') }}"
                                >
                                @error('ipv6_suffix')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-main">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let modal = new bootstrap.Modal(document.getElementById('modalAgregarAsignacion'));
                modal.show();
            });
        </script>
    @endif
@endsection
