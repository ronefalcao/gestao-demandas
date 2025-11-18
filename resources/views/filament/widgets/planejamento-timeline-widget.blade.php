<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
                Planejamento de Projetos e Itens
            </div>
        </x-slot>

        <div class="space-y-6">
            @php
                $projetos = $this->getViewData()['projetos'] ?? [];
            @endphp

            @if (count($projetos) > 0)
                <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                    <table class="w-full border-collapse bg-white">
                        <thead>
                            <tr class="bg-gray-50 border-b-2 border-gray-300">
                                <th
                                    class="px-4 py-3 text-left text-sm font-bold text-gray-800 border-r border-gray-200">
                                    Projeto
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-bold text-gray-800">
                                    Itens
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($projetos as $projeto)
                                <tr class="bg-blue-50 hover:bg-blue-100 border-b-2 border-blue-200">
                                    <td class="px-4 py-3 font-bold text-blue-900 border-r border-gray-200">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z">
                                                </path>
                                            </svg>
                                            <span>{{ $projeto->nome }}</span>
                                            <span
                                                class="text-xs font-normal text-blue-700">({{ $projeto->itens->count() ?? 0 }}
                                                itens)</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="space-y-2">
                                            @foreach ($projeto->itens ?? [] as $item)
                                                <div
                                                    class="flex items-center gap-2 flex-wrap p-2 bg-white rounded border border-gray-200 hover:bg-gray-50">
                                                    @if ($item->numero)
                                                        <span
                                                            class="text-xs font-bold text-gray-600">{{ $item->numero }}</span>
                                                    @endif
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border
                                                        {{ $item->status === 'fechado' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-yellow-100 text-yellow-800 border-yellow-300' }}">
                                                        {{ ucfirst($item->status ?? 'aberto') }}
                                                    </span>
                                                    <span
                                                        class="font-medium text-gray-900">{{ $item->titulo }}</span>
                                                    @if ($item->feature)
                                                        <span
                                                            class="text-xs text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">
                                                            {{ $item->feature->numero ?? '' }} - {{ $item->feature->titulo ?? '' }}
                                                        </span>
                                                    @endif
                                                    @if ($item->sprint)
                                                        <span
                                                            class="text-xs text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">
                                                            Sprint {{ $item->sprint->numero }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if (($projeto->itens ?? collect())->isEmpty())
                                                <div class="text-center py-4 text-sm text-gray-500">
                                                    Nenhum item cadastrado
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum projeto encontrado</h3>
                    <p class="mt-1 text-sm text-gray-500">Não há projetos cadastrados para exibir.</p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
