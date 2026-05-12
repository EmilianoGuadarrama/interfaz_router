@extends('layouts.dashboard')

@section('title', 'Estado - Procesos')
@section('page-title', 'Estado del Sistema')

@section('content')

<div class="panel-card">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h3 class="page-title mb-0">
            Procesos
        </h3>

        <button class="btn btn-main">
            <i class="bi bi-arrow-repeat"></i>
            Actualizar
        </button>

    </div>

    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6>Procesos activos</h6>
                <h3 class="mt-3">86</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6>CPU total</h6>
                <h3 class="mt-3">34%</h3>

                <div class="progress mt-3">
                    <div class="progress-bar bg-primary" style="width:34%"></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6>RAM utilizada</h6>
                <h3 class="mt-3">61%</h3>

                <div class="progress mt-3">
                    <div class="progress-bar bg-success" style="width:61%"></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats-card p-3">
                <h6>Procesos críticos</h6>
                <h3 class="mt-3 text-danger">3</h3>
            </div>
        </div>

    </div>

    <div class="panel-card p-3">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h5 class="mb-0">
                Lista de procesos
            </h5>

            <span class="soft-badge">
                top / ps aux
            </span>

        </div>

        <div class="table-responsive">

            <table class="table-dark-custom">

                <thead>
                    <tr>
                        <th>PID</th>
                        <th>Proceso</th>
                        <th>Usuario</th>
                        <th>CPU</th>
                        <th>RAM</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>102</td>
                        <td>dnsmasq</td>
                        <td>root</td>
                        <td>2%</td>
                        <td>15 MB</td>
                        <td>
                            <span class="badge bg-success">
                                Activo
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>214</td>
                        <td>hostapd</td>
                        <td>root</td>
                        <td>6%</td>
                        <td>22 MB</td>
                        <td>
                            <span class="badge bg-success">
                                Activo
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>301</td>
                        <td>dropbear</td>
                        <td>root</td>
                        <td>1%</td>
                        <td>8 MB</td>
                        <td>
                            <span class="badge bg-success">
                                Activo
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>412</td>
                        <td>firewall</td>
                        <td>system</td>
                        <td>12%</td>
                        <td>28 MB</td>
                        <td>
                            <span class="badge bg-warning text-dark">
                                Alto consumo
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>550</td>
                        <td>networkd</td>
                        <td>system</td>
                        <td>3%</td>
                        <td>11 MB</td>
                        <td>
                            <span class="badge bg-success">
                                Activo
                            </span>
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection