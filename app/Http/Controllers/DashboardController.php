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

        // Base query para filtragem por tipo de usuário e projetos associados
        $baseQuery = Demanda::query();

        if ($user->isAdmin()) {
            // Admin vê todas as demandas
        } else {
            // Usuários não-admin só veem demandas dos projetos aos quais estão associados
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if ($projetosIds->isEmpty()) {
                // Se o usuário não tem projetos associados, não mostrar nenhuma demanda
                $baseQuery->whereRaw('1 = 0'); // Query que sempre retorna vazio
            } else {
                $baseQuery->whereIn('projeto_id', $projetosIds);

                // Usuário comum só vê suas próprias demandas (que ele criou)
                // Analista vê todas as demandas dos projetos que tem acesso
                if ($user->isUsuario()) {
                    $baseQuery->where('solicitante_id', $user->id);
                }
            }
        }

        // Excluir rascunhos de outros usuários (apenas o criador vê seus rascunhos)
        $baseQuery->excludeRascunhosFromOthers($user->id);

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

        // Totais gerais do sistema
        if ($user->isAdmin()) {
            $totalUsuarios = User::count();
            $totalProjetos = Projeto::where('ativo', true)->count();
            $totalClientes = Cliente::count();
        } else {
            $totalUsuarios = 0;
            $totalProjetos = $user->projetos()->where('ativo', true)->count();
            $totalClientes = 0;
        }

        $recentesQuery = clone $baseQuery;
        $demandasRecentes = $recentesQuery->with(['cliente', 'projeto', 'solicitante', 'responsavel', 'status'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('totais', 'totalDemandas', 'totalUsuarios', 'totalProjetos', 'totalClientes', 'demandasRecentes'));
    }
}
