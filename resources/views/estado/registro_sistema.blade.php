@extends('layouts.dashboard')

@section('title', 'Estado - Registro del sistema')
@section('page-title', 'Estado del Sistema')

@section('content')

<div class="panel-card">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h3 class="page-title mb-0">
            Registro del sistema
        </h3>

        <button class="btn btn-main">
            <i class="bi bi-arrow-clockwise"></i>
            Actualizar
        </button>

    </div>

    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="stats-card p-3">
                <h6>Eventos registrados</h6>
                <h3 class="mt-3">1,284</h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stats-card p-3">
                <h6>Advertencias</h6>
                <h3 class="mt-3 text-warning">18</h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stats-card p-3">
                <h6>Errores críticos</h6>
                <h3 class="mt-3 text-danger">2</h3>
            </div>
        </div>

    </div>

    <div class="panel-card p-3">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h5 class="mb-0">
                Eventos recientes
            </h5>

            <span class="soft-badge">
                Últimos registros
            </span>

        </div>

        <div style="
            background: rgba(0,0,0,0.25);
            border-radius: 18px;
            padding: 20px;
            max-height: 500px;
            overflow-y: auto;
            font-family: monospace;
            font-size: .92rem;
            color: #d7e3ff;
        ">

[12:00:02] Sistema iniciado correctamente<br><br>

[12:01:15] Servicio DNS iniciado<br><br>

[12:03:44] Interfaz WAN conectada<br><br>

[12:05:11] Cliente DHCP asignado: 192.168.1.20<br><br>

[12:08:22] Configuración guardada por administrador<br><br>

[12:12:08] Firewall actualizado correctamente<br><br>

[12:15:51] Reinicio parcial del sistema<br><br>

[12:18:43] Verificación de conectividad completada<br><br>

[12:22:14] Servicio SSH habilitado<br><br>

[12:24:50] Actualización automática completada<br><br>

        </div>

    </div>

</div>

@endsection