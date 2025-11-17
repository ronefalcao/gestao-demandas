@extends('layouts.app')

@section('title', 'Visualizar Projeto')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-eye"></i> Visualizar Projeto</h2>
        <a href="{{ route('projetos.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Nome:</strong> {{ $projeto->nome }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong>
                        <span class="badge {{ $projeto->ativo ? 'bg-success' : 'bg-secondary' }}">
                            {{ $projeto->ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </p>
                </div>
            </div>

            @if ($projeto->descricao)
                <div class="mb-3">
                    <p><strong>Descrição:</strong></p>
                    <div class="p-3 bg-light rounded">
                        {{ $projeto->descricao }}
                    </div>
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Criado em:</strong> {{ $projeto->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Atualizado em:</strong> {{ $projeto->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('projetos.edit', $projeto) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            </div>
        </div>
    </div>
@endsection

