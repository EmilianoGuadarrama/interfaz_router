@extends('layouts.dashboard')

@section('title', 'DHCP y DNS - Resolv y Hosts')

@section('content')
    <div class="container-fluid">
        <h2 class="page-title mb-3">DHCP y DNS</h2>

        <div class="panel-card">
            <ul class="nav nav-tabs mb-4 border-0">
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.general') }}">Configuración general</a></li>
                <li class="nav-item"><a class="nav-link active" href="{{ route('network.dhcpdns.resolv') }}">Archivos Resolv y Hosts</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.tftp') }}">Configuración TFTP</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.advanced') }}">Configuración avanzada</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.static') }}">Asignaciones estáticas</a></li>
            </ul>

            <form action="{{ route('network.dhcpdns.resolv.update') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="readethers" checked>
                            <label class="form-check-label text-light">Usar /etc/ethers</label>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label text-light">Archivo de asignación</label>
                        <input type="text" class="form-control" name="leasefile" value="/tmp/dhcp.leases">
                    </div>

                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="noresolv">
                            <label class="form-check-label text-light">Ignorar archivo resolve</label>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label text-light">Archivo de resolución</label>
                        <input type="text" class="form-control" name="resolvfile" value="/tmp/resolv.conf.auto">
                    </div>

                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="nohosts">
                            <label class="form-check-label text-light">Ignorar /etc/hosts</label>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label text-light">Archivos de hosts adicionales</label>
                        <input type="text" class="form-control" name="addnhosts">
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
