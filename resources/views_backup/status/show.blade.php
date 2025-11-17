@extends('layouts.app')

@section('title', 'Visualizar Status')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-eye"></i> Visualizar Status</h2>
        <a href="{{ route('status.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Nome:</strong> {{ $status->nome }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Cor:</strong>
                        <span class="badge" style="background-color: {{ $status->cor ?? '#6c757d' }}">
                            {{ $status->cor ?? 'N/A' }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Ordem:</strong> {{ $status->ordem }}</p>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('status.edit', $status) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            </div>
        </div>
    </div>
@endsection



