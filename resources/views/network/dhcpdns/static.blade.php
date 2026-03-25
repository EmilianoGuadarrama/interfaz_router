@extends('layouts.dashboard')

@section('title', 'DHCP y DNS - Asignaciones estáticas')

@section('content')
    <div class="container-fluid">
        <h2 class="page-title mb-3">DHCP y DNS</h2>

        <div class="panel-card">
            <ul class="nav nav-tabs mb-4 border-0">
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.general') }}">Configuración general</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.resolv') }}">Archivos Resolv y Hosts</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.tftp') }}">Configuración TFTP</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.advanced') }}">Configuración avanzada</a></li>
                <li class="nav-item"><a class="nav-link active" href="{{ route('network.dhcpdns.static') }}">Asignaciones estáticas</a></li>
            </ul>

            <p class="text-light">
                Las asignaciones estáticas se usan para asignar direcciones IP fijas y nombres identificativos a dispositivos o clientes DHCP.
            </p>

            <div class="table-responsive mb-4">
                <table class="table-dark-custom">
                    <thead>
                    <tr>
                        <th>Nombre de host</th>
                        <th>Dirección MAC</th>
                        <th>Dirección IPv4</th>
                        <th>Tiempo de asignación</th>
                        <th>DUID</th>
                        <th>Sufijo IPv6</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="6" class="text-center">Esta sección aún no contiene valores</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <button class="btn btn-main mb-4">Añadir</button>

            <h4 class="text-light mb-3">Asignaciones DHCP activas</h4>

            <div class="table-responsive">
                <table class="table-dark-custom">
                    <thead>
                    <tr>
                        <th>Nombre de host</th>
                        <th>Dirección IPv4</th>
                        <th>Dirección MAC</th>
                        <th>Tiempo de asignación restante</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Susu</td>
                        <td>192.168.10.180</td>
                        <td>50:EB:F6:D1:96:1E</td>
                        <td>11h 56m 51s</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-main">Guardar y aplicar</button>
                <button class="btn btn-outline-light">Guardar</button>
                <button class="btn btn-danger">Restablecer</button>
            </div>
        </div>

        @include('network.partials.result')
    </div>
@endsection
