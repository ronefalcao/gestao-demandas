@extends('layouts.app')

@section('title', 'Visualizar Cliente')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-eye"></i> Visualizar Cliente</h2>
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Nome:</strong> {{ $cliente->nome }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Criado em:</strong> {{ $cliente->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Atualizado em:</strong> {{ $cliente->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            </div>
        </div>
    </div>
@endsection
