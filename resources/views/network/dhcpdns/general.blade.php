@extends('layouts.dashboard')

@section('title', 'DHCP y DNS - Configuración general')

@section('content')
    <div class="container-fluid">
        <h2 class="page-title mb-3">DHCP y DNS</h2>

        <div class="panel-card">
            <ul class="nav nav-tabs mb-4 border-0">
                <li class="nav-item"><a class="nav-link active" href="{{ route('network.dhcpdns.general') }}">Configuración general</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.resolv') }}">Archivos Resolv y Hosts</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.tftp') }}">Configuración TFTP</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.advanced') }}">Configuración avanzada</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.static') }}">Asignaciones estáticas</a></li>
            </ul>

            <form action="{{ route('network.dhcpdns.general.update') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label text-light">Servidor local</label>
                        <input type="text" name="local" class="form-control" value="/lan/">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-light">Dominio local</label>
                        <input type="text" name="domain" class="form-control" value="lan">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-light">Reenvíos de DNS</label>
                        <input type="text" name="server" class="form-control" placeholder="/example.org/10.1.2.3">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-light">Lista blanca de dominios</label>
                        <input type="text" name="rebind_domain" class="form-control" placeholder="ihost.netflix.com">
                    </div>

                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="domainneeded" checked>
                            <label class="form-check-label text-light">Requerir dominio</label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="authoritative" checked>
                            <label class="form-check-label text-light">Autorizar</label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="logqueries">
                            <label class="form-check-label text-light">Registrar consultas</label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="localservice" checked>
                            <label class="form-check-label text-light">Solo servicio local</label>
                        </div>
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
