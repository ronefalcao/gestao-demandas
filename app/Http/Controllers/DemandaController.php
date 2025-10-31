<?php

namespace App\Http\Controllers;

use App\Models\Demanda;
use App\Models\DemandaArquivo;
use App\Models\Cliente;
use App\Models\Projeto;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class DemandaController extends Controller
{
    public function index(Request $request)
    {
        $query = Demanda::with(['cliente', 'projeto', 'solicitante', 'responsavel', 'status']);

        // Filtrar demandas baseado no tipo de usuário e projetos associados
        $user = auth()->user();
        if ($user->isAdmin()) {
            // Admin vê todas as demandas
        } else {
            // Usuários não-admin só veem demandas dos projetos aos quais estão associados
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if ($projetosIds->isEmpty()) {
                // Se o usuário não tem projetos associados, não mostrar nenhuma demanda
                $query->whereRaw('1 = 0'); // Query que sempre retorna vazio
            } else {
                $query->whereIn('projeto_id', $projetosIds);

                // Usuário comum só vê suas próprias demandas (que ele criou)
                if ($user->isUsuario()) {
                    $query->where('solicitante_id', $user->id);
                }
            }
        }

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

        // Filtrar projetos baseado no usuário
        if ($user->isAdmin()) {
            $projetos = Projeto::where('ativo', true)->orderBy('nome')->get();
        } else {
            $projetos = $user->projetos()->where('ativo', true)->orderBy('nome')->get();
        }

        $statuses = Status::all();

        return view('demandas.index', compact('demandas', 'clientes', 'projetos', 'statuses'));
    }

    public function create()
    {
        $user = auth()->user();
        $clientes = Cliente::all();

        // Filtrar projetos baseado no usuário
        if ($user->isAdmin()) {
            $projetos = Projeto::where('ativo', true)->orderBy('nome')->get();
        } else {
            $projetos = $user->projetos()->where('ativo', true)->orderBy('nome')->get();
        }

        // Se o usuário for do tipo "usuario", mostrar apenas o status "Solicitada"
        if ($user->isUsuario()) {
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
                'projeto_id' => 'required|exists:projetos,id',
                'modulo' => 'required|string|max:255',
                'descricao' => 'required|string',
                'observacao' => 'nullable|string',
            ]);

            // Verificar se o usuário tem acesso ao projeto
            if (!$user->isAdmin()) {
                $projetosIds = $user->projetos()->pluck('projetos.id');
                if (!in_array($validated['projeto_id'], $projetosIds->toArray())) {
                    return back()->withErrors(['projeto_id' => 'Você não tem permissão para criar demandas neste projeto.'])->withInput();
                }
            }

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
                'projeto_id' => 'required|exists:projetos,id',
                'solicitante_id' => 'required|exists:users,id',
                'responsavel_id' => 'nullable|exists:users,id',
                'modulo' => 'required|string|max:255',
                'descricao' => 'required|string',
                'status_id' => 'required|exists:status,id',
                'observacao' => 'nullable|string',
            ]);

            // Verificar se o usuário tem acesso ao projeto (exceto admin)
            if (!$user->isAdmin()) {
                $projetosIds = $user->projetos()->pluck('projetos.id');
                if (!in_array($validated['projeto_id'], $projetosIds->toArray())) {
                    return back()->withErrors(['projeto_id' => 'Você não tem permissão para criar demandas neste projeto.'])->withInput();
                }
            }
        }

        $demanda = Demanda::create($validated);
        return redirect()->route('demandas.show', $demanda)->with('success', 'Demanda criada com sucesso!');
    }

    public function show(Demanda $demanda)
    {
        $user = auth()->user();

        // Verificar se o usuário tem acesso à demanda
        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if (!in_array($demanda->projeto_id, $projetosIds->toArray())) {
                abort(403, 'Você não tem permissão para visualizar esta demanda.');
            }

            // Usuário comum só pode ver suas próprias demandas (que ele criou)
            if ($user->isUsuario() && $demanda->solicitante_id !== $user->id) {
                abort(403, 'Você não tem permissão para visualizar esta demanda.');
            }
        }

        $demanda->load(['cliente', 'projeto', 'solicitante', 'responsavel', 'status', 'arquivos']);
        return view('demandas.show', compact('demanda'));
    }

    public function edit(Demanda $demanda)
    {
        $user = auth()->user();

        // Verificar se o usuário tem acesso à demanda
        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if (!in_array($demanda->projeto_id, $projetosIds->toArray())) {
                abort(403, 'Você não tem permissão para editar esta demanda.');
            }

            // Usuário comum só pode editar suas próprias demandas (que ele criou)
            if ($user->isUsuario() && $demanda->solicitante_id !== $user->id) {
                abort(403, 'Você não tem permissão para editar esta demanda.');
            }
        }

        // Usuário comum só pode editar demandas com status 'Concluído' para alterar para 'Homologada'
        if ($user->isUsuario()) {
            $statusConcluido = Status::where('nome', 'Concluído')->first();
            if (!$statusConcluido || $demanda->status_id !== $statusConcluido->id) {
                abort(403, 'Você só pode editar demandas com status "Concluído" para homologar.');
            }
        }

        $clientes = Cliente::all();

        // Filtrar projetos baseado no usuário
        if ($user->isAdmin()) {
            $projetos = Projeto::where('ativo', true)->orderBy('nome')->get();
        } else {
            $projetos = $user->projetos()->where('ativo', true)->orderBy('nome')->get();
        }

        // Se for usuário comum editando demanda concluída, mostrar apenas status 'Homologada'
        if ($user->isUsuario()) {
            $statuses = Status::where('nome', 'Homologada')->get();
        } else {
            $statuses = Status::all();
        }

        $users = User::all();
        $isUsuarioEditandoConcluida = $user->isUsuario();

        return view('demandas.edit', compact('demanda', 'clientes', 'projetos', 'statuses', 'users', 'isUsuarioEditandoConcluida'));
    }

    public function update(Request $request, Demanda $demanda)
    {
        $user = auth()->user();

        // Verificar se o usuário tem acesso à demanda
        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if (!in_array($demanda->projeto_id, $projetosIds->toArray())) {
                abort(403, 'Você não tem permissão para atualizar esta demanda.');
            }
        }

        // Usuário comum só pode atualizar status de demandas 'Concluído' para 'Homologada'
        if ($user->isUsuario()) {
            $statusConcluido = Status::where('nome', 'Concluído')->first();
            $statusHomologada = Status::where('nome', 'Homologada')->first();

            if (!$statusConcluido || $demanda->status_id !== $statusConcluido->id) {
                abort(403, 'Você só pode editar demandas com status "Concluído" para homologar.');
            }

            if (!$statusHomologada) {
                abort(500, 'Status "Homologada" não encontrado no sistema.');
            }

            // Validar apenas o status
            $validated = $request->validate([
                'status_id' => 'required|exists:status,id',
            ]);

            // Verificar se o status selecionado é 'Homologada'
            if ($validated['status_id'] != $statusHomologada->id) {
                return back()->withErrors(['status_id' => 'Você só pode alterar o status para "Homologada".'])->withInput();
            }

            // Atualizar apenas o status
            $demanda->update(['status_id' => $validated['status_id']]);
            return redirect()->route('demandas.index')->with('success', 'Demanda homologada com sucesso!');
        }

        // Validação completa para gestores e administradores
        $validated = $request->validate([
            'data' => 'required|date',
            'cliente_id' => 'required|exists:clientes,id',
            'projeto_id' => 'required|exists:projetos,id',
            'solicitante_id' => 'required|exists:users,id',
            'responsavel_id' => 'nullable|exists:users,id',
            'modulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'status_id' => 'required|exists:status,id',
            'observacao' => 'nullable|string',
        ]);

        // Verificar se o usuário tem acesso ao projeto (exceto admin)
        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if (!in_array($validated['projeto_id'], $projetosIds->toArray())) {
                return back()->withErrors(['projeto_id' => 'Você não tem permissão para atualizar demandas neste projeto.'])->withInput();
            }
        }

        $demanda->update($validated);
        return redirect()->route('demandas.index')->with('success', 'Demanda atualizada com sucesso!');
    }

    public function homologar(Demanda $demanda)
    {
        $user = auth()->user();

        // Verificar se o usuário tem acesso à demanda
        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if (!in_array($demanda->projeto_id, $projetosIds->toArray())) {
                abort(403, 'Você não tem permissão para homologar esta demanda.');
            }

            // Usuário comum só pode homologar suas próprias demandas (que ele criou)
            if ($user->isUsuario() && $demanda->solicitante_id !== $user->id) {
                abort(403, 'Você não tem permissão para homologar esta demanda.');
            }
        }

        // Verificar se é usuário comum e se a demanda está com status 'Concluído'
        if ($user->isUsuario()) {
            $statusConcluido = Status::where('nome', 'Concluído')->first();
            if (!$statusConcluido || $demanda->status_id !== $statusConcluido->id) {
                abort(403, 'Você só pode homologar demandas com status "Concluído".');
            }
        }

        // Buscar o status 'Homologada'
        $statusHomologada = Status::where('nome', 'Homologada')->first();
        if (!$statusHomologada) {
            return redirect()->back()->with('error', 'Status "Homologada" não encontrado no sistema.');
        }

        // Atualizar o status
        $demanda->update(['status_id' => $statusHomologada->id]);

        return redirect()->back()->with('success', 'Demanda homologada com sucesso!');
    }

    public function destroy(Demanda $demanda)
    {
        $user = auth()->user();

        // Usuário comum não pode excluir demandas
        if ($user->isUsuario()) {
            abort(403, 'Você não tem permissão para excluir demandas.');
        }

        // Verificar se o usuário tem acesso à demanda
        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if (!in_array($demanda->projeto_id, $projetosIds->toArray())) {
                abort(403, 'Você não tem permissão para excluir esta demanda.');
            }
        }

        $demanda->delete();
        return redirect()->route('demandas.index')->with('success', 'Demanda excluída com sucesso!');
    }

    public function exportarPdf(Request $request)
    {
        $user = auth()->user();
        $query = Demanda::with(['cliente', 'projeto', 'solicitante', 'responsavel', 'status']);

        // Filtrar demandas baseado no tipo de usuário e projetos associados
        if ($user->isAdmin()) {
            // Admin exporta todas as demandas
        } else {
            // Usuários não-admin só exportam demandas dos projetos aos quais estão associados
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if ($projetosIds->isEmpty()) {
                // Se o usuário não tem projetos associados, não exportar nenhuma demanda
                $query->whereRaw('1 = 0'); // Query que sempre retorna vazio
            } else {
                $query->whereIn('projeto_id', $projetosIds);

                // Usuário comum só exporta suas próprias demandas (que ele criou)
                if ($user->isUsuario()) {
                    $query->where('solicitante_id', $user->id);
                }
            }
        }

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

    public function uploadArquivo(Request $request, Demanda $demanda)
    {
        $user = auth()->user();

        // Verificar se o usuário tem acesso à demanda
        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if (!in_array($demanda->projeto_id, $projetosIds->toArray())) {
                abort(403, 'Você não tem permissão para fazer upload de arquivos nesta demanda.');
            }

            // Usuário comum só pode fazer upload em suas próprias demandas (que ele criou)
            if ($user->isUsuario() && $demanda->solicitante_id !== $user->id) {
                abort(403, 'Você não tem permissão para fazer upload de arquivos nesta demanda.');
            }
        }

        // Verificar se a demanda está em um status que não permite anexar arquivos
        $statusBloqueados = ['Concluído', 'Homologada', 'Publicada', 'Cancelada'];
        $demanda->load('status');
        if (in_array($demanda->status->nome, $statusBloqueados)) {
            return redirect()->back()->with('error', 'Não é possível anexar arquivos em demandas com status "' . $demanda->status->nome . '".');
        }

        $request->validate([
            'arquivo' => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240', // 10MB max
        ]);

        $arquivo = $request->file('arquivo');
        $nomeOriginal = $arquivo->getClientOriginalName();
        $nomeArquivo = uniqid() . '_' . time() . '.' . $arquivo->getClientOriginalExtension();
        $caminho = $arquivo->storeAs('demandas', $nomeArquivo, 'public');

        DemandaArquivo::create([
            'demanda_id' => $demanda->id,
            'nome_original' => $nomeOriginal,
            'nome_arquivo' => $nomeArquivo,
            'caminho' => $caminho,
            'tipo' => $arquivo->getClientOriginalExtension(),
            'tamanho' => $arquivo->getSize(),
        ]);

        return redirect()->route('demandas.show', $demanda)->with('success', 'Arquivo enviado com sucesso!');
    }

    public function downloadArquivo(DemandaArquivo $arquivo)
    {
        $user = auth()->user();
        $demanda = $arquivo->demanda;

        // Verificar se o usuário tem acesso à demanda
        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if (!in_array($demanda->projeto_id, $projetosIds->toArray())) {
                abort(403, 'Você não tem permissão para baixar este arquivo.');
            }

            // Usuário comum só pode baixar arquivos de suas próprias demandas (que ele criou)
            if ($user->isUsuario() && $demanda->solicitante_id !== $user->id) {
                abort(403, 'Você não tem permissão para baixar este arquivo.');
            }
        }

        $path = Storage::disk('public')->path($arquivo->caminho);
        return Response::download($path, $arquivo->nome_original);
    }

    public function deletarArquivo(DemandaArquivo $arquivo)
    {
        $user = auth()->user();
        $demanda = $arquivo->demanda;

        // Usuário comum não pode deletar arquivos
        if ($user->isUsuario()) {
            abort(403, 'Você não tem permissão para deletar arquivos.');
        }

        // Verificar se o usuário tem acesso à demanda
        if (!$user->isAdmin()) {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            if (!in_array($demanda->projeto_id, $projetosIds->toArray())) {
                abort(403, 'Você não tem permissão para deletar este arquivo.');
            }
        }

        // Verificar se a demanda está com status 'Solicitada' (único status que permite excluir arquivos)
        $demanda->load('status');
        if ($demanda->status->nome !== 'Solicitada') {
            return redirect()->back()->with('error', 'Não é possível excluir arquivos de demandas com status "' . $demanda->status->nome . '". Apenas demandas com status "Solicitada" permitem exclusão de arquivos.');
        }

        Storage::disk('public')->delete($arquivo->caminho);
        $arquivo->delete();

        return redirect()->route('demandas.show', $demanda)->with('success', 'Arquivo excluído com sucesso!');
    }
}
