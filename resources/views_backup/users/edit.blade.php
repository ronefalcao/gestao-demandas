@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-pencil"></i> Editar Usuário</h2>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome"
                            name="nome" value="{{ old('nome', $user->nome) }}" required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" class="form-control @error('telefone') is-invalid @enderror" id="telefone"
                            name="telefone" value="{{ old('telefone', $user->telefone) }}">
                        @error('telefone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select class="form-select @error('tipo') is-invalid @enderror" id="tipo" name="tipo"
                            required>
                            <option value="">Selecione o tipo</option>
                            <option value="administrador"
                                {{ old('tipo', $user->tipo) == 'administrador' ? 'selected' : '' }}>Administrador
                            </option>
                            <option value="gestor" {{ old('tipo', $user->tipo) == 'gestor' ? 'selected' : '' }}>
                                Gestor</option>
                            <option value="usuario" {{ old('tipo', $user->tipo) == 'usuario' ? 'selected' : '' }}>
                                Usuário</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Nova Senha</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                            name="password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Deixe em branco para manter a senha atual. Mínimo de 8
                            caracteres</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>
                </div>

                @if (!$user->isAdmin())
                    <div class="mb-3">
                        <label for="projetos" class="form-label">Projetos Associados</label>
                        <small class="form-text text-muted d-block mb-2">Selecione os projetos aos quais este usuário terá
                            acesso. Administradores têm acesso a todos os projetos.</small>
                        <select class="form-select @error('projetos') is-invalid @enderror" id="projetos" name="projetos[]"
                            multiple size="5">
                            @foreach ($projetos as $projeto)
                                <option value="{{ $projeto->id }}"
                                    {{ $user->projetos->contains($projeto->id) ? 'selected' : '' }}>
                                    {{ $projeto->nome }}
                                    @if (!$projeto->ativo)
                                        (Inativo)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('projetos')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Mantenha a tecla Ctrl (ou Cmd no Mac) pressionada para
                            selecionar múltiplos projetos.</small>
                    </div>
                @else
                    <div class="mb-3">
                        <label class="form-label">Projetos Associados</label>
                        <small class="form-text text-muted d-block">Usuários administradores têm acesso a todos os projetos
                            automaticamente.</small>
                    </div>
                @endif

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Atualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
