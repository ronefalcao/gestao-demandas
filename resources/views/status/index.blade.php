@extends('layouts.app')

@section('title', 'Status')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-tag"></i> Status</h2>
        <a href="{{ route('status.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Novo Status
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="statusTable" class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Cor</th>
                            <th>Ordem</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statuses as $status)
                            <tr>
                                <td>{{ $status->id }}</td>
                                <td>{{ $status->nome }}</td>
                                <td>
                                    <span class="badge" style="background-color: {{ $status->cor ?? '#6c757d' }}">
                                        {{ $status->cor ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $status->ordem }}</td>
                                <td>
                                    <a href="{{ route('status.show', $status) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('status.edit', $status) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('status.destroy', $status) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Deseja realmente excluir este status?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Nenhum status cadastrado</td>
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
        $('#statusTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
            },
            pageLength: 15,
            order: [[3, 'asc']], // Ordenar por ordem (coluna 3)
            columnDefs: [
                { orderable: false, targets: 4 } // Desabilitar ordenação na coluna de ações
            ]
        });
    });
</script>
@endsection



