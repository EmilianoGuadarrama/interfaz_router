@extends('layouts.dashboard')
@section('page-title', 'Operaciones de grabado')

@section('content')

@if(session('result_title'))
    <div class="alert {{ session('result_success') ? 'alert-success' : 'alert-danger' }} mb-4">
        {{ session('result_title') }}
    </div>
@endif

{{-- Tabs --}}
<ul class="nav nav-tabs mb-4" id="grabadoTabs">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabAcciones">Acciones</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabConfiguracion">Configuración</button>
    </li>
</ul>

<div class="tab-content">

    {{-- ══ TAB ACCIONES ══ --}}
    <div class="tab-pane fade show active" id="tabAcciones">
        <div class="panel-card">

            {{-- Descargar copia de seguridad --}}
            <div style="padding:24px;">
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
                    Pulse "Generar archivo" para descargar un archivo con extensión .tar con los archivos de configuración actuales.
                </p>
                <div style="display:grid; grid-template-columns:260px 1fr; align-items:center; gap:16px;">
                    <label style="font-size:13.5px; font-weight:600; color:var(--text-soft); text-align:right;">
                        Descargar copia de seguridad
                    </label>
                    <form action="{{ route('grabado.backup') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-main btn-sm">GENERAR ARCHIVO</button>
                    </form>
                </div>
            </div>

            <hr style="border-color:var(--border-soft); margin:0;">

            {{-- Restablecer a configuraciones predeterminadas --}}
            <div style="padding:24px;">
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
                    Para restaurar los archivos de configuración, debe subir primero una copia de seguridad.
                    Para reiniciar el firmware a sus configuraciones predeterminadas pulse "Realizar restablecimiento"
                    (sólo posible con imágenes squashfs).
                </p>
                <div style="display:grid; grid-template-columns:260px 1fr; align-items:center; gap:16px; margin-bottom:20px;">
                    <label style="font-size:13.5px; font-weight:600; color:var(--text-soft); text-align:right;">
                        Reiniciar a configuraciones predeterminadas
                    </label>
                    <form action="{{ route('grabado.fabrica') }}" method="POST"
                          onsubmit="return confirm('¿Restablecer el router a configuración de fábrica? Se perderán todos los cambios.')">
                        @csrf
                        <button type="submit" class="btn btn-sm"
                                style="background:#dc3545;color:white;border:none;border-radius:10px;padding:8px 18px;font-weight:700;cursor:pointer;">
                            REALIZAR RESTABLECIMIENTO
                        </button>
                    </form>
                </div>

                <div style="display:grid; grid-template-columns:260px 1fr; align-items:start; gap:16px;">
                    <label style="font-size:13.5px; font-weight:600; color:var(--text-soft); text-align:right; padding-top:8px;">
                        Restaurar copia de seguridad
                    </label>
                    <div>
                        <form action="{{ route('grabado.restaurar') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                                <label class="btn btn-main btn-sm" style="cursor:pointer;">
                                    SUBIR ARCHIVO...
                                    <input type="file" name="backup" accept=".tar,.tar.gz" style="display:none;"
                                           onchange="this.closest('form').submit()">
                                </label>
                            </div>
                        </form>
                        <p style="font-size:12px; color:var(--text-muted); margin-top:8px;">
                            Los archivos personalizados (certificados, scripts) pueden permanecer en el sistema.
                            Para evitar esto, primero realice un restablecimiento de fábrica.
                        </p>
                    </div>
                </div>
            </div>

            <hr style="border-color:var(--border-soft); margin:0;">

            {{-- Mtdblock --}}
            <div style="padding:24px;">
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
                    Haga clic en "Guardar mtdblock" para descargar el archivo mtdblock especificado.
                    <strong style="color:var(--text-soft);">(NOTA: ¡ESTA FUNCIÓN ES PARA PROFESIONALES!)</strong>
                </p>
                <form action="{{ route('grabado.mtdblock') }}" method="POST">
                    @csrf
                    <div style="display:grid; grid-template-columns:260px 1fr; align-items:center; gap:16px; margin-bottom:16px;">
                        <label style="font-size:13.5px; font-weight:600; color:var(--text-soft); text-align:right;">
                            Elegir mtdblock
                        </label>
                        <select name="mtdblock" class="form-select" style="max-width:300px;">
                            @foreach($mtdblocks as $mtd)
                                <option value="{{ $mtd['device'] }}">{{ $mtd['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns:260px 1fr; align-items:center; gap:16px;">
                        <label style="font-size:13.5px; font-weight:600; color:var(--text-soft); text-align:right;">
                            Descargar mtdblock
                        </label>
                        <button type="submit" class="btn btn-main btn-sm">GUARDAR MTDBLOCK</button>
                    </div>
                </form>
            </div>

            <hr style="border-color:var(--border-soft); margin:0;">

            {{-- Grabar imagen --}}
            <div style="padding:24px;">
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
                    Cargue aquí una imagen compatible con sysupgrade para reemplazar el firmware en ejecución.
                </p>
                <div style="display:grid; grid-template-columns:260px 1fr; align-items:center; gap:16px;">
                    <label style="font-size:13.5px; font-weight:600; color:var(--text-soft); text-align:right;">
                        Imagen
                    </label>
                    <form action="{{ route('grabado.imagen') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="btn btn-main btn-sm" style="cursor:pointer;">
                            GRABAR IMAGEN...
                            <input type="file" name="imagen" accept=".bin,.img" style="display:none;"
                                   onchange="this.closest('form').submit()">
                        </label>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ TAB CONFIGURACIÓN ══ --}}
    <div class="tab-pane fade" id="tabConfiguracion">
        <div class="panel-card" style="padding:24px;">
            <p style="color:var(--text-muted); font-size:13.5px;">
                No hay opciones de configuración disponibles para esta sección.
            </p>
        </div>
    </div>

</div>

@endsection