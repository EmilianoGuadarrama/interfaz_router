@extends('layouts.dashboard')

@section('title', 'DHCP y DNS - TFTP')

@section('content')
    <div class="container-fluid">
        <h2 class="page-title mb-3">DHCP y DNS</h2>

        <div class="panel-card">
            <ul class="nav nav-tabs mb-4 border-0">
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.general') }}">Configuración general</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.resolv') }}">Archivos Resolv y Hosts</a></li>
                <li class="nav-item"><a class="nav-link active" href="{{ route('network.dhcpdns.tftp') }}">Configuración TFTP</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.advanced') }}">Configuración avanzada</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('network.dhcpdns.static') }}">Asignaciones estáticas</a></li>
            </ul>

            <form action="{{ route('network.dhcpdns.tftp.update') }}" method="POST">
                @csrf

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="enable_tftp">
                    <label class="form-check-label text-light">Activar servidor TFTP</label>
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
