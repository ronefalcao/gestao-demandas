<?php

namespace App\Http\Controllers;

use App\Models\Demanda;
use App\Models\Projeto;
use App\Models\Cliente;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Base query para filtragem por tipo de usuário
        $baseQuery = Demanda::query();

        // Usuário comum só vê suas próprias demandas
        if ($user->isUsuario()) {
            $baseQuery->where('solicitante_id', $user->id);
        }
        // Admin e Gestor veem todas as demandas

        $statuses = Status::where('nome', '!=', 'Cancelada')->get();
        $totais = [];

        foreach ($statuses as $status) {
            $countQuery = clone $baseQuery;
            $totais[$status->id] = [
                'nome' => $status->nome,
                'cor' => $status->cor ?? '#6c757d',
                'total' => $countQuery->where('status_id', $status->id)->count(),
            ];
        }

        $totalDemandas = $baseQuery->count();

        // Totais gerais do sistema (só Admin/Gestor veem)
        $totalUsuarios = User::count();
        $totalProjetos = Projeto::where('ativo', true)->count();
        $totalClientes = Cliente::count();

        $recentesQuery = clone $baseQuery;
        $demandasRecentes = $recentesQuery->with(['cliente', 'projeto', 'solicitante', 'responsavel', 'status'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('totais', 'totalDemandas', 'totalUsuarios', 'totalProjetos', 'totalClientes', 'demandasRecentes'));
    }
}
