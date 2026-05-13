@extends('layouts.dashboard')

@section('title', 'Estado - Gráficos en tiempo real')
@section('page-title', 'Estado del Sistema')

@section('content')

<div class="panel-card">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h3 class="page-title mb-0">
            Gráficos en tiempo real
        </h3>

        <button class="btn btn-main">
            <i class="bi bi-arrow-repeat"></i>
            Actualizar
        </button>

    </div>

    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="stats-card p-3">
                <h6>CPU</h6>

                <div class="d-flex justify-content-between mt-3">
                    <h3>37%</h3>
                    <span class="badge bg-primary">Normal</span>
                </div>

                <div class="progress mt-3">
                    <div class="progress-bar bg-primary" style="width:37%"></div>
                </div>

            </div>
        </div>

        <div class="col-md-4">
            <div class="stats-card p-3">
                <h6>Memoria RAM</h6>

                <div class="d-flex justify-content-between mt-3">
                    <h3>68%</h3>
                    <span class="badge bg-success">Estable</span>
                </div>

                <div class="progress mt-3">
                    <div class="progress-bar bg-success" style="width:68%"></div>
                </div>

            </div>
        </div>

        <div class="col-md-4">
            <div class="stats-card p-3">
                <h6>Red</h6>

                <div class="d-flex justify-content-between mt-3">
                    <h3>125 Mbps</h3>
                    <span class="badge bg-info text-dark">
                        Tráfico
                    </span>
                </div>

                <div class="progress mt-3">
                    <div class="progress-bar bg-info" style="width:80%"></div>
                </div>

            </div>
        </div>

    </div>

    <div class="row g-4">

        <div class="col-md-6">

            <div class="panel-card p-4 h-100">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h5 class="mb-0">
                        Uso de CPU
                    </h5>

                    <span class="soft-badge">
                        Tiempo real
                    </span>

                </div>

                <div style="
                    height: 260px;
                    background: rgba(255,255,255,0.03);
                    border-radius: 18px;
                    position: relative;
                    overflow: hidden;
                ">

                    <svg width="100%" height="260">

                        <polyline
                            fill="none"
                            stroke="#4a86f7"
                            stroke-width="4"
                            points="
                                0,180
                                50,160
                                100,170
                                150,120
                                200,130
                                250,90
                                300,110
                                350,80
                                400,100
                                450,60
                                500,95
                            "
                        />

                    </svg>

                </div>

            </div>

        </div>

        <div class="col-md-6">

            <div class="panel-card p-4 h-100">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h5 class="mb-0">
                        Consumo de red
                    </h5>

                    <span class="soft-badge">
                        WAN / LAN
                    </span>

                </div>

                <div style="
                    height: 260px;
                    background: rgba(255,255,255,0.03);
                    border-radius: 18px;
                    position: relative;
                    overflow: hidden;
                ">

                    <svg width="100%" height="260">

                        <polyline
                            fill="none"
                            stroke="#22c55e"
                            stroke-width="4"
                            points="
                                0,210
                                50,180
                                100,190
                                150,160
                                200,140
                                250,120
                                300,100
                                350,110
                                400,85
                                450,95
                                500,70
                            "
                        />

                    </svg>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection