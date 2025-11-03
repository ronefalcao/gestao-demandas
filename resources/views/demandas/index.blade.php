@extends('layouts.app')

@section('title', 'Demandas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-text"></i> Demandas</h2>
        <div>
            <a href="{{ route('demandas.exportar', request()->all()) }}" target="_blank" class="btn btn-success me-2">
                <i class="bi bi-file-pdf"></i> Exportar PDF
            </a>
            <a href="{{ route('demandas.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nova Demanda
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('demandas.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="cliente_id" class="form-label">Cliente</label>
                        <select class="form-select" id="cliente_id" name="cliente_id">
                            <option value="">Todos os clientes</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}"
                                    {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="projeto_id" class="form-label">Projeto</label>
                        <select class="form-select" id="projeto_id" name="projeto_id">
                            <option value="">Todos os projetos</option>
                            @foreach ($projetos as $projeto)
                                <option value="{{ $projeto->id }}"
                                    {{ request('projeto_id') == $projeto->id ? 'selected' : '' }}>
                                    {{ $projeto->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="status_id" class="form-label">Status</label>
                        <select class="form-select" id="status_id" name="status_id">
                            <option value="">Todos os status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}"
                                    {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                    {{ $status->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="demandasTable" class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Projeto</th>
                            <th>Módulo</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th>Solicitante</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($demandas as $demanda)
                            <tr>
                                <td><strong>{{ $demanda->numero }}</strong></td>
                                <td>{{ $demanda->data->format('d/m/Y') }}</td>
                                <td>{{ $demanda->cliente->nome }}</td>
                                <td>{{ $demanda->projeto ? $demanda->projeto->nome : '-' }}</td>
                                <td>{{ $demanda->modulo }}</td>
                                <td>{{ Str::limit($demanda->descricao, 50) }}</td>
                                <td>
                                    <span class="badge"
                                        style="background-color: {{ $demanda->status->cor ?? '#6c757d' }}">
                                        {{ $demanda->status->nome }}
                                    </span>
                                </td>
                                <td>{{ $demanda->solicitante->nome }}</td>
                                <td>
                                    <a href="{{ route('demandas.show', $demanda) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @php
                                        $user = auth()->user();
                                        $statusConcluido = $demanda->status->nome === 'Concluído';
                                        $podeEditar = !$user->isUsuario() || ($user->isUsuario() && $statusConcluido);
                                    @endphp
                                    @if ($podeEditar)
                                        @if ($user->isUsuario() && $statusConcluido)
                                            <form action="{{ route('demandas.homologar', $demanda) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success"
                                                    title="Homologar demanda"
                                                    onclick="return confirm('Deseja realmente homologar esta demanda?')">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('demandas.edit', $demanda) }}" class="btn btn-sm btn-warning"
                                                title="Editar demanda">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                    @endif
                                    @if (!auth()->user()->isUsuario())
                                        <form action="{{ route('demandas.destroy', $demanda) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Deseja realmente excluir esta demanda?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Nenhuma demanda encontrada</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#demandasTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
            },
            pageLength: 15,
            order: [[1, 'desc']], // Ordenar por data (coluna 1)
            columnDefs: [
                { orderable: false, targets: 8 } // Desabilitar ordenação na coluna de ações
            ]
        });
    });
</script>
@endsection
