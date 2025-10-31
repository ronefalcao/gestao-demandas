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
                <div class="col-md-12">
                    <p><strong>Número:</strong> <span class="badge bg-primary">{{ $demanda->numero }}</span></p>
                </div>
            </div>

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

            <!-- Seção de Arquivos -->
            <div class="mt-4 border-top pt-4">
                <h5 class="mb-3"><i class="bi bi-paperclip"></i> Arquivos Anexados</h5>

                <!-- Formulário de Upload -->
                <form action="{{ route('demandas.arquivos.upload', $demanda) }}" method="POST"
                    enctype="multipart/form-data" class="mb-3">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <input type="file" class="form-control @error('arquivo') is-invalid @enderror" name="arquivo"
                                accept=".pdf,.jpeg,.jpg,.png" required>
                            @error('arquivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Formatos aceitos: PDF, JPEG, JPG, PNG (máximo 10MB)</small>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-upload"></i> Enviar Arquivo
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Lista de Arquivos -->
                @if ($demanda->arquivos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome do Arquivo</th>
                                    <th>Tipo</th>
                                    <th>Tamanho</th>
                                    <th>Data de Upload</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($demanda->arquivos as $arquivo)
                                    <tr>
                                        <td>{{ $arquivo->nome_original }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ strtoupper($arquivo->tipo) }}</span>
                                        </td>
                                        <td>{{ $arquivo->tamanho_formatado }}</td>
                                        <td>{{ $arquivo->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('demandas.arquivos.download', $arquivo) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="bi bi-download"></i> Baixar
                                                </a>
                                                @if (!auth()->user()->isUsuario())
                                                    <form action="{{ route('demandas.arquivos.delete', $arquivo) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Tem certeza que deseja excluir este arquivo?')">
                                                            <i class="bi bi-trash"></i> Excluir
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Nenhum arquivo anexado a esta demanda.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
