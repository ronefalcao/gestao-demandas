<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Projeto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('nome')->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telefone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'tipo' => 'required|in:administrador,gestor,usuario',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);
        return redirect()->route('users.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function show(User $user)
    {
        $user->load('projetos');
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $projetos = Projeto::orderBy('nome')->get();
        $user->load('projetos');
        return view('users.edit', compact('user', 'projetos'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telefone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'tipo' => 'required|in:administrador,gestor,usuario',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        // Sincronizar projetos (apenas se não for administrador, pois admin vê tudo)
        // Administradores podem gerenciar projetos de outros usuários
        if (!$user->isAdmin()) {
            if ($request->has('projetos') && is_array($request->projetos)) {
                $user->projetos()->sync($request->projetos);
            } else {
                // Se não tiver projetos selecionados, remover todos
                $user->projetos()->detach();
            }
        }

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(User $user)
    {
        // Evitar que o administrador exclua a si mesmo
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Você não pode excluir seu próprio usuário!');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuário excluído com sucesso!');
    }
}