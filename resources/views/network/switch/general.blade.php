@extends('layouts.dashboard')

@section('title', 'Conmutador - Configuración general')

@section('content')
    <div class="container-fluid">
        <h2 class="page-title mb-3">Conmutador</h2>

        @if(session('success'))
            <div class="alert alert-success rounded-4 mb-3">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger rounded-4 mb-3">
                {{ session('error') }}
            </div>
        @endif

        <div class="panel-card">
            <ul class="nav nav-tabs mb-4 border-0">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('red.conmutador.general') }}">Configuración general</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('red.conmutador.vlans') }}">VLANs</a>
                </li>
            </ul>

            <p class="text-light mb-4">
                Los puertos de red de este dispositivo se pueden combinar en varias VLANs en las que los ordenadores se pueden comunicar directamente entre ellos.
            </p>

            <form action="{{ route('red.conmutador.general.update') }}" method="POST">
                @csrf

                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="form-check form-switch d-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="enable_vlan" id="enable_vlan" value="1" style="width: 2.5em; height: 1.25em; cursor: pointer;"
                                {{ !empty($config['enable_vlan']) ? 'checked' : '' }}>
                            <label class="form-check-label text-light fw-semibold m-0" for="enable_vlan" style="cursor: pointer;">
                                Activar funcionalidad VLAN
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" name="submit_action" value="apply" class="btn btn-main">Guardar y aplicar</button>
                    <button type="submit" name="submit_action" value="save" class="btn btn-outline-light">Guardar</button>
                </div>
            </form>
        </div>

        @include('network.partials.result')
    </div>

    <style>
        /* form-switch active state */
        .form-switch .form-check-input:checked {
            background-color: #5b8cff;
            border-color: #5b8cff;
        }
    </style>
@endsection
