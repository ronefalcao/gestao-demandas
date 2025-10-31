<?php

namespace App\Http\Controllers;

use App\Models\Demanda;
use App\Models\Cliente;
use App\Models\Projeto;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;

class DemandaController extends Controller
{
    public function index(Request $request)
    {
        $query = Demanda::with(['cliente', 'projeto', 'solicitante', 'responsavel', 'status']);

        // Filtrar demandas baseado no tipo de usuário
        $user = auth()->user();
        if ($user->isUsuario()) {
            // Usuário comum só vê suas próprias demandas
            $query->where('solicitante_id', $user->id);
        }
        // Admin e Gestor veem todas as demandas

        if ($request->has('status_id') && $request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('cliente_id') && $request->cliente_id) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->has('projeto_id') && $request->projeto_id) {
            $query->where('projeto_id', $request->projeto_id);
        }

        $demandas = $query->orderBy('data', 'desc')->paginate(15);
        $clientes = Cliente::all();
        $projetos = Projeto::where('ativo', true)->orderBy('nome')->get();
        $statuses = Status::all();

        return view('demandas.index', compact('demandas', 'clientes', 'projetos', 'statuses'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        $projetos = Projeto::where('ativo', true)->orderBy('nome')->get();

        // Se o usuário for do tipo "usuario", mostrar apenas o status "Solicitada"
        if (auth()->user()->isUsuario()) {
            $statuses = Status::where('nome', 'Solicitada')->get();
        } else {
            $statuses = Status::all();
        }

        $users = User::all();
        return view('demandas.create', compact('clientes', 'projetos', 'statuses', 'users'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Se o usuário for do tipo "usuario", não exige responsavel_id e define status automaticamente
        if ($user->isUsuario()) {
            $validated = $request->validate([
                'data' => 'required|date',
                'cliente_id' => 'required|exists:clientes,id',
                'projeto_id' => 'nullable|exists:projetos,id',
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
                'projeto_id' => 'nullable|exists:projetos,id',
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
        $user = auth()->user();
        
        // Usuário comum só pode ver suas próprias demandas
        if ($user->isUsuario() && $demanda->solicitante_id !== $user->id) {
            abort(403, 'Você não tem permissão para visualizar esta demanda.');
        }

        $demanda->load(['cliente', 'projeto', 'solicitante', 'responsavel', 'status']);
        return view('demandas.show', compact('demanda'));
    }

    public function edit(Demanda $demanda)
    {
        $user = auth()->user();
        
        // Usuário comum não pode editar demandas
        if ($user->isUsuario()) {
            abort(403, 'Você não tem permissão para editar demandas.');
        }

        $clientes = Cliente::all();
        $projetos = Projeto::where('ativo', true)->orderBy('nome')->get();
        $statuses = Status::all();
        $users = User::all();
        return view('demandas.edit', compact('demanda', 'clientes', 'projetos', 'statuses', 'users'));
    }

    public function update(Request $request, Demanda $demanda)
    {
        $user = auth()->user();
        
        // Usuário comum não pode atualizar demandas
        if ($user->isUsuario()) {
            abort(403, 'Você não tem permissão para atualizar demandas.');
        }

        $validated = $request->validate([
            'data' => 'required|date',
            'cliente_id' => 'required|exists:clientes,id',
            'projeto_id' => 'nullable|exists:projetos,id',
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
        $user = auth()->user();
        
        // Usuário comum não pode excluir demandas
        if ($user->isUsuario()) {
            abort(403, 'Você não tem permissão para excluir demandas.');
        }

        $demanda->delete();
        return redirect()->route('demandas.index')->with('success', 'Demanda excluída com sucesso!');
    }

    public function exportarPdf(Request $request)
    {
        $user = auth()->user();
        $query = Demanda::with(['cliente', 'projeto', 'solicitante', 'responsavel', 'status']);

        // Filtrar demandas baseado no tipo de usuário
        if ($user->isUsuario()) {
            // Usuário comum só exporta suas próprias demandas
            $query->where('solicitante_id', $user->id);
        }
        // Admin e Gestor exportam todas as demandas

        if ($request->has('status_id') && $request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->has('cliente_id') && $request->cliente_id) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->has('projeto_id') && $request->projeto_id) {
            $query->where('projeto_id', $request->projeto_id);
        }

        $demandas = $query->orderBy('data', 'desc')->get();
        $statuses = Status::all();

        return view('demandas.pdf', compact('demandas', 'statuses'))->render();
    }
}
