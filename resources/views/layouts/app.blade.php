<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Gestão de Demandas')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
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
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar p-0">
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
    @yield('scripts')
</body>

</html>
