@extends('layouts.dashboard')

@section('title', 'Rutas - IPv6 estáticas')
@section('page-title', 'Rutas')

@section('content')
    <p class="mb-4" style="color: var(--text-soft);">Las rutas especifican sobre qué interfaz y puerta de enlace se puede llegar a un cierto dispositivo o red.</p>

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

        <h4 class="mb-4 fs-5 fw-bold" style="color: #fff;">Rutas IPv6 estáticas</h4>

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

        <!-- Formulario para agregar una nueva ruta IPv6 -->
        <div class="mt-5 pt-4" style="border-top: 1px solid var(--border-soft);">
            <h5 class="mb-3 fw-bold" style="color: #fff;"><i class="bi bi-plus-circle me-2"></i>Añadir nueva ruta IPv6</h5>
            <form action="{{ route('network.routes.static.ipv6.store') }}" method="POST">
                @csrf
                
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label style="color: var(--text-soft);" class="form-label">Interfaz</label>
                        <input type="text" class="form-control" name="interface" required placeholder="ej. lan">
                    </div>
                    <div class="col-md-4">
                        <label style="color: var(--text-soft);" class="form-label">Objetivo (CIDR IPv6)</label>
                        <input type="text" class="form-control" name="target" required placeholder="ej. 2001:db8::/32">
                    </div>
                    <div class="col-md-4">
                        <label style="color: var(--text-soft);" class="form-label">Puerta de enlace IPv6</label>
                        <input type="text" class="form-control" name="gateway" placeholder="ej. fe80::1">
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

                <div class="d-flex gap-2">
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
@endsection