@extends('layouts.dashboard')

@section('title', 'Estado - Rutas')
@section('page-title', 'Estado del Sistema')

@section('content')

<div class="panel-card">

    <h3 class="page-title">Rutas</h3>

    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="stats-card p-3 h-100">
                <h6>Total de rutas</h6>

                <div class="mt-3">
                    <h3>12</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stats-card p-3 h-100">
                <h6>Interfaces activas</h6>

                <div class="mt-3">
                    <h3>4</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stats-card p-3 h-100">
                <h6>Gateway principal</h6>

                <div class="mt-3">
                    <h5>192.168.1.1</h5>
                </div>
            </div>
        </div>

    </div>

    <div class="panel-card p-3">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Tabla de rutas</h5>

            <button class="btn btn-main">
                <i class="bi bi-arrow-repeat"></i>
                Actualizar
            </button>
        </div>

        <div class="table-responsive">

            <table class="table-dark-custom">

                <thead>
                    <tr>
                        <th>Destino</th>
                        <th>Gateway</th>
                        <th>Máscara</th>
                        <th>Interfaz</th>
                        <th>Métrica</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>0.0.0.0</td>
                        <td>192.168.1.1</td>
                        <td>0.0.0.0</td>
                        <td>WAN</td>
                        <td>10</td>
                        <td>
                            <span class="badge bg-success">
                                Activa
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>192.168.1.0</td>
                        <td>0.0.0.0</td>
                        <td>255.255.255.0</td>
                        <td>LAN</td>
                        <td>1</td>
                        <td>
                            <span class="badge bg-success">
                                Activa
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>10.0.0.0</td>
                        <td>192.168.1.254</td>
                        <td>255.0.0.0</td>
                        <td>VPN</td>
                        <td>5</td>
                        <td>
                            <span class="badge bg-warning text-dark">
                                En espera
                            </span>
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection