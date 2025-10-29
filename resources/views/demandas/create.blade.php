@extends('layouts.app')

@section('title', 'Criar Demanda')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-plus-circle"></i> Criar Demanda</h2>
        <a href="{{ route('demandas.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('demandas.store') }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="data" class="form-label">Data</label>
                        <input type="date" class="form-control @error('data') is-invalid @enderror" id="data"
                            name="data" value="{{ old('data', now()->format('Y-m-d')) }}" required>
                        @error('data')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="cliente_id" class="form-label">Cliente</label>
                        <select class="form-select @error('cliente_id') is-invalid @enderror" id="cliente_id"
                            name="cliente_id" required>
                            <option value="">Selecione um cliente</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}"
                                    {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('cliente_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    @if (auth()->user()->isUsuario())
                        <div class="col-md-12">
                            <label for="solicitante_id" class="form-label">Solicitante</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->nome }}" readonly>
                            <input type="hidden" name="solicitante_id" value="{{ auth()->id() }}">
                        </div>
                    @else
                        <div class="col-md-6">
                            <label for="solicitante_id" class="form-label">Solicitante</label>
                            <select class="form-select @error('solicitante_id') is-invalid @enderror" id="solicitante_id"
                                name="solicitante_id" required>
                                <option value="">Selecione um solicitante</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('solicitante_id', auth()->id()) == $user->id ? 'selected' : '' }}>
                                        {{ $user->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('solicitante_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if (!auth()->user()->isUsuario())
                        <div class="col-md-6">
                            <label for="responsavel_id" class="form-label">Responsável</label>
                            <select class="form-select @error('responsavel_id') is-invalid @enderror" id="responsavel_id"
                                name="responsavel_id" required>
                                <option value="">Selecione um responsável</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('responsavel_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('responsavel_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="modulo" class="form-label">Módulo</label>
                        <input type="text" class="form-control @error('modulo') is-invalid @enderror" id="modulo"
                            name="modulo" value="{{ old('modulo') }}" required>
                        @error('modulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if (auth()->user()->isUsuario())
                        <div class="col-md-6">
                            <label for="status_id" class="form-label">Status</label>
                            <input type="text" class="form-control"
                                value="{{ $statuses->first()->nome ?? 'Solicitada' }}" readonly>
                            <input type="hidden" name="status_id" value="{{ $statuses->first()->id ?? '' }}">
                        </div>
                    @else
                        <div class="col-md-6">
                            <label for="status_id" class="form-label">Status</label>
                            <select class="form-select @error('status_id') is-invalid @enderror" id="status_id"
                                name="status_id" required>
                                <option value="">Selecione um status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}"
                                        {{ old('status_id') == $status->id ? 'selected' : '' }}>
                                        {{ $status->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea class="form-control @error('descricao') is-invalid @enderror" id="descricao" name="descricao" rows="5"
                        required>{{ old('descricao') }}</textarea>
                    @error('descricao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="observacao" class="form-label">Observação</label>
                    <textarea class="form-control @error('observacao') is-invalid @enderror" id="observacao" name="observacao"
                        rows="3">{{ old('observacao') }}</textarea>
                    @error('observacao')
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
