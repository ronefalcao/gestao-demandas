@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
    </div>

    <!-- Totais por Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Total de Demandas por Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($totais as $statusId => $dados)
                            <div class="col-md-3 mb-3">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle p-3"
                                                style="background-color: {{ $dados['cor'] }}; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                                <span class="text-white fw-bold">{{ $dados['total'] }}</span>
                                            </div>
                                            <div class="ms-3">
                                                <h6 class="mb-0">{{ $dados['nome'] }}</h6>
                                                <p class="text-muted mb-0">{{ $dados['total'] }}
                                                    {{ Str::plural('demanda', $dados['total']) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Totais Gerais -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <h5 class="text-muted"><i class="bi bi-people-fill"></i> Total de Usuários</h5>
                    <h1 class="display-3 fw-bold text-primary">{{ $totalUsuarios }}</h1>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <h5 class="text-muted"><i class="bi bi-folder"></i> Total de Projetos</h5>
                    <h1 class="display-3 fw-bold text-danger">{{ $totalProjetos }}</h1>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <h5 class="text-muted"><i class="bi bi-people"></i> Total de Clientes</h5>
                    <h1 class="display-3 fw-bold text-success">{{ $totalClientes }}</h1>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <h5 class="text-muted"><i class="bi bi-file-text"></i> Total de Demandas</h5>
                    <h1 class="display-3 fw-bold text-warning">{{ $totalDemandas }}</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Demandas Recentes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Demandas Recentes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($demandasRecentes as $demanda)
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Nenhuma demanda encontrada</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('demandas.index') }}" class="btn btn-primary">
                            Ver Todas as Demandas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
