@extends('layouts.dashboard')

@section('title', 'Estado - Visión general')
@section('page-title', 'Estado del Sistema')

@section('content')

<div class="panel-card">

    <h3 class="page-title">Visión general</h3>

    <div class="row g-4">

        <!-- Uptime -->
        <div class="col-md-4">
            <div class="stats-card p-3">
                <h6>Uptime</h6>
                <h4>2 días 4 horas</h4>
            </div>
        </div>

        <!-- CPU -->
        <div class="col-md-4">
            <div class="stats-card p-3">
                <h6>CPU</h6>
                <h4>35%</h4>
                <div class="progress">
                    <div class="progress-bar bg-primary" style="width:35%"></div>
                </div>
            </div>
        </div>

        <!-- Memoria -->
        <div class="col-md-4">
            <div class="stats-card p-3">
                <h6>Memoria</h6>
                <h4>60%</h4>
                <div class="progress">
                    <div class="progress-bar bg-success" style="width:60%"></div>
                </div>
            </div>
        </div>

        <!-- Firmware -->
        <div class="col-md-6">
            <div class="panel-card p-3">
                <h6>Firmware</h6>
                <p>v1.0.0</p>
            </div>
        </div>

        <!-- Temperatura -->
        <div class="col-md-6">
            <div class="panel-card p-3">
                <h6>Temperatura</h6>
                <p>45°C</p>
            </div>
        </div>

        <!-- Estado -->
        <div class="col-md-12">
            <div class="panel-card p-3">
                <h6>Estado del sistema</h6>
                <span class="badge bg-success">Activo</span>
            </div>
        </div>

    </div>

</div>

@endsection