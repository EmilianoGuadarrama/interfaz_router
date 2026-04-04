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

            <div class="grabado-section">
                <p class="grabado-desc">
                    Pulse "Generar archivo" para descargar un archivo con extensión .tar con los archivos de configuración actuales.
                </p>
                <div class="grabado-row">
                    <label class="grabado-label">Descargar copia de seguridad</label>
                    <form action="{{ route('grabado.backup') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-main btn-sm">GENERAR ARCHIVO</button>
                    </form>
                </div>
            </div>

            <hr class="grabado-divider">

            <div class="grabado-section">
                <p class="grabado-desc">
                    Para restaurar los archivos de configuración, debe subir primero una copia de seguridad.
                    Para reiniciar el firmware a sus configuraciones predeterminadas pulse "Realizar restablecimiento"
                    (sólo posible con imágenes squashfs).
                </p>
                <div class="grabado-row" style="margin-bottom:16px;">
                    <label class="grabado-label" style="line-height:1.3;">
                        Reiniciar a configuraciones<br>predeterminadas
                    </label>
                    <form action="{{ route('grabado.fabrica') }}" method="POST"
                          onsubmit="return confirm('¿Restablecer el router a configuración de fábrica? Se perderán todos los cambios.')">
                        @csrf
                        <button type="submit" class="btn btn-sm"
                                style="background:#dc3545;color:white;border:none;border-radius:10px;padding:8px 20px;font-weight:700;cursor:pointer;">
                            REALIZAR RESTABLECIMIENTO
                        </button>
                    </form>
                </div>
                <div class="grabado-row" style="align-items:flex-start;">
                    <label class="grabado-label" style="padding-top:8px;">Restaurar copia de seguridad</label>
                    <div>
                        <form action="{{ route('grabado.restaurar') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <label class="btn btn-main btn-sm" style="cursor:pointer;">
                                SUBIR ARCHIVO...
                                <input type="file" name="backup" accept=".tar,.tar.gz" style="display:none;"
                                       onchange="this.closest('form').submit()">
                            </label>
                        </form>
                        <p style="font-size:12px;color:var(--text-muted);margin-top:8px;">
                            Los archivos personalizados (certificados, scripts) pueden permanecer en el sistema.
                            Para evitar esto, primero realice un restablecimiento de fábrica.
                        </p>
                    </div>
                </div>
            </div>

            <hr class="grabado-divider">

            <div class="grabado-section">
                <p class="grabado-desc">
                    Haga clic en "Guardar mtdblock" para descargar el archivo mtdblock especificado.
                    <strong style="color:var(--text-soft);">(NOTA: ¡ESTA FUNCIÓN ES PARA PROFESIONALES!)</strong>
                </p>
                <form action="{{ route('grabado.mtdblock') }}" method="POST">
                    @csrf
                    <div class="grabado-row" style="margin-bottom:16px;">
                        <label class="grabado-label">Elegir mtdblock</label>
                        <select name="mtdblock" class="form-select dark-select" style="max-width:300px;">
                            @foreach($mtdblocks as $mtd)
                                <option value="{{ $mtd['device'] }}">{{ $mtd['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grabado-row">
                        <label class="grabado-label">Descargar mtdblock</label>
                        <button type="submit" class="btn btn-main btn-sm">GUARDAR MTDBLOCK</button>
                    </div>
                </form>
            </div>

            <hr class="grabado-divider">

            <div class="grabado-section">
                <p class="grabado-desc">
                    Cargue aquí una imagen compatible con sysupgrade para reemplazar el firmware en ejecución.
                </p>
                <div class="grabado-row">
                    <label class="grabado-label">Imagen</label>
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
        <div class="panel-card">
            <div class="grabado-section">
                <p class="grabado-desc">
                    Lista de patrones shell con los archivos y directorios que se deben incluir en un sysupgrade.
                    Los archivos modificados en /etc/config/ y ciertas otras configuraciones se guardarán automáticamente.
                </p>

                <div class="grabado-row" style="margin-bottom:20px;">
                    <label class="grabado-label">Mostrar lista de archivos a resguardar</label>
                    <button type="button" class="btn btn-main btn-sm" id="btnToggleLista" onclick="toggleLista(this)">
                        ABRIR LISTA...
                    </button>
                </div>

                <div id="listaArchivos" style="display:none;">
                    <form action="{{ route('grabado.guardarLista') }}" method="POST">
                        @csrf
                        <textarea name="lista_contenido" id="listaTexto" class="dark-select"
                                  style="width:100%; height:400px; font-family:monospace; font-size:13px; resize:vertical; padding:12px; border-radius:6px; border:1px solid rgba(255,255,255,.1);">{{ $listaArchivos ?? '' }}</textarea>
                        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                            <button type="button" class="btn btn-sm"
                                    style="background:rgba(255,255,255,.08); color:var(--text-main); border:1px solid var(--border-soft); border-radius:10px; padding:8px 18px; font-weight:600;"
                                    onclick="toggleLista(document.getElementById('btnToggleLista'))">
                                DESCARTAR
                            </button>
                            <button type="submit" class="btn btn-main btn-sm">GUARDAR</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.grabado-section  { padding: 24px; }
.grabado-desc     { font-size:13px; color:var(--text-muted); margin-bottom:18px; line-height:1.6; }
.grabado-divider  { border-color:var(--border-soft); margin:0; }
.grabado-row      { display:grid; grid-template-columns:280px 1fr; align-items:center; gap:16px; }
.grabado-label    { font-size:13.5px; font-weight:600; color:var(--text-soft); text-align:right; }
.dark-select { background: #1a2744 !important; color: var(--text-main) !important; border-color: rgba(255,255,255,.1) !important; }
.dark-select option { background: #1a2744; color: var(--text-main); }
</style>

@push('scripts')
<script>
function toggleLista(btn) {
    const lista = document.getElementById('listaArchivos');
    const boton = btn || document.getElementById('btnToggleLista');
    if (lista.style.display === 'none') {
        lista.style.display = 'block';
        boton.textContent   = 'CERRAR LISTA';
    } else {
        lista.style.display = 'none';
        boton.textContent   = 'ABRIR LISTA...';
    }
}
</script>
@endpush

@endsection