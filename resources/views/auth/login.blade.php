<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name', 'Gestão de Demandas') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="auth-body">
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-brand">
                <div class="auth-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5s-3 1.343-3 3 1.343 3 3 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M6 21v-1a6 6 0 0 1 12 0v1M6 21h12" />
                    </svg>
                </div>
                <p class="auth-eyebrow">Acesso Restrito</p>
                <h1 class="auth-title">{{ config('app.name', 'Gestão de Demandas') }}</h1>
            </div>

            @if ($errors->any())
                <div class="auth-errors">
                    <p><strong>Ops! Algo deu errado.</strong></p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="auth-field">
                    <label for="email" class="auth-label">Email</label>
                    <input class="auth-input" id="email" type="email" name="email" value="{{ old('email') }}"
                        required autofocus autocomplete="email" placeholder="seu@email.com">
                </div>

                <div class="auth-field">
                    <label for="password" class="auth-label">Senha</label>
                    <input class="auth-input" id="password" type="password" name="password" required
                        autocomplete="current-password" placeholder="********">
                </div>

                <button type="submit" class="auth-submit">Entrar</button>
            </form>

            <p class="auth-footer">
                © {{ date('Y') }} {{ config('app.name', 'Gestão de Demandas') }}. Todos os direitos reservados.
            </p>
        </div>
    </div>
</body>

</html>
