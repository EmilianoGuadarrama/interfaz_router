@extends('layouts.dashboard')
@section('page-title', 'Nombres de host')
@section('content')

<div class="container-fluid">

    @if(session('result_title'))
        <div class="alert {{ session('result_success') ? 'alert-success' : 'alert-danger' }} d-flex align-items-center gap-2 mb-4">
            <i class="bi {{ session('result_success') ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
            <strong>{{ session('result_title') }}</strong>
        </div>
    @endif

    {{-- Tabla de entradas --}}
    <div class="panel-card mb-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h5 class="m-0" style="font-weight:700; color:#e2eaff;">
                <i class="bi bi-list-ul me-2" style="color:#4a86f7;"></i>Entradas de host
            </h5>
            <button class="btn btn-main" type="button" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                <i class="bi bi-plus-lg me-1"></i> Añadir
            </button>
        </div>

        @if(count($entries) > 0)
            <table class="table-dark-custom w-100">
                <thead>
                    <tr>
                        <th>Nombre de host</th>
                        <th>Dirección IP</th>
                        <th style="width:100px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                        <tr>
                            <td>
                                <i class="bi bi-hdd-network me-2" style="color:#4a86f7;"></i>
                                {{ $entry['name'] }}
                            </td>
                            <td>
                                <span class="soft-badge">{{ $entry['ip'] }}</span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('network.hostentries.destroy') }}"
                                      onsubmit="return confirm('¿Eliminar esta entrada?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="index" value="{{ $entry['index'] }}">
                                    <button type="submit" class="btn btn-sm"
                                        style="background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.25); border-radius:10px;">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-center py-5" style="color: var(--text-muted);">
                <i class="bi bi-inbox" style="font-size:2.5rem; opacity:.4;"></i>
                <p class="mt-3 mb-0">No hay entradas de host configuradas.</p>
            </div>
        @endif
    </div>

</div>

{{-- Modal Agregar --}}
<div class="modal fade" id="modalAgregar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--card-bg); border: 1px solid var(--border-soft); border-radius: var(--radius-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-soft);">
                <h5 class="modal-title" style="font-weight:700; color:#e2eaff;">
                    <i class="bi bi-plus-circle me-2" style="color:#4a86f7;"></i>Nueva entrada de host
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('network.hostentries.store') }}">
                @csrf
                <div class="modal-body" style="padding: 24px;">

                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-soft); font-weight:600;">Nombre de host</label>
                        <input type="text"
                               name="name"
                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               placeholder="ej: laptopMaite"
                               value="{{ old('name') }}"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small style="color: var(--text-muted);">Solo letras, números y guiones.</small>
                    </div>

                    <div class="mb-1">
                        <label class="form-label" style="color: var(--text-soft); font-weight:600;">Dirección IP</label>
                        <input type="text"
                               name="ip"
                               class="form-control {{ $errors->has('ip') ? 'is-invalid' : '' }}"
                               placeholder="ej: 192.168.10.206"
                               value="{{ old('ip') }}"
                               required>
                        @error('ip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border-soft);">
                    <button type="button" class="btn btn-sm" data-bs-dismiss="modal"
                        style="background:rgba(255,255,255,0.06); color:var(--text-soft); border-radius:12px; padding:8px 18px;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-main">
                        <i class="bi bi-check-lg me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Abrir modal si hay errores de validación --}}
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('modalAgregar')).show();
    });
</script>
@endif

@endsection