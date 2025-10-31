<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Demandas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #667eea;
            color: white;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-size: 12px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Sistema de Gestão de Demandas</h1>
        <h2>Relatório de Demandas</h2>
        <p>{{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Cliente</th>
                <th>Projeto</th>
                <th>Módulo</th>
                <th>Descrição</th>
                <th>Status</th>
                <th>Solicitante</th>
                <th>Responsável</th>
            </tr>
        </thead>
        <tbody>
            @forelse($demandas as $demanda)
                <tr>
                    <td>{{ $demanda->data->format('d/m/Y') }}</td>
                    <td>{{ $demanda->cliente->nome }}</td>
                    <td>{{ $demanda->projeto ? $demanda->projeto->nome : '-' }}</td>
                    <td>{{ $demanda->modulo }}</td>
                    <td>{{ $demanda->descricao }}</td>
                    <td>{{ $demanda->status->nome }}</td>
                    <td>{{ $demanda->solicitante->nome }}</td>
                    <td>{{ $demanda->responsavel ? $demanda->responsavel->nome : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Nenhuma demanda encontrada</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total de Demandas: {{ $demandas->count() }}</p>
    </div>
</body>

</html>
