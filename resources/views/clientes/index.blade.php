@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people"></i> Clientes</h2>
        <a href="{{ route('clientes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Novo Cliente
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
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientes as $cliente)
                            <tr>
                                <td>{{ $cliente->id }}</td>
                                <td>{{ $cliente->nome }}</td>
                                <td>{{ $cliente->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('clientes.destroy', $cliente) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Deseja realmente excluir este cliente?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Nenhum cliente cadastrado</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $clientes->links() }}
            </div>
        </div>
    </div>
@endsection
