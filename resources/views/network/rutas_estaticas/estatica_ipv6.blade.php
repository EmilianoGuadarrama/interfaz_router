@extends('layouts.dashboard')

@section('title', 'Rutas - IPv6 estáticas')
@section('page-title', 'Rutas')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <p class="mb-0" style="color: var(--text-soft); max-width: 70%;">Las rutas IPv6 especifican sobre qué interfaz y puerta de enlace se puede llegar a una red IPv6 específica.</p>
        
        <!-- Indicador de estado de conexión -->
        <div id="router-status" class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill" style="background: rgba(255,255,255,0.05); border: 1px solid var(--border-soft); font-size: 0.85rem; transition: all 0.3s ease;">
            <div class="spinner-grow spinner-grow-sm text-secondary" role="status" style="width: 12px; height: 12px;"></div>
            <span class="text-secondary fw-bold">Verificando conexión...</span>
        </div>
    </div>

    <div class="panel-card">
        <!-- Pestañas -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('network.routes.static.ipv4') }}">Rutas IPv4 estáticas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('network.routes.static.ipv6') }}">Rutas IPv6 estáticas</a>
            </li>
        </ul>

        <!-- Componente de resultados -->
        @include('network.partials.result')

        <!-- Título y botón para abrir Modal -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fs-5 fw-bold" style="color: #fff;">Rutas IPv6 estáticas</h4>
            <button type="button" class="btn text-white fw-bold d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addRouteIpv6Modal" style="background: #36b9cc; border-radius: 10px; padding: 8px 16px;">
                <i class="bi bi-plus-lg"></i> Añadir
            </button>
        </div>

        <!-- Tabla dinámica IPv6 -->
        <div class="table-responsive mb-4">
            <table class="table-dark-custom">
                <thead>
                    <tr>
                        <th>Interfaz</th>
                        <th>
                            Objetivo<br>
                            <small style="color: var(--text-muted); font-weight: normal;">Dirección o red (CIDR) IPv6</small>
                        </th>
                        <th>Puerta de enlace IPv6</th>
                        <th>Métrica</th>
                        <th>Ruta en enlace (MTU)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($routes as $route)
                        <tr>
                            <td><span class="soft-badge">{{ strtoupper($route['interface'] ?? '-') }}</span></td>
                            <td>{{ $route['target'] ?? '-' }}</td>
                            <td>{{ $route['gateway'] ?? '-' }}</td>
                            <td>{{ $route['metric'] ?? '-' }}</td>
                            <td>{{ $route['mtu'] ?? '-' }}</td>
                            <td>
                                <!-- Formulario para eliminar ruta IPv6 -->
                                <form action="{{ route('network.routes.static.ipv6.destroy') }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="route_key" value="{{ $route['key'] }}">
                                    <button type="submit" class="btn btn-sm text-white" style="background: #e74a3b; border-radius: 8px;" onclick="return confirm('¿Seguro que deseas eliminar esta ruta IPv6?');" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4" style="color: var(--text-muted); font-style: italic;">
                                Esta sección aún no contiene valores
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL AÑADIR RUTA IPv6 -->
    <div class="modal fade" id="addRouteIpv6Modal" tabindex="-1" aria-labelledby="addRouteIpv6ModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background: var(--card-bg); border: 1px solid var(--border-soft); border-radius: 16px;">
                <div class="modal-header" style="border-bottom: 1px solid var(--border-soft); padding: 20px 24px;">
                    <h5 class="modal-title fw-bold text-white" id="addRouteIpv6ModalLabel">
                        <i class="bi bi-signpost-split text-primary me-2"></i>Añadir nueva ruta IPv6
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('network.routes.static.ipv6.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        
                        <!-- SECCIÓN: CONFIGURACIÓN GENERAL -->
                        <h6 class="fw-bold mb-3" style="color: #36b9cc;">Configuración General</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label" style="color: var(--text-soft);">Interfaz</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="interface" required placeholder="ej. lan">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="color: var(--text-soft);">Objetivo (CIDR IPv6)</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="target" required placeholder="ej. 2001:db8::/32">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="color: var(--text-soft);">Puerta de enlace IPv6</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="gateway" placeholder="ej. fe80::1">
                            </div>
                        </div>

                        <!-- SECCIÓN: CONFIGURACIÓN AVANZADA -->
                        <h6 class="fw-bold mb-3 mt-2" style="color: #36b9cc;">Configuración Avanzada</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label" style="color: var(--text-soft);">Métrica</label>
                                <input type="number" class="form-control bg-dark text-white border-secondary" name="metric" placeholder="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="color: var(--text-soft);">MTU</label>
                                <input type="number" class="form-control bg-dark text-white border-secondary" name="mtu" placeholder="1500">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="color: var(--text-soft);">Tipo de ruta</label>
                                <select class="form-select bg-dark text-white border-secondary" name="type">
                                    <option value="unicast" selected>unicast</option>
                                    <option value="local">local</option>
                                    <option value="broadcast">broadcast</option>
                                    <option value="multicast">multicast</option>
                                    <option value="unreachable">unreachable</option>
                                    <option value="prohibit">prohibit</option>
                                    <option value="blackhole">blackhole</option>
                                    <option value="anycast">anycast</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: var(--text-soft);">Tabla de ruta</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="table" placeholder="ej. main">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: var(--text-soft);">Dirección de origen</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="source" placeholder="Automático">
                            </div>
                        </div>
                        
                        <div class="form-check mt-3">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" value="1" id="onlink_ipv6" name="onlink">
                            <label class="form-check-label" for="onlink_ipv6" style="color: var(--text-soft);">
                                Ruta en enlace
                            </label>
                        </div>
                    </div>
                    
                    <div class="modal-footer" style="border-top: 1px solid var(--border-soft); padding: 16px 24px;">
                        <button type="button" class="btn text-white fw-bold" data-bs-dismiss="modal" style="background: rgba(255,255,255,0.1); border-radius: 10px; padding: 8px 20px;">
                            DESCARTAR
                        </button>
                        <button type="submit" class="btn text-white fw-bold" style="background: #36b9cc; border-radius: 10px; padding: 8px 20px;">
                            GUARDAR Y APLICAR
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script de Estado de Conexión -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const statusContainer = document.getElementById('router-status');

            fetch("{{ route('network.estado.conexion') }}")
                .then(response => response.json())
                .then(data => {
                    if(data.connected) {
                        statusContainer.style.background = 'rgba(40, 167, 69, 0.15)';
                        statusContainer.style.borderColor = 'rgba(40, 167, 69, 0.3)';
                        statusContainer.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> <span class="text-success fw-bold">Router Conectado</span>';
                    } else {
                        statusContainer.style.background = 'rgba(220, 53, 69, 0.15)';
                        statusContainer.style.borderColor = 'rgba(220, 53, 69, 0.3)';
                        statusContainer.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> <span class="text-danger fw-bold">Desconectado</span>';
                    }
                })
                .catch(error => {
                    statusContainer.style.background = 'rgba(220, 53, 69, 0.15)';
                    statusContainer.style.borderColor = 'rgba(220, 53, 69, 0.3)';
                    statusContainer.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> <span class="text-danger fw-bold">Sin conexión</span>';
                });
        });
    </script>
@endsection