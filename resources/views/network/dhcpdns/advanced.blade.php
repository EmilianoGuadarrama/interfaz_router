@extends('layouts.dashboard')

@section('title', 'DHCP y DNS - Configuración avanzada')

@section('content')
    <div class="container-fluid">
        <h2 class="page-title mb-3">DHCP y DNS</h2>

        <div class="panel-card">
            <ul class="nav nav-tabs mb-4 border-0">
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.general') }}">Configuración general</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.resolv') }}">Archivos Resolv y Hosts</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.tftp') }}">Configuración TFTP</a></li>
                <li class="nav-item"><a class="nav-link active" href="{{ route('network.dhcpdns.advanced') }}">Configuración avanzada</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.static') }}">Asignaciones estáticas</a></li>
            </ul>

            <form action="{{ route('network.dhcpdns.advanced.update') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="quietdhcp"><label class="form-check-label text-light">Suprimir el registro</label></div></div>
                    <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="sequential_ip"><label class="form-check-label text-light">Asignar IPs secuencialmente</label></div></div>
                    <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="boguspriv" checked><label class="form-check-label text-light">Filtro privado</label></div></div>
                    <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="filterwin2k"><label class="form-check-label text-light">Filtro inútil</label></div></div>
                    <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="localise_queries" checked><label class="form-check-label text-light">Localizar consultas</label></div></div>
                    <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="expandhosts" checked><label class="form-check-label text-light">Expandir hosts</label></div></div>

                    <div class="col-md-6">
                        <label class="form-label text-light">Archivo de servidores adicionales</label>
                        <input type="text" class="form-control" name="serversfile">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-light">Ignorar dominio falso NX</label>
                        <input type="text" class="form-control" name="bogusnxdomain" value="67.215.65.132">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-light">Puerto del servidor DNS</label>
                        <input type="number" class="form-control" name="port" value="53">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-light">Puerto de consultas al DNS</label>
                        <input type="text" class="form-control" name="queryport" value="cualquiera">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-light">Máximo de asignaciones DHCP</label>
                        <input type="text" class="form-control" name="dhcpleasemax" value="ilimitado">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-light">Máx. tamaño del paquete EDNS0</label>
                        <input type="number" class="form-control" name="ednspacket_max" value="1280">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-light">Máx. consultas simultáneas</label>
                        <input type="number" class="form-control" name="dnsforwardmax" value="150">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-light">Tamaño de la caché DNS</label>
                        <input type="number" class="form-control" name="cachesize" value="150">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-main">Guardar y aplicar</button>
                    <button class="btn btn-outline-light" type="submit">Guardar</button>
                </div>
            </form>
        </div>

        @include('network.partials.result')
    </div>
@endsection
