<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function index()
    {
        $statuses = Status::orderBy('ordem')->get();
        return view('status.index', compact('statuses'));
    }

    public function create()
    {
        return view('status.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cor' => 'nullable|string|max:7',
            'ordem' => 'nullable|integer',
        ]);

        Status::create($validated);
        return redirect()->route('status.index')->with('success', 'Status criado com sucesso!');
    }

    public function show(Status $status)
    {
        return view('status.show', compact('status'));
    }

    public function edit(Status $status)
    {
        return view('status.edit', compact('status'));
    }

    public function update(Request $request, Status $status)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cor' => 'nullable|string|max:7',
            'ordem' => 'nullable|integer',
        ]);

        $status->update($validated);
        return redirect()->route('status.index')->with('success', 'Status atualizado com sucesso!');
    }

    public function destroy(Status $status)
    {
        $status->delete();
        return redirect()->route('status.index')->with('success', 'Status excluído com sucesso!');
    }
}



