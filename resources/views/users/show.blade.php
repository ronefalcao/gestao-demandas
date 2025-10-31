@extends('layouts.app')

@section('title', 'Visualizar Usuário')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-eye"></i> Visualizar Usuário</h2>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Nome:</strong> {{ $user->nome }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Telefone:</strong> {{ $user->telefone ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Tipo:</strong>
                        <span
                            class="badge {{ $user->tipo === 'administrador' ? 'bg-danger' : ($user->tipo === 'gestor' ? 'bg-warning' : 'bg-primary') }}">
                            {{ ucfirst($user->tipo) }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Criado em:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Atualizado em:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <p><strong>Projetos Associados:</strong></p>
                    @if ($user->isAdmin())
                        <span class="badge bg-info">Acesso a todos os projetos</span>
                    @else
                        @php
                            $user->load('projetos');
                        @endphp
                        @if ($user->projetos->count() > 0)
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($user->projetos as $projeto)
                                    <span class="badge bg-primary">{{ $projeto->nome }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">Nenhum projeto associado</span>
                        @endif
                    @endif
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            </div>
        </div>
    </div>
@endsection
