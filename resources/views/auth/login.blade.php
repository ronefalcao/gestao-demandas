<!DOCTYPE html>
<html lang="pt-BR" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name', 'Gestão de Demandas') }}</title>
    @vite(['resources/css/app.css', 'resources/css/filament/admin/theme.css'])
</head>

<body class="min-h-full bg-gradient-to-br from-[rgb(var(--primary-900))] to-[rgb(var(--secondary-800))]">
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div
            class="w-full max-w-md rounded-2xl border border-white/10 bg-white/10 p-10 text-white shadow-2xl backdrop-blur-xl">
            <div class="mb-10 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-white/15">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5s-3 1.343-3 3 1.343 3 3 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M6 21v-1a6 6 0 0 1 12 0v1M6 21h12" />
                    </svg>
                </div>
                <p class="mt-6 text-sm uppercase tracking-[0.35em] text-white/70">Acesso Restrito</p>
                <h1 class="mt-2 text-2xl font-semibold text-white">{{ config('app.name', 'Gestão de Demandas') }}</h1>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-300/40 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                    <p class="font-medium">Ops! Algo deu errado.</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-white/80">Email</label>
                    <input
                        class="block w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder:text-white/40 transition focus:border-[rgb(var(--primary-300))] focus:bg-slate-900/30 focus:outline-none focus:ring-2 focus:ring-[rgb(var(--primary-400))]"
                        id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        autocomplete="email">
                </div>

                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-white/80">Senha</label>
                    <input
                        class="block w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder:text-white/40 transition focus:border-[rgb(var(--primary-300))] focus:bg-slate-900/30 focus:outline-none focus:ring-2 focus:ring-[rgb(var(--primary-400))]"
                        id="password" type="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/40 transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-primary/60">
                    Entrar
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-white/50">
                © {{ date('Y') }} {{ config('app.name', 'Gestão de Demandas') }}. Todos os direitos reservados.
            </p>
        </div>
    </div>
</body>

</html>
