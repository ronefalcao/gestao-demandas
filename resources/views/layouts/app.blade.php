<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestão de Demandas')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Roboto:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="app-body">
    <div class="app-shell">
        <aside class="sidebar">
            <div class="sidebar-brand d-flex align-items-center gap-3">
                <div class="brand-mark">
                    <span class="bi bi-stack"></span>
                </div>
                <div>
                    <p class="text-uppercase text-white-50 small mb-1">Sistema</p>
                    <strong>Gestão de Demandas</strong>
                </div>
            </div>

            <nav class="sidebar-nav flex-grow-1">
                <a href="{{ route('dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="bi bi-speedometer2"></span>
                    Dashboard
                </a>
                <a href="{{ route('demandas.index') }}"
                    class="sidebar-link {{ request()->routeIs('demandas.*') ? 'active' : '' }}">
                    <span class="bi bi-kanban"></span>
                    Demandas
                </a>

                @auth
                    <span class="sidebar-section">Administração</span>
                    <a href="{{ route('users.index') }}"
                        class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <span class="bi bi-people"></span>
                        Usuários
                    </a>
                    <a href="{{ route('clientes.index') }}"
                        class="sidebar-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                        <span class="bi bi-building"></span>
                        Clientes
                    </a>
                    <a href="{{ route('projetos.index') }}"
                        class="sidebar-link {{ request()->routeIs('projetos.*') ? 'active' : '' }}">
                        <span class="bi bi-folder"></span>
                        Projetos
                    </a>
                    <a href="{{ route('status.index') }}"
                        class="sidebar-link {{ request()->routeIs('status.*') ? 'active' : '' }}">
                        <span class="bi bi-tags"></span>
                        Status
                    </a>
                @endauth
            </nav>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn w-100 btn-outline-light">
                        <span class="bi bi-box-arrow-right me-2"></span> Sair
                    </button>
                </form>
            </div>
        </aside>

        <main class="app-content">
            <header class="app-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                <div>
                    <p class="text-uppercase text-muted small mb-1">Painel</p>
                    <h1 class="h4 mb-0">@yield('title', 'Gestão de Demandas')</h1>
                </div>
                @auth
                    <div class="app-user-chip d-flex align-items-center gap-2">
                        <span class="bi bi-person-circle fs-4 text-primary"></span>
                        <div>
                            <div class="fw-semibold">{{ auth()->user()->nome }}</div>
                            <small class="text-muted text-capitalize">{{ auth()->user()->tipo }}</small>
                        </div>
                    </div>
                @endauth
            </header>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <p class="fw-semibold mb-2">Ocorreram alguns erros:</p>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif

            <section class="app-card">
                @yield('content')
            </section>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    @yield('scripts')
    @stack('scripts')
</body>

</html>
