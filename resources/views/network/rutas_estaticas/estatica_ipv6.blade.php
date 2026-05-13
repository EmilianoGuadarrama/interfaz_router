@extends('layouts.dashboard')

@section('title', 'Rutas - IPv6 estáticas')
@section('page-title', 'Rutas')

@section('content')

    {{-- 🔥 FORM GLOBAL PARA ELIMINAR --}}
    <form id="deleteForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="route_key" id="deleteRouteKey">
    </form>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <p class="mb-0" style="color: #9ba1a6; max-width: 70%;">
            Las rutas IPv6 especifican sobre qué interfaz y puerta de enlace se puede llegar a una red IPv6 específica.
        </p>

        <div id="router-status" class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill"
            style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); font-size: 0.85rem; transition: all 0.3s ease;">
            <div class="spinner-grow spinner-grow-sm text-secondary" role="status" style="width: 12px; height: 12px;"></div>
            <span class="text-secondary fw-bold">Verificando conexión...</span>
        </div>
    </div>

    <div class="panel-card">
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('red.routes.static.ipv4') }}">Rutas IPv4 estáticas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('red.routes.static.ipv6') }}">Rutas IPv6 estáticas</a>
            </li>
        </ul>

        @includeIf('network.partials.result')

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fs-5 fw-bold" style="color: #ffffff;">Rutas IPv6 estáticas</h4>
            <button type="button" class="btn text-white fw-bold d-flex align-items-center gap-2" data-bs-toggle="modal"
                data-bs-target="#addRouteIpv6Modal" style="background: #36b9cc; border-radius: 10px; padding: 8px 16px;">
                <i class="bi bi-plus-lg"></i> Añadir
            </button>
        </div>

        <div class="table-responsive mb-4">
            <table class="table-dark-custom align-middle">
                <thead>
                    <tr>
                        <th>Interfaz</th>
                        <th>
                            Destino<br>
                            <small style="color: #9ba1a6; font-weight: normal;">Red (CIDR) IPv6</small>
                        </th>
                        <th>Puerta de enlace IPv6</th>
                        <th>
                            Opciones<br>
                            <small style="color: #9ba1a6; font-weight: normal;">Métrica / MTU / Enlace</small>
                        </th>
                        <th>
                            Avanzado<br>
                            <small style="color: #9ba1a6; font-weight: normal;">Tipo / Tabla / Origen</small>
                        </th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($routes as $route)
                        <tr>
                            <td>
                                <span class="soft-badge">
                                    {{ strtoupper($route['interface'] ?? '-') }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-white">{{ $route['target'] ?? '-' }}</div>
                            </td>
                            <td class="text-white">{{ $route['gateway'] ?? '-' }}</td>
                            <td>
                                <div style="font-size: 0.85rem;">
                                    <span style="color: #9ba1a6;">Métrica:</span> <span class="fw-semibold text-white">{{ $route['metric'] ?? '0' }}</span><br>
                                    <span style="color: #9ba1a6;">MTU:</span> <span class="fw-semibold text-white">{{ $route['mtu'] ?? 'Auto' }}</span><br>
                                    <span style="color: #9ba1a6;">Enlace:</span> 
                                    @if(isset($route['onlink']) && $route['onlink'] == '1')
                                        <span class="badge bg-success rounded-pill" style="font-size: 0.7rem;">Sí</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill" style="font-size: 0.7rem;">No</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;">
                                    <span style="color: #9ba1a6;">Tipo:</span> <span class="fw-semibold text-white">{{ $route['type'] ?? 'unicast' }}</span><br>
                                    <span style="color: #9ba1a6;">Tabla:</span> <span class="fw-semibold text-white">{{ $route['table'] ?? 'main' }}</span><br>
                                    <span style="color: #9ba1a6;">Origen:</span> <span class="fw-semibold text-white">{{ $route['source'] ?? 'Auto' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- 🔥 BOTÓN EDITAR --}}
                                    <button type="button" class="btn btn-sm text-white edit-btn" 
                                        data-route="{{ json_encode($route) }}"
                                        data-bs-toggle="modal" data-bs-target="#editRouteIpv6Modal"
                                        style="background: #4a86f7; border-radius: 8px;" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    {{-- 🔥 BOTÓN ELIMINAR --}}
                                    <button type="button" class="btn btn-sm text-white delete-btn" data-key="{{ $route['key'] }}"
                                        style="background: #e74a3b; border-radius: 8px;" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4" style="color: #9ba1a6; font-style: italic;">
                                Esta sección aún no contiene rutas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 🔥 MODAL AÑADIR IPv6 --}}
    <div class="modal fade" id="addRouteIpv6Modal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background: var(--card-bg); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px;">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.1); padding: 20px 24px;">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-signpost-split text-primary me-2"></i> Añadir nueva ruta IPv6
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('red.routes.static.ipv6.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <h6 class="fw-bold mb-3" style="color: #36b9cc;">Configuración General</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label" style="color: #9ba1a6;">Interfaz</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="interface" required placeholder="ej. lan">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="color: #9ba1a6;">Objetivo (CIDR IPv6)</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="target" required placeholder="ej. 2001:db8::/32">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="color: #9ba1a6;">Puerta de enlace IPv6</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="gateway" placeholder="ej. fe80::1">
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 mt-2" style="color: #36b9cc;">Configuración Avanzada</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label" style="color: #9ba1a6;">Métrica</label>
                                <input type="number" class="form-control bg-dark text-white border-secondary" name="metric" placeholder="256">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="color: #9ba1a6;">MTU</label>
                                <input type="number" class="form-control bg-dark text-white border-secondary" name="mtu" placeholder="1500">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="color: #9ba1a6;">Tipo de ruta</label>
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
                                <label class="form-label" style="color: #9ba1a6;">Tabla de ruta</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="table" placeholder="ej. main">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #9ba1a6;">Dirección de origen</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="source" placeholder="Automático">
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" value="1" name="onlink" id="onlink_ipv6">
                            <label class="form-check-label" for="onlink_ipv6" style="color: #9ba1a6;">Ruta en enlace</label>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.1); padding: 16px 24px;">
                        <button type="button" class="btn text-white fw-bold" data-bs-dismiss="modal" style="background: rgba(255,255,255,0.1); border-radius: 10px;">DESCARTAR</button>
                        <button type="submit" class="btn text-white fw-bold" style="background: #36b9cc; border-radius: 10px;">GUARDAR Y APLICAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 🔥 MODAL EDITAR IPv6 --}}
    <div class="modal fade" id="editRouteIpv6Modal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background: var(--card-bg); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px;">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.1); padding: 20px 24px;">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-pencil-square text-primary me-2"></i> Editar ruta IPv6
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('red.routes.static.ipv6.update') }}" method="POST" id="editRouteIpv6Form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="route_key" id="edit_route_key_ipv6">

                    <div class="modal-body p-4">
                        <h6 class="fw-bold mb-3" style="color: #36b9cc;">Configuración General</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label" style="color: #9ba1a6;">Interfaz</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="interface" id="edit_interface_ipv6" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="color: #9ba1a6;">Objetivo (CIDR)</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="target" id="edit_target_ipv6" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="color: #9ba1a6;">Puerta de enlace IPv6</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="gateway" id="edit_gateway_ipv6">
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 mt-2" style="color: #36b9cc;">Configuración Avanzada</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label" style="color: #9ba1a6;">Métrica</label>
                                <input type="number" class="form-control bg-dark text-white border-secondary" name="metric" id="edit_metric_ipv6">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="color: #9ba1a6;">MTU</label>
                                <input type="number" class="form-control bg-dark text-white border-secondary" name="mtu" id="edit_mtu_ipv6">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="color: #9ba1a6;">Tipo de ruta</label>
                                <select class="form-select bg-dark text-white border-secondary" name="type" id="edit_type_ipv6">
                                    <option value="unicast">unicast</option>
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
                                <label class="form-label" style="color: #9ba1a6;">Tabla de ruta</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="table" id="edit_table_ipv6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="color: #9ba1a6;">Dirección de origen</label>
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="source" id="edit_source_ipv6">
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" value="1" name="onlink" id="edit_onlink_ipv6">
                            <label class="form-check-label" for="edit_onlink_ipv6" style="color: #9ba1a6;">Ruta en enlace</label>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.1); padding: 16px 24px;">
                        <button type="button" class="btn text-white fw-bold" data-bs-dismiss="modal" style="background: rgba(255,255,255,0.1); border-radius: 10px;">DESCARTAR</button>
                        <button type="submit" class="btn text-white fw-bold" style="background: #4a86f7; border-radius: 10px;">ACTUALIZAR CAMBIOS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // ELIMINAR
            const form = document.getElementById('deleteForm');
            const input = document.getElementById('deleteRouteKey');

            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    if (confirm('¿Seguro que deseas eliminar esta ruta IPv6?')) {
                        input.value = this.dataset.key;
                        form.action = "{{ route('red.routes.static.ipv6.destroy') }}";
                        form.submit();
                    }
                });
            });

            // EDITAR: Llenar modal auto
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const data = JSON.parse(this.dataset.route);
                    
                    document.getElementById('edit_route_key_ipv6').value = data.key || '';
                    document.getElementById('edit_interface_ipv6').value = data.interface || '';
                    document.getElementById('edit_target_ipv6').value = data.target || '';
                    document.getElementById('edit_gateway_ipv6').value = data.gateway || '';
                    document.getElementById('edit_metric_ipv6').value = data.metric || '';
                    document.getElementById('edit_mtu_ipv6').value = data.mtu || '';
                    
                    if(data.type) document.getElementById('edit_type_ipv6').value = data.type;
                    
                    document.getElementById('edit_table_ipv6').value = data.table || '';
                    document.getElementById('edit_source_ipv6').value = data.source || '';
                    document.getElementById('edit_onlink_ipv6').checked = (data.onlink === '1');
                });
            });

            // FETCH ESTADO CONEXIÓN
            setTimeout(() => {
                fetch("{{ route('red.estado.conexion') }}")
                    .then(r => r.json())
                    .then(data => {
                        const status = document.getElementById('router-status');
                        if (data.connected) {
                            status.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> <span class="text-success fw-bold">Router Conectado</span>';
                        } else {
                            status.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> <span class="text-danger fw-bold">Desconectado</span>';
                        }
                    });
            }, 200);

        });
    </script>
@endsection