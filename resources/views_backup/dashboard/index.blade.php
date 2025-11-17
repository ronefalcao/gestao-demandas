@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-10">
        {{-- Totais por Status --}}
        <section class="space-y-4">
            <header class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Status</p>
                    <h2 class="text-2xl font-semibold text-slate-900">Demandas por Status</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ collect($totais)->sum('total') }} demandas monitoradas
                </span>
            </header>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($totais as $dados)
                    <article class="rounded-2xl border border-slate-100 bg-white/80 p-5 shadow-sm ring-1 ring-black/5 backdrop-blur">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                                {{ $dados['nome'] }}
                            </span>
                            <span class="rounded-full px-3 py-1 text-xs font-medium"
                                style="color: {{ $dados['cor'] }}; background-color: {{ $dados['cor'] }}22;">
                                {{ $dados['total'] }} {{ Str::plural('registro', $dados['total']) }}
                            </span>
                        </div>
                        <div class="mt-6 flex items-end justify-between">
                            <p class="text-4xl font-semibold text-slate-900">{{ $dados['total'] }}</p>
                            <div class="h-12 w-12 rounded-xl text-white shadow-lg"
                                style="background: linear-gradient(135deg, {{ $dados['cor'] }} 0%, {{ $dados['cor'] }}cc 100%);">
                                <div class="flex h-full items-center justify-center text-lg font-bold">
                                    {{ mb_substr($dados['nome'], 0, 1) }}
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Resumo Geral --}}
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-3xl bg-gradient-to-br from-[rgb(var(--primary-600))] to-[rgb(var(--primary-800))] p-6 text-white shadow-xl">
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-white/70">Usuários</p>
                <h3 class="mt-4 text-4xl font-semibold">{{ $totalUsuarios }}</h3>
                <p class="mt-2 text-sm text-white/80">Contas ativas no sistema</p>
            </article>

            <article class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-slate-400">Projetos</p>
                <h3 class="mt-4 text-4xl font-semibold text-slate-900">{{ $totalProjetos }}</h3>
                <p class="mt-2 text-sm text-slate-500">Projetos cadastrados</p>
            </article>

            <article class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-slate-400">Clientes</p>
                <h3 class="mt-4 text-4xl font-semibold text-slate-900">{{ $totalClientes }}</h3>
                <p class="mt-2 text-sm text-slate-500">Organizações atendidas</p>
            </article>

            <article class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium uppercase tracking-[0.25em] text-slate-400">Demandas</p>
                <h3 class="mt-4 text-4xl font-semibold text-slate-900">{{ $totalDemandas }}</h3>
                <p class="mt-2 text-sm text-slate-500">Itens no pipeline</p>
            </article>
        </section>

        {{-- Demandas Recentes --}}
        <section class="space-y-4 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Monitoramento</p>
                    <h2 class="text-2xl font-semibold text-slate-900">Demandas Recentes</h2>
                </div>
                <a href="{{ route('demandas.index') }}"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-[rgb(var(--primary-400))] hover:text-[rgb(var(--primary-600))]">
                    Ver todas
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </header>

            <div class="overflow-hidden rounded-2xl border border-slate-100 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-widest text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Número</th>
                                <th class="px-4 py-3 text-left">Data</th>
                                <th class="px-4 py-3 text-left">Cliente</th>
                                <th class="px-4 py-3 text-left">Projeto</th>
                                <th class="px-4 py-3 text-left">Módulo</th>
                                <th class="px-4 py-3 text-left">Descrição</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Solicitante</th>
                                <th class="px-4 py-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($demandasRecentes as $demanda)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $demanda->numero }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $demanda->data->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $demanda->cliente->nome }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $demanda->projeto->nome ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $demanda->modulo }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ Str::limit($demanda->descricao, 60) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                            style="color: {{ $demanda->status->cor ?? '#0f172a' }}; background-color: {{ ($demanda->status->cor ?? '#0f172a') . '1a' }};">
                                            {{ $demanda->status->nome }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $demanda->solicitante->nome }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('demandas.show', $demanda) }}"
                                            class="inline-flex items-center gap-1 rounded-full bg-[rgb(var(--primary-500))] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[rgb(var(--primary-600))]">
                                            Detalhes
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-slate-500">
                                        Nenhuma demanda encontrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection
