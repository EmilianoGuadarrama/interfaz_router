@extends('layouts.dashboard')

@section('page-title', 'Arranque')

@section('content')
    <div class="container-fluid">

        @if(session('result_title'))
            <div class="alert {{ session('result_success') ? 'alert-success' : 'alert-danger' }} d-flex align-items-center mb-4">
                <i class="bi {{ session('result_success') ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-2"></i>
                <strong>{{ session('result_title') }}</strong>
            </div>
        @endif

        <div class="panel-card">
            <h2 class="page-title mb-4">Arranque</h2>

            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a href="{{ route('startup', ['tab' => 'scripts']) }}"
                       class="nav-link {{ $activeTab === 'scripts' ? 'active' : '' }}">
                        Scripts de inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('startup', ['tab' => 'local']) }}"
                       class="nav-link {{ $activeTab === 'local' ? 'active' : '' }}">
                        Arranque local
                    </a>
                </li>
            </ul>

            @if($activeTab === 'scripts')
                <p class="mb-4" style="color: var(--text-soft); font-size: 1rem;">
                    Puede activar o desactivar los scripts de inicio instalados aquí. Los cambios se aplicarán después de que se reinicie el dispositivo.
                </p>

                <div class="table-responsive">
                    <table class="table-dark-custom">
                        <thead>
                        <tr>
                            <th style="width: 180px;">Prioridad de inicio</th>
                            <th>Nombre del script de inicio</th>
                            <th style="width: 220px;">Estado</th>
                            <th style="width: 460px;">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($scripts as $script)
                            <tr>
                                <td>{{ $script['priority'] }}</td>
                                <td>{{ $script['name'] }}</td>
                                <td>
                                    <span class="soft-badge">
                                        {{ $script['enabled'] ? 'Activado' : 'Desactivado' }}
                                    </span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <div class="d-flex flex-nowrap align-items-center gap-2">
                                        <form action="{{ route('startup.scripts.action', ['script' => $script['name'], 'action' => $script['enabled'] ? 'disable' : 'enable']) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $script['enabled'] ? 'btn-warning' : 'btn-success' }}">
                                                {{ $script['enabled'] ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('startup.scripts.action', ['script' => $script['name'], 'action' => 'start']) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-info text-white">
                                                Iniciar
                                            </button>
                                        </form>

                                        <form action="{{ route('startup.scripts.action', ['script' => $script['name'], 'action' => 'restart']) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                Reiniciar
                                            </button>
                                        </form>

                                        <form action="{{ route('startup.scripts.action', ['script' => $script['name'], 'action' => 'stop']) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Detener
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center" style="color: var(--text-soft); padding: 20px;">
                                    No se encontraron scripts de inicio.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            @if($activeTab === 'local')
                <p class="mb-4" style="color: var(--text-soft); font-size: 1rem;">
                    Contenido de <code>/etc/rc.local</code>. Coloca aquí tus comandos antes de <code>exit 0</code>.
                </p>

                <form action="{{ route('startup.update') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="content" class="form-label">Contenido</label>
                        <textarea
                            name="content"
                            id="content"
                            rows="18"
                            class="form-control @error('content') is-invalid @enderror"
                            style="font-family: monospace;"
                        >{{ old('content', $content) }}</textarea>

                        @error('content')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-main">
                            <i class="bi bi-save me-1"></i> Guardar
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
