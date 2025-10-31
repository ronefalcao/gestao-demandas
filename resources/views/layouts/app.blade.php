<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Gestão de Demandas')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Roboto:wght@400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --bs-primary: #0F6EBF;
            --bs-primary-rgb: 15, 110, 191;
            --font-primary: 'Open Sans', sans-serif;
            --font-secondary: 'Roboto', sans-serif;
        }

        body {
            font-family: var(--font-secondary);
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .h1,
        .h2,
        .h3,
        .h4,
        .h5,
        .h6 {
            font-family: var(--font-primary);
            font-weight: 600;
        }

        .sidebar,
        .sidebar .nav-link,
        .sidebar h4 {
            font-family: var(--font-primary);
        }

        label,
        .form-label {
            font-family: var(--font-secondary);
            font-weight: 500;
        }

        p,
        .descricao,
        td,
        .text-muted {
            font-family: var(--font-secondary);
        }

        .sidebar {
            min-height: 100vh;
            background: #08253D;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 15px 20px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #34495e;
            color: #fff;
        }

        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }

        /* Botão hamburger */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: #08253D;
            border: none;
            color: white;
            font-size: 1.5rem;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* Overlay para fechar menu ao clicar fora */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* Ajustes para mobile */
        @media (max-width: 991.98px) {
            .sidebar {
                width: 260px;
            }

            .menu-toggle {
                display: block;
            }

            .main-content {
                margin-left: 0 !important;
            }
        }

        /* Desktop: mostrar sidebar sempre */
        @media (min-width: 992px) {
            .sidebar {
                position: relative;
                transform: translateX(0) !important;
            }

            .main-content {
                margin-left: auto;
            }
        }
    </style>
</head>

<body>
    <!-- Botão hamburger -->
    <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
        <i class="bi bi-list"></i>
    </button>

    <!-- Overlay para fechar menu -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar p-0 hidden" id="sidebar">
                <div class="p-3 text-white">
                    <h4>Sistema de Demandas</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('dashboard')) active @endif"
                            href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('demandas.*')) active @endif"
                            href="{{ route('demandas.index') }}">
                            <i class="bi bi-file-text"></i> Demandas
                        </a>
                    </li>
                    @auth
                        @if (auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link @if (request()->routeIs('users.*')) active @endif"
                                    href="{{ route('users.index') }}">
                                    <i class="bi bi-people-fill"></i> Usuários
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (request()->routeIs('clientes.*')) active @endif"
                                    href="{{ route('clientes.index') }}">
                                    <i class="bi bi-people"></i> Clientes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (request()->routeIs('projetos.*')) active @endif"
                                    href="{{ route('projetos.index') }}">
                                    <i class="bi bi-folder"></i> Projetos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (request()->routeIs('status.*')) active @endif"
                                    href="{{ route('status.index') }}">
                                    <i class="bi bi-tag"></i> Status
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
                <div class="p-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-light w-100">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </button>
                    </form>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content p-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle do menu mobile
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleMenu() {
            sidebar.classList.toggle('hidden');
            overlay.classList.toggle('active');
        }

        menuToggle.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);

        // Fechar menu ao clicar em um link (mobile)
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    toggleMenu();
                }
            });
        });

        // Fechar menu ao clicar no botão de logout (mobile)
        const logoutBtn = document.querySelector('.sidebar form button');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    toggleMenu();
                }
            });
        }

        // Auto-fechar alertas após 5 segundos
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            setTimeout(() => {
                bsAlert.close();
            }, 5000);
        });
    </script>
    @yield('scripts')
</body>

</html>
