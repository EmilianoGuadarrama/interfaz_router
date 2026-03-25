@extends('layouts.dashboard')

@section('title', 'Dashboard principal')

@section('content')
    <div class="banner-warning">
        <strong>Atención:</strong> No hay una contraseña root configurada para proteger completamente la interfaz web del router.
    </div>

    <h2 class="page-title">Estado del sistema</h2>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="bi bi-wifi"></i>
                </div>
                <div class="stats-label">Red WiFi activa</div>
                <h3 class="stats-number">Sí</h3>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="stats-label">Reglas del firewall</div>
                <h3 class="stats-number">12</h3>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="bi bi-diagram-3-fill"></i>
                </div>
                <div class="stats-label">Interfaces conectadas</div>
                <h3 class="stats-number">3</h3>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="bi bi-speedometer2"></i>
                </div>
                <div class="stats-label">Tráfico actual</div>
                <h3 class="stats-number">84.79 KB</h3>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-heading">
            <h4>Estado del cortafuegos</h4>
            <div class="panel-actions">
                <a href="#">Ocultar cadenas vacías</a>
                <a href="#">Reiniciar contadores</a>
                <a href="#">Reiniciar cortafuegos</a>
            </div>
        </div>

        <div class="subheading">Cadena INPUT (Política: ACCEPT)</div>

        <div class="table-responsive mb-4">
            <table class="table-dark-custom">
                <thead>
                <tr>
                    <th>Paq.</th>
                    <th>Tráfico</th>
                    <th>Objetivo</th>
                    <th>Prot.</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Opciones</th>
                    <th>Comentario</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>33</td>
                    <td>2.25 KB</td>
                    <td>ACCEPT</td>
                    <td>all</td>
                    <td><span class="soft-badge">lo</span></td>
                    <td>*</td>
                    <td>0.0.0.0/0</td>
                    <td>0.0.0.0/0</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>582</td>
                    <td>84.79 KB</td>
                    <td>input_rule</td>
                    <td>all</td>
                    <td>*</td>
                    <td>*</td>
                    <td>0.0.0.0/0</td>
                    <td>0.0.0.0/0</td>
                    <td>-</td>
                    <td>Custom input rule chain</td>
                </tr>
                <tr>
                    <td>525</td>
                    <td>80.05 KB</td>
                    <td>ACCEPT</td>
                    <td>all</td>
                    <td>*</td>
                    <td>*</td>
                    <td>0.0.0.0/0</td>
                    <td>0.0.0.0/0</td>
                    <td>ctstate RELATED,ESTABLISHED</td>
                    <td>-</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="subheading">Cadena FORWARD (Política: DROP)</div>

        <div class="table-responsive">
            <table class="table-dark-custom">
                <thead>
                <tr>
                    <th>Paq.</th>
                    <th>Tráfico</th>
                    <th>Objetivo</th>
                    <th>Prot.</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Opciones</th>
                    <th>Comentario</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>0</td>
                    <td>0 B</td>
                    <td>forwarding_rule</td>
                    <td>all</td>
                    <td>*</td>
                    <td>*</td>
                    <td>0.0.0.0/0</td>
                    <td>0.0.0.0/0</td>
                    <td>-</td>
                    <td>Custom forwarding rule chain</td>
                </tr>
                <tr>
                    <td>0</td>
                    <td>0 B</td>
                    <td>ACCEPT</td>
                    <td>all</td>
                    <td>*</td>
                    <td>*</td>
                    <td>0.0.0.0/0</td>
                    <td>0.0.0.0/0</td>
                    <td>ctstate RELATED,ESTABLISHED</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>0</td>
                    <td>0 B</td>
                    <td>zone_lan_forward</td>
                    <td>all</td>
                    <td><span class="soft-badge">br-lan</span></td>
                    <td>*</td>
                    <td>0.0.0.0/0</td>
                    <td>0.0.0.0/0</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
