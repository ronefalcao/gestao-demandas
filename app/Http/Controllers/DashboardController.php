<?php

namespace App\Http\Controllers;

use App\Models\Demanda;
use App\Models\Status;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $statuses = Status::all();
        $totais = [];

        foreach ($statuses as $status) {
            $totais[$status->id] = [
                'nome' => $status->nome,
                'cor' => $status->cor ?? '#6c757d',
                'total' => Demanda::where('status_id', $status->id)->count(),
            ];
        }

        $totalDemandas = Demanda::count();
        $demandasRecentes = Demanda::with(['cliente', 'solicitante', 'responsavel', 'status'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('totais', 'totalDemandas', 'demandasRecentes'));
    }
}
