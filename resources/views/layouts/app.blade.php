<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Objetos Perdidos - TecNM Mérida Campus Poniente')</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --tecnm-red:  #7E0827;
            --tecnm-gold: #D09700;
            --tecnm-dark: #5a0019;
            --tecnm-nav-text: #98A6BC;
        }
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden;
        }
        .bg-tecnm {
            background-color: var(--tecnm-red) !important;
            border: none !important;
            outline: none !important;
        }
        .text-tecnm {
            color: var(--tecnm-red) !important;
        }
        .btn-tecnm {
            background-color: var(--tecnm-gold);
            color: #ffffff;
            border-color: var(--tecnm-gold);
            font-weight: 600;
        }
        .btn-tecnm:hover {
            background-color: #b07d00;
            border-color: #b07d00;
            color: #ffffff;
        }
        .btn-warning {
            background-color: var(--tecnm-gold) !important;
            border-color: var(--tecnm-gold) !important;
            color: #ffffff !important;
        }
        .btn-warning:hover {
            background-color: #b07d00 !important;
            border-color: #b07d00 !important;
            color: #ffffff !important;
        }
        .badge-tecnm {
            background-color: var(--tecnm-gold);
            color: #ffffff;
            font-weight: 600;
        }
        /* Navbar items y enlaces */
        .bg-tecnm .navbar-nav .nav-item {
            display: flex;
            align-items: center;
        }
        .bg-tecnm .navbar-nav .nav-link {
            color: var(--tecnm-nav-text) !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            border-bottom: 3px solid transparent !important;
            border-radius: 0 !important;
            padding: 8px 12px !important;
            margin: 0 !important;
            outline: none !important;
            box-shadow: none !important;
            transition: color 0.15s ease, border-color 0.2s ease;
        }
        .bg-tecnm .navbar-nav .nav-link:hover {
            color: #ffffff !important;
            border-bottom-color: rgba(255, 255, 255, 0.6) !important;
        }
        .bg-tecnm .navbar-nav .nav-link.active,
        .bg-tecnm .navbar-nav .nav-link.is-current {
            color: #ffffff !important;
            font-weight: 700 !important;
            border-bottom-color: #ffffff !important;
            background-color: transparent !important;
        }
        .navbar-brand {
            color: #ffffff !important;
        }
        .card-custom {
            border-radius: 0.75rem;
            border: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .card-custom:hover {
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08);
        }
        .author-box {
            background-color: #f8fafc;
            border-left: 3px solid #94a3b8;
            padding: 0.4rem 0.65rem;
            border-radius: 0.25rem;
        }
        /* Estilos formales para tablas administrativas */
        .table-prefectura {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        .table-prefectura thead th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
            padding: 10px 14px;
            border-bottom: 2px solid #cbd5e1;
            border-top: none;
            vertical-align: middle;
        }
        .table-prefectura tbody td {
            padding: 12px 14px;
            font-size: 0.85rem;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            border-top: none;
        }
        .table-prefectura tbody tr:hover {
            background-color: #f8fafc;
        }
        .badge-subtle-perdido {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            font-weight: 600;
            font-size: 0.72rem;
            letter-spacing: 0.03em;
        }
        .badge-subtle-encontrado {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            font-weight: 600;
            font-size: 0.72rem;
            letter-spacing: 0.03em;
        }
        .badge-status-activo {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            font-weight: 600;
        }
        .badge-status-devuelto {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
            font-weight: 600;
        }
        .badge-status-cerrado {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            font-weight: 600;
        }
        /* Ocultar definitivamente el ojo nativo de contraseñas de Microsoft Edge y navegadores Chromium */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            pointer-events: none !important;
        }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Barra de Navegación Institucional (TecNM Mérida Poniente) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-tecnm sticky-top shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('items.index') }}">
                <img src="{{ asset('img/logo_tecnm.svg') }}" alt="Logo TecNM" style="height: 36px; width: auto; filter: brightness(0) invert(1);">
                <span>TecNM Mérida <span class="fw-light opacity-75 d-none d-sm-inline">| Campus Poniente</span></span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                {{-- Enlaces Principales de Navegación --}}
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('items.index') || request()->routeIs('home') ? 'active fw-bold' : '' }}" href="{{ route('items.index') }}">
                            <i class="bi bi-grid-fill me-1"></i> Muro de Objetos
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('items.my-items') ? 'active fw-bold' : '' }}" href="{{ route('items.my-items') }}">
                            <i class="bi bi-card-checklist me-1"></i> Mis Publicaciones
                        </a>
                    </li>

                    {{-- Enlace a Mis Mensajes (Requisito 6) --}}
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('messages.*') ? 'active fw-bold' : '' }}" href="{{ route('messages.index') }}">
                                <i class="bi bi-chat-left-dots-fill me-1"></i> Mis Mensajes
                            </a>
                        </li>
                    @endauth

                    {{-- Enlaces de Prefectura (visible para Prefectura y Administrador) --}}
                    @if (Auth::check() && in_array(Auth::user()->role, ['prefectura', 'admin'], true))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('prefectura.*') ? 'active fw-bold' : '' }}" href="{{ route('prefectura.dashboard') }}">
                                <i class="bi bi-shield-check me-1"></i> Panel Prefectura
                            </a>
                        </li>
                    @endif

                    {{-- Enlaces de Administración de Cuentas (visible solo para Admin) --}}
                    @if (Auth::check() && Auth::user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.*') ? 'active fw-bold' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="bi bi-people-fill me-1"></i> Cuentas (Admin)
                            </a>
                        </li>
                    @endif
                </ul>

                {{-- Menú de Usuario y Conmutador de Rol --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @auth
                        <div class="d-flex align-items-center bg-dark bg-opacity-25 rounded-pill px-3 py-1 text-white border border-light border-opacity-25">
                            <a href="{{ route('profile.edit') }}" class="text-white text-decoration-none me-2 small" title="Ver mi perfil">
                                <i class="bi bi-person-fill text-warning me-1"></i>{{ Auth::user()->name }}
                            </a>
                            @switch(Auth::user()->role)
                                @case('prefectura')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-shield-check me-1"></i>Prefectura</span>
                                    @break
                                @case('admin')
                                    <span class="badge bg-danger"><i class="bi bi-gear-fill me-1"></i>Admin</span>
                                    @break
                                @default
                                    <span class="badge bg-info text-dark"><i class="bi bi-person me-1"></i>Comunidad</span>
                            @endswitch
                        </div>

                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-light btn-sm" title="Editar Mi Perfil">
                            <i class="bi bi-gear"></i>
                        </a>

                        {{-- Botón de Cierre de Sesión (RF-63) --}}
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm" title="Cerrar Sesión">
                                <i class="bi bi-box-arrow-right"></i>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-warning btn-sm fw-bold">
                            <i class="bi bi-person-plus me-1"></i> Registrarse
                        </a>
                    @endauth

                </div>
            </div>
        </div>
    </nav>

    <!-- Contenedor Principal -->
    <main class="container-fluid px-4 py-4 flex-grow-1">
        {{-- Mensajes Flash de Sesión --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div>{{ session('warning') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                <i class="bi bi-x-octagon-fill me-2 fs-5"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any() && !$errors->hasBag('nameUpdate') && !$errors->hasBag('passwordUpdate'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-2"></i>Por favor verifique los siguientes errores:</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Pie de Página Institucional -->
    <footer class="bg-white border-top py-3 mt-auto">
        <div class="container-fluid px-4 text-center text-muted small">
            <div class="fw-semibold text-tecnm mb-1">Tecnológico Nacional de México - Campus Poniente Mérida</div>
            <div>Plataforma Institucional para la Gestión de Objetos Perdidos y Encontrados • Versión Prototipo</div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const currentPath = window.location.pathname.replace(/\/+$/, '');
            const navLinks = document.querySelectorAll('.bg-tecnm .navbar-nav .nav-link');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (!href) return;
                try {
                    const linkUrl = new URL(href, window.location.origin);
                    const linkPath = linkUrl.pathname.replace(/\/+$/, '');
                    
                    const isHome = (currentPath === '' || currentPath === '/muro') && (linkPath === '' || linkPath === '/muro');
                    const isMatch = isHome || (linkPath !== '' && linkPath !== '/' && currentPath.startsWith(linkPath));
                    
                    if (isMatch) {
                        link.classList.add('active', 'is-current');
                    }
                } catch (e) {}
            });
        });
    </script>
    <!-- Bootstrap 5.3 Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
