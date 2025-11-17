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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="app-shell antialiased">
    <button id="menuToggle"
        class="fixed left-4 top-4 z-50 rounded-lg bg-secondary px-3 py-2 text-white shadow-md transition hover:bg-primary focus:outline-none focus:ring-2 focus:ring-primary lg:hidden"
        aria-label="Abrir menu">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-sm lg:hidden hidden"></div>

    <div class="flex min-h-screen w-full">
        <nav id="sidebar"
            class="sidebar fixed inset-y-0 left-0 z-40 w-64 -translate-x-full transform bg-secondary text-white transition-transform duration-300 ease-in-out lg:relative lg:flex lg:translate-x-0 lg:flex-col">
            <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                <div>
                    <p class="text-sm uppercase tracking-widest text-white/60">Sistema</p>
                    <h4 class="text-xl font-semibold text-white">Gestão de Demandas</h4>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-3 py-4">
                <ul class="space-y-1 text-sm font-medium">
                    <li>
                        <a href="{{ route('dashboard') }}"
                            @class([
                                'sidebar-link',
                                'sidebar-link-active' => request()->routeIs('dashboard'),
                            ])>
                            <span class="text-lg">📊</span>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('demandas.index') }}"
                            @class([
                                'sidebar-link',
                                'sidebar-link-active' => request()->routeIs('demandas.*'),
                            ])>
                            <span class="text-lg">🗂️</span>
                            Demandas
                        </a>
                    </li>
                    @auth
                        @if (auth()->user()->isAdmin())
                            <li>
                                <a href="{{ route('users.index') }}"
                                    @class([
                                        'sidebar-link',
                                        'sidebar-link-active' => request()->routeIs('users.*'),
                                    ])>
                                    <span class="text-lg">👥</span>
                                    Usuários
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('clientes.index') }}"
                                    @class([
                                        'sidebar-link',
                                        'sidebar-link-active' => request()->routeIs('clientes.*'),
                                    ])>
                                    <span class="text-lg">🏢</span>
                                    Clientes
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('projetos.index') }}"
                                    @class([
                                        'sidebar-link',
                                        'sidebar-link-active' => request()->routeIs('projetos.*'),
                                    ])>
                                    <span class="text-lg">📁</span>
                                    Projetos
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('status.index') }}"
                                    @class([
                                        'sidebar-link',
                                        'sidebar-link-active' => request()->routeIs('status.*'),
                                    ])>
                                    <span class="text-lg">🏷️</span>
                                    Status
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
            </div>

            <div class="border-t border-white/10 px-4 py-4">
                <form method="POST" action="{{ route('logout') }}" class="space-y-3">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-lg border border-white/30 px-4 py-2 text-sm font-semibold text-white transition hover:border-white hover:bg-white/10">
                        <span>⏏️</span> Sair
                    </button>
                </form>
            </div>
        </nav>

        <main class="flex-1 px-4 py-6 lg:px-8">
            <div class="app-main">
                <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-widest text-slate-500">Painel</p>
                        <h1 class="text-2xl font-semibold text-slate-900">@yield('title', 'Sistema de Gestão de Demandas')</h1>
                    </div>
                    @auth
                        <div
                            class="flex items-center gap-3 rounded-full bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm">
                            <span class="text-xl">👤</span>
                            {{ auth()->user()->nome }}
                        </div>
                    @endauth
                </header>

                @if (session('success'))
                    <div class="alert alert-success" data-alert data-auto-dismiss="5000">
                        <span class="alert-icon">✅</span>
                        <div class="alert-body">
                            {{ session('success') }}
                        </div>
                        <button type="button" class="alert-close" data-alert-close aria-label="Fechar alerta">✕</button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error" data-alert>
                        <span class="alert-icon">⚠️</span>
                        <div class="alert-body">
                            {{ session('error') }}
                        </div>
                        <button type="button" class="alert-close" data-alert-close aria-label="Fechar alerta">✕</button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error" data-alert>
                        <span class="alert-icon">🚫</span>
                        <div class="alert-body">
                            <p class="font-semibold">Ocorreram alguns erros:</p>
                            <ul class="mt-2 list-inside list-disc space-y-1 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="alert-close" data-alert-close aria-label="Fechar alerta">✕</button>
                    </div>
                @endif

                <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100 lg:p-6">
                    @yield('content')
                </section>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    @yield('scripts')
    @stack('scripts')
</body>

</html>
