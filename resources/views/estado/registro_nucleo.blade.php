@extends('layouts.dashboard')

@section('title', 'Estado - Registro del núcleo')
@section('page-title', 'Estado del Sistema')

@section('content')

<div class="panel-card">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h3 class="page-title mb-0">
            Registro del núcleo
        </h3>

        <button class="btn btn-main">
            <i class="bi bi-arrow-clockwise"></i>
            Actualizar
        </button>

    </div>

    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6>Procesos kernel</h6>
                <h3 class="mt-3">86</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6>Módulos cargados</h6>
                <h3 class="mt-3">41</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6>Advertencias</h6>
                <h3 class="mt-3 text-warning">5</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6>Errores kernel</h6>
                <h3 class="mt-3 text-danger">0</h3>
            </div>
        </div>

    </div>

    <div class="panel-card p-3">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h5 class="mb-0">
                Kernel log
            </h5>

            <span class="soft-badge">
                dmesg output
            </span>

        </div>

        <div style="
            background: rgba(0,0,0,0.30);
            border-radius: 18px;
            padding: 20px;
            max-height: 520px;
            overflow-y: auto;
            font-family: monospace;
            font-size: .88rem;
            color: #d7e3ff;
            line-height: 1.7;
        ">

[0.000000] Linux version 5.15.0-router<br><br>

[0.215443] CPU: ARMv7 Processor initialized<br><br>

[0.812114] Memory: 512MB available<br><br>

[1.054337] NET: Registered protocol family 16<br><br>

[1.842221] Ethernet interface initialized<br><br>

[2.105551] IPv4 routing table loaded<br><br>

[2.441902] Wireless subsystem initialized<br><br>

[3.117662] DHCP client started<br><br>

[3.854110] Firewall service active<br><br>

[4.221431] USB subsystem initialized<br><br>

[4.778234] EXT4 filesystem mounted<br><br>

[5.104922] System ready<br><br>

        </div>

    </div>

</div>

@endsection