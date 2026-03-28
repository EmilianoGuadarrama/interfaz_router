@extends('layouts.dashboard')

@section('title', 'Rutas - IPv4 estáticas')

{{-- Modificamos esta sección para que incluya el badge de estado --}}
@section('page-title')
    Rutas 
    <span id="router-status" style="font-size: 0.85rem; font-weight: 500; margin-left: 10px; vertical-align: middle;">
        {{-- Aquí se inyectará el estado vía JS --}}
    </span>
@endsection

@section('content')
    <p class="mb-4" style="color: var(--text-soft);">Las rutas especifican sobre qué interfaz y puerta de enlace se puede llegar a un cierto dispositivo o red.</p>

    <div class="panel-card">
        <!-- Pestañas -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('network.routes.static.ipv4') }}">Rutas IPv4 estáticas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('network.routes.static.ipv6') }}">Rutas IPv6 estáticas</a>
            </li>
        </ul>

        <h4 class="mb-4 fs-5 fw-bold" style="color: #fff;">Rutas IPv4 estáticas</h4>

        <!-- Tabla dinámica para mostrar rutas leídas del router -->
        <div class="table-responsive mb-4">
            <table class="table-dark-custom">
                <thead>
                    <tr>
                        <th>Interfaz</th>
                        <th>
                            Objetivo<br>
                            <small style="color: var(--text-muted); font-weight: normal;">Dirección IP o red</small>
                        </th>
                        <th>
                            Máscara de red IPv4<br>
                            <small style="color: var(--text-muted); font-weight: normal;">Si el destino es una red</small>
                        </th>
                        <th>Puerta de enlace IPv4</th>
                        <th>Métrica</th>
                        <th>Ruta en enlace (MTU)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($routes as $route)
                        <tr>
                            <td>
                                <span class="soft-badge">{{ strtoupper($route['interface'] ?? '-') }}</span>
                            </td>
                            <td>{{ $route['target'] ?? '-' }}</td>
                            <td>{{ $route['netmask'] ?? '-' }}</td>
                            <td>{{ $route['gateway'] ?? '-' }}</td>
                            <td>{{ $route['metric'] ?? '-' }}</td>
                            <td>{{ $route['mtu'] ?? '-' }}</td>
                            <td>
                                <form action="{{ route('network.routes.static.ipv4.destroy') }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="route_key" value="{{ $route['key'] }}">
                                    <button type="submit" class="btn btn-sm text-white" style="background: #e74a3b; border-radius: 8px;" onclick="return confirm('¿Seguro que deseas eliminar esta ruta?');" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4" style="color: var(--text-muted); font-style: italic;">
                                Esta sección aún no contiene valores
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Formulario para agregar una nueva ruta -->
        <div class="mt-5 pt-4" style="border-top: 1px solid var(--border-soft);">
            <h5 class="mb-4 fw-bold" style="color: #fff;"><i class="bi bi-plus-circle me-2"></i>Añadir nueva ruta</h5>
            <form action="{{ route('network.routes.static.ipv4.store') }}" method="POST">
                @csrf
                
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label style="color: var(--text-soft);" class="form-label">Interfaz</label>
                        <input type="text" class="form-control" name="interface" required placeholder="ej. lan">
                    </div>
                    <div class="col-md-3">
                        <label style="color: var(--text-soft);" class="form-label">Objetivo</label>
                        <input type="text" class="form-control" name="target" required placeholder="ej. 192.168.2.0">
                    </div>
                    <div class="col-md-2">
                        <label style="color: var(--text-soft);" class="form-label">Máscara IPv4</label>
                        <input type="text" class="form-control" name="netmask" placeholder="ej. 255.255.255.0">
                    </div>
                    <div class="col-md-3">
                        <label style="color: var(--text-soft);" class="form-label">Puerta de enlace</label>
                        <input type="text" class="form-control" name="gateway" placeholder="ej. 192.168.1.1">
                    </div>
                    <div class="col-md-1">
                        <label style="color: var(--text-soft);" class="form-label">Métrica</label>
                        <input type="number" class="form-control" name="metric" placeholder="0">
                    </div>
                    <div class="col-md-1">
                        <label style="color: var(--text-soft);" class="form-label">MTU</label>
                        <input type="number" class="form-control" name="mtu" placeholder="1500">
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button class="btn text-white" type="submit" style="background: #36b9cc; border:none; border-radius: 14px; padding: 10px 18px; font-weight: 700; font-size: .92rem;">
                        GUARDAR Y APLICAR <i class="bi bi-caret-down-fill ms-1"></i>
                    </button>
                    <button class="btn text-white" type="reset" style="background: #e74a3b; border:none; border-radius: 14px; padding: 10px 18px; font-weight: 700; font-size: .92rem;">
                        LIMPIAR FORMULARIO
                    </button>
                </div>
            </form>
        </div>
    </div>

    @include('network.partials.result')

    {{-- SCRIPTS DE FUNCIONAMIENTO --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Lógica del Botón Refrescar del Topbar
            const refreshBtn = document.querySelector('.btn-main'); // Detecta tu botón azul del topbar
            if (refreshBtn) {
                refreshBtn.addEventListener('click', () => window.location.reload());
            }

            // 2. Lógica del Estado de Conexión
            const statusTarget = document.getElementById('router-status');
            if (statusTarget) {
                statusTarget.innerHTML = '<span style="color: var(--text-muted);"><i class="bi bi-arrow-repeat spin"></i> Comprobando...</span>';

                fetch("{{ route('network.estado.conexion') }}")
                    .then(response => response.json())
                    .then(data => {
                        if (data.connected) {
                            statusTarget.innerHTML = '<span style="color: #2ecc71;"><i class="bi bi-check-circle-fill"></i> CONECTADO</span>';
                        } else {
                            statusTarget.innerHTML = '<span style="color: #e74a3b;"><i class="bi bi-x-circle-fill"></i> DESCONECTADO</span>';
                        }
                    })
                    .catch(() => {
                        statusTarget.innerHTML = '<span style="color: #f1c40f;"><i class="bi bi-exclamation-triangle-fill"></i> ERROR DE COMUNICACIÓN</span>';
                    });
            }
        });
    </script>

    {{-- Animación para el icono de carga --}}
    <style>
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .spin { display: inline-block; animation: spin 1s linear infinite; }
    </style>
@endsection