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
        :root{
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
            --border-soft: rgba(255,255,255,0.08);
            --sidebar-width: 300px;
            --topbar-height: 78px;
            --radius-lg: 24px;
            --radius-md: 16px;
            --shadow-main: 0 20px 45px rgba(0,0,0,0.35);
        }

        *{
            box-sizing: border-box;
        }

        html, body{
            margin: 0;
            padding: 0;
            min-height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--bg-main);
            color: var(--text-main);
        }

        body{
            overflow-x: hidden;
        }

        .app-wrapper{
            display: flex;
            min-height: 100vh;
        }

        /* =========================
           SIDEBAR
        ========================= */
        .sidebar{
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            backdrop-filter: blur(10px);
            border-right: 1px solid var(--border-soft);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 1040;
            box-shadow: 6px 0 30px rgba(0,0,0,.18);
        }

        .sidebar-brand{
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0 22px;
            border-bottom: 1px solid var(--border-soft);
        }

        .brand-icon{
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: linear-gradient(180deg, #63a4ff 0%, #3f7ef3 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            color: white;
            box-shadow: 0 10px 24px rgba(74, 134, 247, .35);
        }

        .brand-title{
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin: 0;
            color: #fff;
        }

        .sidebar-body{
            padding: 18px 0 24px;
        }

        .menu-section{
            margin-bottom: 10px;
        }

        .menu-toggle{
            width: 100%;
            border: none;
            background: transparent;
            color: var(--text-main);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1rem;
            font-weight: 700;
            transition: .2s ease;
        }

        .menu-toggle:hover{
            background: rgba(255,255,255,0.03);
        }

        .submenu{
            list-style: none;
            margin: 0;
            padding: 0 10px;
        }

        .submenu .nav-link{
            color: var(--text-soft);
            border-radius: 12px;
            padding: 11px 14px 11px 16px;
            margin: 4px 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-size: .95rem;
            font-weight: 500;
            transition: all .2s ease;
        }

        .submenu .nav-link:hover{
            background: var(--sidebar-hover);
            color: #ffffff;
        }

        .submenu .nav-link.active{
            background: linear-gradient(90deg, #4a86f7 0%, #2f73f5 100%);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 8px 18px rgba(74, 134, 247, .28);
        }

        .submenu-level-2{
            list-style: none;
            margin: 0;
            padding: 0 0 0 12px;
        }

        .submenu-level-2 .nav-link{
            font-size: .92rem;
            margin-left: 12px;
        }

        .logout-link{
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-soft);
            text-decoration: none;
            margin: 14px 18px 0;
            padding: 12px 14px;
            border-radius: 12px;
            transition: .2s ease;
        }

        .logout-link:hover{
            background: rgba(255,255,255,0.04);
            color: #fff;
        }

        /* =========================
           MAIN
        ========================= */
        .main-content{
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
        }

        .topbar{
            height: var(--topbar-height);
            background: var(--topbar-bg);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-soft);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .topbar-title{
            margin: 0;
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -0.7px;
        }

        .btn-main{
            background: linear-gradient(90deg, #4a86f7 0%, #3b7bf3 100%);
            border: none;
            color: white;
            padding: 10px 18px;
            border-radius: 14px;
            font-weight: 700;
            font-size: .92rem;
            box-shadow: 0 10px 24px rgba(74, 134, 247, .30);
        }

        .btn-main:hover{
            background: linear-gradient(90deg, #3d7cf4 0%, #2f73f5 100%);
            color: white;
        }

        .content-area{
            padding: 26px;
        }

        .panel-card,
        .stats-card{
            background: var(--card-bg);
            border: 1px solid var(--border-soft);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-main);
        }

        .panel-card{
            padding: 24px;
        }

        .page-title{
            font-size: 2.1rem;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 24px;
            color: #fff;
        }

        .nav-tabs{
            border-bottom: none;
            gap: 6px;
        }

        .nav-tabs .nav-link{
            border: none;
            border-radius: 12px 12px 0 0;
            color: var(--text-soft);
            background: rgba(255,255,255,0.05);
            font-weight: 600;
        }

        .nav-tabs .nav-link:hover{
            color: #fff;
            background: rgba(255,255,255,0.08);
        }

        .nav-tabs .nav-link.active{
            background: rgba(74,134,247,.20);
            color: #fff;
        }

        .form-control,
        .form-select{
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            color: #fff;
            border-radius: 14px;
            min-height: 46px;
        }

        .form-control:focus,
        .form-select:focus{
            background: rgba(255,255,255,0.08);
            color: #fff;
            border-color: #4a86f7;
            box-shadow: 0 0 0 .2rem rgba(74,134,247,.20);
        }

        .form-control::placeholder{
            color: #9eb0ca;
        }

        .form-check-input{
            width: 1.1rem;
            height: 1.1rem;
            background-color: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.15);
        }

        .form-check-input:checked{
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .table-dark-custom{
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border-radius: 18px;
            background: rgba(255,255,255,0.02);
        }

        .table-dark-custom thead th{
            background: rgba(255,255,255,0.05);
            color: #eaf1ff;
            font-size: .88rem;
            font-weight: 700;
            padding: 14px 12px;
            border-bottom: 1px solid var(--border-soft);
            white-space: nowrap;
        }

        .table-dark-custom tbody td{
            color: var(--text-soft);
            font-size: .9rem;
            padding: 13px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            white-space: nowrap;
        }

        .table-dark-custom tbody tr:hover{
            background: rgba(255,255,255,0.03);
        }

        .soft-badge{
            display: inline-block;
            padding: 6px 10px;
            border-radius: 10px;
            background: rgba(255,255,255,0.06);
            color: #d9e6ff;
            font-size: .82rem;
            font-weight: 600;
        }

        .sidebar-toggler{
            display: none;
            border: none;
            background: transparent;
            color: white;
            font-size: 1.5rem;
        }

        .small-chevron{
            transition: transform .2s ease;
        }

        @media (max-width: 992px){
            .sidebar{
                transform: translateX(-100%);
                transition: transform .28s ease;
            }

            .sidebar.show{
                transform: translateX(0);
            }

            .main-content{
                margin-left: 0;
                width: 100%;
            }

            .sidebar-toggler{
                display: inline-flex;
            }
        }
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
            <li>
    <a href="{{ route('network.hostentries') }}"
       class="nav-link {{ request()->routeIs('network.hostentries*') ? 'active' : '' }}">
        <i class="bi bi-globe2"></i> Nombres de host
    </a>
</li>
        <div class="sidebar-body">

            <div class="menu-section">
                <button class="menu-toggle" data-bs-toggle="collapse" data-bs-target="#menuEstado" type="button">
                    <span>Estado</span>
                    <i class="bi bi-chevron-down"></i>
                </button>

                <div class="collapse show" id="menuEstado">
                    <ul class="submenu">
                        <li><a href="#" class="nav-link"><i class="bi bi-grid"></i> Visión general</a></li>
                        <li><a href="#" class="nav-link"><i class="bi bi-shield-lock"></i> Cortafuegos</a></li>
                        <li><a href="#" class="nav-link"><i class="bi bi-signpost-2"></i> Rutas</a></li>
                        <li><a href="#" class="nav-link"><i class="bi bi-journal-text"></i> Registro del sistema</a></li>
                        <li><a href="#" class="nav-link"><i class="bi bi-cpu"></i> Registro del núcleo</a></li>
                        <li><a href="#" class="nav-link"><i class="bi bi-gear-wide-connected"></i> Procesos</a></li>
                        <li><a href="#" class="nav-link"><i class="bi bi-graph-up-arrow"></i> Gráficos en tiempo real</a></li>
                    </ul>
                </div>
            </div>

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
                               class="nav-link {{ request()->routeIs('startup') || request()->routeIs('startup.*') ? 'active' : '' }}">
                                <i class="bi bi-power"></i> Arranque
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('tasks') }}"
                               class="nav-link {{ request()->routeIs('tasks') || request()->routeIs('tasks.*') ? 'active' : '' }}">
                                <i class="bi bi-clock-history"></i> Tareas programadas
                            </a>
                        </li>
                       <li><a href="{{ route('leds.index') }}" class="nav-link"><i class="bi bi-lightbulb"></i> Configuración de LEDs</a></li>
                        <li><a href="{{ route('grabado.index') }}" class="nav-link {{ request()->routeIs('grabado.*') ? 'active' : '' }}"><i class="bi bi-cloud-arrow-down"></i> Copia de seguridad</a></li>
                        <li><a href="{{ route('reiniciar.index') }}" class="nav-link"><i class="bi bi-arrow-repeat"></i> Reiniciar</a></li>
                    </ul>
                </div>
            </div>

            <div class="menu-section">
                <button class="menu-toggle" data-bs-toggle="collapse" data-bs-target="#menuRed" type="button">
                    <span>Red</span>
                    <i class="bi bi-chevron-down"></i>
                </button>

                <div class="collapse show" id="menuRed">
                    <ul class="submenu">
                        <li>
                            <a href="{{ route('network.interfaces') }}" class="nav-link {{ request()->routeIs('network.interfaces*') ? 'active' : '' }}">
                                <i class="bi bi-diagram-3"></i> Interfaces
                            </a>
                        </li>

                        <li>
                            <a href="#" class="nav-link">
                                <i class="bi bi-wifi"></i> Wi-Fi
                            </a>
                        </li>

                        <li>
                            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#submenuConmutador" role="button" aria-expanded="true">
                                <span><i class="bi bi-hdd-network"></i> Conmutador</span>
                                <i class="bi bi-chevron-down small-chevron"></i>
                            </a>

                            <div class="collapse show" id="submenuConmutador">
                                <ul class="submenu-level-2">
                                    <li>
                                        <a href="{{ route('network.switch.general') }}" class="nav-link">
                                            Configuración general
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('network.switch.vlans') }}" class="nav-link">
                                            VLANs
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li>
                            <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#submenuDhcpDns" role="button" aria-expanded="true">
                                <span><i class="bi bi-router"></i> DHCP y DNS</span>
                                <i class="bi bi-chevron-down small-chevron"></i>
                            </a>

                            <div class="collapse show" id="submenuDhcpDns">
                                <ul class="submenu-level-2">
                                    <li>
                                        <a href="{{ route('network.dhcpdns.general') }}" class="nav-link">
                                            Configuración general
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('network.dhcpdns.resolv') }}" class="nav-link">
                                            Archivos Resolv y Hosts
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('network.dhcpdns.tftp') }}" class="nav-link">
                                            Configuración TFTP
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('network.dhcpdns.advanced') }}" class="nav-link">
                                            Configuración avanzada
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('network.dhcpdns.static') }}" class="nav-link">
                                            Asignaciones estáticas
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                       <li>
                                         <a href="{{ route('network.hostentries') }}" class="nav-link {{ request()->routeIs('network.hostentries*') ? 'active' : '' }}">
                                 <i class="bi bi-globe2"></i> Nombres de host
    </a>
</li>

                        <li>
                            <a href="{{ route('network.routes.static.ipv4') }}" class="nav-link {{ request()->routeIs('network.routes.static.*') ? 'active' : '' }}">
                                <i class="bi bi-sign-turn-right"></i> Rutas estáticas
                            </a>
                        </li>

                        <li>
                            <a href="#" class="nav-link">
                                <i class="bi bi-activity"></i> Diagnósticos
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <a href="#" class="logout-link">
                <i class="bi bi-box-arrow-left"></i>
                Cerrar sesión
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggler" id="sidebarToggle" type="button">
                    <i class="bi bi-list"></i>
                </button>
                <h2 class="topbar-title">@yield('page-title', 'Panel principal')</h2>
            </div>

            <button class="btn btn-main" type="button">Refrescar</button>
        </header>

        <div class="content-area">
            @yield('content')
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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
