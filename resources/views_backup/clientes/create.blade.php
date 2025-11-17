@extends('layouts.app')

@section('title', 'Criar Cliente')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-plus-circle"></i> Criar Cliente</h2>
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('clientes.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome do Cliente</label>
                    <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome"
                        name="nome" value="{{ old('nome') }}" required>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
