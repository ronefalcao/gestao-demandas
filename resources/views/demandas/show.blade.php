@extends('layouts.app')

@section('title', 'Visualizar Demanda')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-eye"></i> Visualizar Demanda</h2>
        <a href="{{ route('demandas.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Data:</strong> {{ $demanda->data->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Cliente:</strong> {{ $demanda->cliente->nome }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Projeto:</strong> {{ $demanda->projeto ? $demanda->projeto->nome : 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Módulo:</strong> {{ $demanda->modulo }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Status:</strong>
                        <span class="badge" style="background-color: {{ $demanda->status->cor ?? '#6c757d' }}">
                            {{ $demanda->status->nome }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Solicitante:</strong> {{ $demanda->solicitante->nome }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Responsável:</strong> {{ $demanda->responsavel ? $demanda->responsavel->nome : 'N/A' }}</p>
                </div>
            </div>

            <div class="mb-3">
                <p><strong>Descrição:</strong></p>
                <div class="p-3 bg-light rounded">
                    {{ $demanda->descricao }}
                </div>
            </div>

            @if ($demanda->observacao)
                <div class="mb-3">
                    <p><strong>Observação:</strong></p>
                    <div class="p-3 bg-light rounded">
                        {{ $demanda->observacao }}
                    </div>
                </div>
            @endif

            @if (!auth()->user()->isUsuario())
                <div class="d-flex gap-2">
                    <a href="{{ route('demandas.edit', $demanda) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

