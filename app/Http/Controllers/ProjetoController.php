<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use Illuminate\Http\Request;

class ProjetoController extends Controller
{
    public function index()
    {
        $projetos = Projeto::orderBy('nome')->paginate(15);
        return view('projetos.index', compact('projetos'));
    }

    public function create()
    {
        return view('projetos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
        ]);

        Projeto::create($validated);
        return redirect()->route('projetos.index')->with('success', 'Projeto criado com sucesso!');
    }

    public function show(Projeto $projeto)
    {
        return view('projetos.show', compact('projeto'));
    }

    public function edit(Projeto $projeto)
    {
        return view('projetos.edit', compact('projeto'));
    }

    public function update(Request $request, Projeto $projeto)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
        ]);

        $projeto->update($validated);
        return redirect()->route('projetos.index')->with('success', 'Projeto atualizado com sucesso!');
    }

    public function destroy(Projeto $projeto)
    {
        $projeto->delete();
        return redirect()->route('projetos.index')->with('success', 'Projeto excluído com sucesso!');
    }
}
