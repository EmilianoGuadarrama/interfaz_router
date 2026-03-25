@extends('layouts.dashboard')

@section('title', 'Conmutador')

@section('content')
    <div class="container-fluid">
        <h2 class="page-title mb-3">Conmutador</h2>

        <div class="panel-card">
            <p class="text-light mb-4">
                Los puertos de red de este dispositivo se pueden combinar en varias VLANs en las que los ordenadores se pueden comunicar directamente entre ellos.
            </p>

            <form action="{{ route('network.switch.vlans.update') }}" method="POST">
                @csrf

                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label class="form-label text-light fw-semibold">Activar funcionalidad VLAN</label>
                    </div>
                    <div class="col-md-2">
                        <input type="checkbox" name="enable_vlan" value="1" class="form-check-input" checked>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-main">Guardar</button>
                </div>
            </form>
        </div>

        @include('network.partials.result')
    </div>
@endsection
