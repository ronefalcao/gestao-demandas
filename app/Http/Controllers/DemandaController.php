<?php

namespace App\Http\Controllers;

use App\Models\Demanda;
use App\Models\Cliente;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;

class DemandaController extends Controller
{
    public function index(Request $request)
    {
        $query = Demanda::with(['cliente', 'solicitante', 'responsavel', 'status']);

        if ($request->has('status_id') && $request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('cliente_id') && $request->cliente_id) {
            $query->where('cliente_id', $request->cliente_id);
        }

        $demandas = $query->orderBy('data', 'desc')->paginate(15);
        $clientes = Cliente::all();
        $statuses = Status::all();

        return view('demandas.index', compact('demandas', 'clientes', 'statuses'));
    }

    public function create()
    {
        $clientes = Cliente::all();

        // Se o usuário for do tipo "usuario", mostrar apenas o status "Solicitada"
        if (auth()->user()->isUsuario()) {
            $statuses = Status::where('nome', 'Solicitada')->get();
        } else {
            $statuses = Status::all();
        }

        $users = User::all();
        return view('demandas.create', compact('clientes', 'statuses', 'users'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Se o usuário for do tipo "usuario", não exige responsavel_id e define status automaticamente
        if ($user->isUsuario()) {
            $validated = $request->validate([
                'data' => 'required|date',
                'cliente_id' => 'required|exists:clientes,id',
                'modulo' => 'required|string|max:255',
                'descricao' => 'required|string',
                'observacao' => 'nullable|string',
            ]);

            // Define automaticamente o solicitante como o próprio usuário logado
            $validated['solicitante_id'] = $user->id;
            // Define o status como "Solicitada" automaticamente
            $statusSolicitada = Status::where('nome', 'Solicitada')->first();
            $validated['status_id'] = $statusSolicitada->id;
            // Usuários do tipo "usuario" não podem definir responsável
            $validated['responsavel_id'] = null;
        } else {
            $validated = $request->validate([
                'data' => 'required|date',
                'cliente_id' => 'required|exists:clientes,id',
                'solicitante_id' => 'required|exists:users,id',
                'responsavel_id' => 'required|exists:users,id',
                'modulo' => 'required|string|max:255',
                'descricao' => 'required|string',
                'status_id' => 'required|exists:status,id',
                'observacao' => 'nullable|string',
            ]);
        }

        Demanda::create($validated);
        return redirect()->route('demandas.index')->with('success', 'Demanda criada com sucesso!');
    }

    public function show(Demanda $demanda)
    {
        $demanda->load(['cliente', 'solicitante', 'responsavel', 'status']);
        return view('demandas.show', compact('demanda'));
    }

    public function edit(Demanda $demanda)
    {
        $clientes = Cliente::all();
        $statuses = Status::all();
        $users = User::all();
        return view('demandas.edit', compact('demanda', 'clientes', 'statuses', 'users'));
    }

    public function update(Request $request, Demanda $demanda)
    {
        $validated = $request->validate([
            'data' => 'required|date',
            'cliente_id' => 'required|exists:clientes,id',
            'solicitante_id' => 'required|exists:users,id',
            'responsavel_id' => 'required|exists:users,id',
            'modulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'status_id' => 'required|exists:status,id',
            'observacao' => 'nullable|string',
        ]);

        $demanda->update($validated);
        return redirect()->route('demandas.index')->with('success', 'Demanda atualizada com sucesso!');
    }

    public function destroy(Demanda $demanda)
    {
        $demanda->delete();
        return redirect()->route('demandas.index')->with('success', 'Demanda excluída com sucesso!');
    }

    public function exportarPdf(Request $request)
    {
        $query = Demanda::with(['cliente', 'solicitante', 'responsavel', 'status']);

        if ($request->has('status_id') && $request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('cliente_id') && $request->cliente_id) {
            $query->where('cliente_id', $request->cliente_id);
        }

        $demandas = $query->orderBy('data', 'desc')->get();
        $statuses = Status::all();

        return view('demandas.pdf', compact('demandas', 'statuses'))->render();
    }
}
