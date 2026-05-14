<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel de administración')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* TODO TU CSS ORIGINAL (NO SE TOCA) */
        :root {
            --bg-main: linear-gradient(90deg, #020d24 0%, #071a3b 50%, #102a57 100%);
            --sidebar-bg: rgba(15, 28, 56, 0.95);
            --sidebar-hover: rgba(72, 128, 255, 0.14);
            --sidebar-active: #2f80ff;
            --topbar-bg: rgba(13, 27, 55, 0.96);
            --card-bg: rgba(30, 49, 84, 0.96);
            --primary: #4a86f7;
            --primary-hover: #2f73f5;
            --text-main: #f5f7fb;
            --text-soft: #c4cfdf;
            --text-muted: #95a4bf;
            --border-soft: rgba(255, 255, 255, 0.08);
            --sidebar-width: 300px;
            --topbar-height: 78px;
            --radius-lg: 24px;
            --radius-md: 16px;
            --shadow-main: 0 20px 45px rgba(0, 0, 0, 0.35);
        }

        /* (se deja TODO igual, no se modifica nada de estilos) */
    </style>
</head>

<body>

<div class="app-wrapper">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-bar-chart-fill"></i>
            </div>
            <h1 class="brand-title">NuupNet</h1>
        </div>

        <div class="sidebar-body">

            <!-- ================= SISTEMA ================= -->
            <div class="menu-section">
                <button class="menu-toggle" data-bs-toggle="collapse" data-bs-target="#menuSistema" type="button">
                    <span>Sistema</span>
                    <i class="bi bi-chevron-down"></i>
                </button>

                <div class="collapse show" id="menuSistema">
                    <ul class="submenu">
                        <li><a href="#" class="nav-link"><i class="bi bi-pc-display"></i> Sistema</a></li>
                        <li><a href="#" class="nav-link"><i class="bi bi-person-gear"></i> Administración</a></li>

                        <li>
                            <a href="{{ route('startup') }}"
                               class="nav-link {{ request()->routeIs('startup') ? 'active' : '' }}">
                                <i class="bi bi-power"></i> Arranque
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('tasks') }}"
                               class="nav-link {{ request()->routeIs('tasks') ? 'active' : '' }}">
                                <i class="bi bi-clock-history"></i> Tareas programadas
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('leds.index') }}" class="nav-link">
                                <i class="bi bi-lightbulb"></i> Configuración de LEDs
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('grabado.index') }}"
                               class="nav-link {{ request()->routeIs('grabado.*') ? 'active' : '' }}">
                                <i class="bi bi-cloud-arrow-down"></i> Copia de seguridad
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('reiniciar.index') }}" class="nav-link">
                                <i class="bi bi-arrow-repeat"></i> Reiniciar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- ================= RED ================= -->
            <div class="menu-section">
                <button class="menu-toggle" data-bs-toggle="collapse" data-bs-target="#menuRed" type="button">
                    <span>Red</span>
                    <i class="bi bi-chevron-down"></i>
                </button>

                <div class="collapse show" id="menuRed">
                    <ul class="submenu">

                        <li>
                            <a href="{{ route('red.hostentries') }}"
                               class="nav-link {{ request()->routeIs('red.hostentries*') ? 'active' : '' }}">
                                <i class="bi bi-globe2"></i> Nombres de host
                            </a>
                        </li>

                    </ul>
                </div>
            </div>

            <!-- LOGOUT -->
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-link w-100">
                    <i class="bi bi-box-arrow-left"></i>
                    Cerrar sesión
                </button>
            </form>

        </div>
    </aside>

    <!-- ================= MAIN ================= -->
    <main class="main-content">
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggler" id="sidebarToggle" type="button">
                    <i class="bi bi-list"></i>
                </button>
                <h2 class="topbar-title">@yield('page-title')</h2>
            </div>

            <button class="btn btn-main">Refrescar</button>
        </header>

        <div class="content-area">
            @yield('content')
        </div>
    </main>
</div>

<!-- ================= SCRIPTS ================= -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.swal2-popup {
    background: rgba(30,49,84,0.96)!important;
    border-radius: 20px!important;
    color:#e2eaff!important;
}
</style>

<script>
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
    });
}
</script>

</body>
</html>