@extends('layouts.app')

@section('title', 'Projetos')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-folder"></i> Projetos</h2>
        <a href="{{ route('projetos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Novo Projeto
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projetos as $projeto)
                            <tr>
                                <td>{{ $projeto->id }}</td>
                                <td>{{ $projeto->nome }}</td>
                                <td>{{ Str::limit($projeto->descricao, 50) ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $projeto->ativo ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $projeto->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>{{ $projeto->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('projetos.show', $projeto) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('projetos.edit', $projeto) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('projetos.destroy', $projeto) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Deseja realmente excluir este projeto?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Nenhum projeto cadastrado</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $projetos->links() }}
            </div>
        </div>
    </div>
@endsection

