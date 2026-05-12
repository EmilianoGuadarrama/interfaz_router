@extends('layouts.dashboard')

@section('title', 'Estado - Cortafuegos')
@section('page-title', 'Estado del Sistema')

@section('content')

<div class="panel-card">

    <h3 class="page-title">Cortafuegos</h3>

    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="stats-card p-3 h-100">
                <h6>Estado del firewall</h6>

                <div class="d-flex align-items-center justify-content-between mt-3">
                    <h3 class="mb-0">Activo</h3>

                    <span class="badge bg-success px-3 py-2">
                        Online
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stats-card p-3 h-100">
                <h6>Reglas activas</h6>

                <div class="mt-3">
                    <h3>24</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stats-card p-3 h-100">
                <h6>Intentos bloqueados</h6>

                <div class="mt-3">
                    <h3>128</h3>
                </div>
            </div>
        </div>

    </div>

    <div class="panel-card p-3">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Reglas del cortafuegos</h5>

            <button class="btn btn-main">
                <i class="bi bi-arrow-clockwise"></i>
                Actualizar
            </button>
        </div>

        <div class="table-responsive">

            <table class="table-dark-custom">

                <thead>
                    <tr>
                        <th>Regla</th>
                        <th>Puerto</th>
                        <th>Protocolo</th>
                        <th>Origen</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>HTTP</td>
                        <td>80</td>
                        <td>TCP</td>
                        <td>WAN</td>
                        <td>
                            <span class="badge bg-success">
                                Permitido
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>HTTPS</td>
                        <td>443</td>
                        <td>TCP</td>
                        <td>WAN</td>
                        <td>
                            <span class="badge bg-success">
                                Permitido
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>SSH</td>
                        <td>22</td>
                        <td>TCP</td>
                        <td>Externo</td>
                        <td>
                            <span class="badge bg-danger">
                                Bloqueado
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>DNS</td>
                        <td>53</td>
                        <td>UDP</td>
                        <td>LAN</td>
                        <td>
                            <span class="badge bg-success">
                                Permitido
                            </span>
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection