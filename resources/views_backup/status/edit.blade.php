@extends('layouts.app')

@section('title', 'Editar Status')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-pencil"></i> Editar Status</h2>
        <a href="{{ route('status.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('status.update', $status) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome do Status</label>
                    <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome"
                        name="nome" value="{{ old('nome', $status->nome) }}" required>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="cor" class="form-label">Cor (hexadecimal)</label>
                    <input type="color" class="form-control form-control-color @error('cor') is-invalid @enderror"
                        id="cor" name="cor" value="{{ old('cor', $status->cor ?? '#6c757d') }}">
                    @error('cor')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Selecione a cor para o status</small>
                </div>

                <div class="mb-3">
                    <label for="ordem" class="form-label">Ordem</label>
                    <input type="number" class="form-control @error('ordem') is-invalid @enderror" id="ordem"
                        name="ordem" value="{{ old('ordem', $status->ordem) }}">
                    @error('ordem')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Atualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection



