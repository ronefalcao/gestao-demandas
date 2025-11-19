<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
                Planejamento de Projetos
            </div>
        </x-slot>

        @php
            $data = $this->getViewData();
            $sprints = $data['sprints'] ?? [];
            $estrutura = $data['estrutura'] ?? [];
        @endphp

        @if (count($estrutura) > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-collapse  text-sm">
                    <thead>
                        <tr class="bg-slate-200 border-b-2 border-slate-400 sticky top-0 z-40">
                            <th
                                class="px-3 py-2 text-left font-bold text-slate-800 border-r border-slate-400 min-w-[200px]">
                                Item
                            </th>
                            <th
                                class="px-3 py-2 text-left font-bold text-slate-800 border-r border-slate-400 min-w-[100px]">
                                <!-- Espaço reservado -->
                            </th>
                            @foreach ($sprints as $sprint)
                                <th
                                    class="px-2 py-2 text-center font-bold text-slate-800 border-r border-slate-400 min-w-[120px] bg-slate-100">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm">{{ $sprint->numero }}</span>
                                        @if ($sprint->inicio)
                                            <span
                                                class="text-xs font-normal text-gray-600">{{ \Carbon\Carbon::parse($sprint->inicio)->format('d/m/Y') }}</span>
                                        @endif
                                        @if ($sprint->termino)
                                            <span
                                                class="text-xs font-normal text-gray-600">{{ \Carbon\Carbon::parse($sprint->termino)->format('d/m/Y') }}</span>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($estrutura as $projetoData)
                            @php
                                $projeto = $projetoData['projeto'];
                                $features = $projetoData['features'] ?? [];
                                $totalColunas = 2 + count($sprints); // Item + Espaço + Sprints
                            @endphp

                            {{-- Linha do Projeto ocupando toda a largura --}}
                            <tr class="bg-blue-500 dark:bg-blue-600 border-b-2 border-blue-600 dark:border-blue-700">
                                <td class="px-4 py-3 font-bold  dark:text-white" colspan="{{ $totalColunas }}">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-700 dark:text-white" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z">
                                            </path>
                                        </svg>
                                        <span
                                            class="text-lg font-bold !text-blue-700 dark:text-white">{{ $projeto->nome }}</span>
                                    </div>
                                </td>
                            </tr>

                            @foreach ($features as $featureData)
                                @php
                                    $feature = $featureData['feature'];
                                    $itens = $featureData['itens'] ?? [];
                                @endphp

                                {{-- Linha da Feature ocupando toda a largura --}}
                                @php
                                    $statusCor = $feature->status->cor ?? 'gray';
                                    $bgClasses = match ($statusCor) {
                                        'danger' => 'bg-red-100 border-red-300',
                                        'warning' => 'bg-yellow-100 border-yellow-300',
                                        'success' => 'bg-green-100 border-green-300',
                                        'info' => 'bg-blue-100 border-blue-300',
                                        'primary' => 'bg-indigo-100 border-indigo-300',
                                        'secondary' => 'bg-gray-100 border-gray-300',
                                        default => 'bg-gray-100 border-gray-300',
                                    };
                                    $textClasses = match ($statusCor) {
                                        'danger' => 'text-red-900',
                                        'warning' => 'text-yellow-900',
                                        'success' => 'text-green-900',
                                        'info' => 'text-blue-900',
                                        'primary' => 'text-indigo-900',
                                        'secondary' => 'text-gray-900',
                                        default => 'text-gray-900',
                                    };
                                    $badgeClasses = match ($statusCor) {
                                        'danger' => 'text-red-700 bg-red-200',
                                        'warning' => 'text-yellow-700 bg-yellow-200',
                                        'success' => 'text-green-700 bg-green-200',
                                        'info' => 'text-blue-700 bg-blue-200',
                                        'primary' => 'text-indigo-700 bg-indigo-200',
                                        'secondary' => 'text-gray-700 bg-gray-200',
                                        default => 'text-gray-700 bg-gray-200',
                                    };
                                @endphp
                                <tr class="{{ $bgClasses }} border-b">
                                    <td class="px-4 py-2 font-semibold {{ $textClasses }}"
                                        colspan="{{ $totalColunas }}">
                                        <div class="flex items-center gap-2">
                                            @if ($feature->numero)
                                                <span
                                                    class="text-sm font-bold {{ $textClasses }}">{{ $feature->numero }}</span>
                                            @endif
                                            <span
                                                class="font-medium {{ $textClasses }}">{{ $feature->titulo }}</span>
                                            @php
                                                $moduloNome = null;
                                                try {
                                                    // Tentar obter o nome do módulo via relacionamento
                                                    if ($feature->modulo_id) {
                                                        $modulo = $feature->modulo;
                                                        if ($modulo && is_object($modulo)) {
                                                            $moduloNome = $modulo->nome;
                                                        }
                                                    }
                                                    // Fallback: se não conseguiu pelo relacionamento, tentar o atributo direto
                                                    if (!$moduloNome && isset($feature->getAttributes()['modulo'])) {
                                                        $moduloAttr = $feature->getAttributes()['modulo'];
                                                        if (!empty($moduloAttr) && is_string($moduloAttr)) {
                                                            $moduloNome = $moduloAttr;
                                                        }
                                                    }
                                                } catch (\Exception $e) {
                                                    // Ignorar erros e não exibir módulo
                                                    $moduloNome = null;
                                                }
                                            @endphp
                                            @if ($moduloNome)
                                                <span
                                                    class="text-xs {{ $badgeClasses }} px-2 py-0.5 rounded font-semibold">
                                                    {{ $moduloNome }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                @foreach ($itens as $item)
                                    <tr class="border-b border-gray-300 hover:bg-blue-50 transition-colors">
                                        {{-- Coluna Item --}}
                                        <td class="px-3 py-2 border-r border-gray-200">
                                            <div class="flex items-center gap-2 flex-wrap pl-8">
                                                @if ($item->numero)
                                                    <span
                                                        class="text-xs font-bold text-gray-600 min-w-[40px] px-2">{{ $item->numero }}</span>
                                                @endif
                                                <span class="font-medium text-gray-900">{{ $item->titulo }}</span>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                                    {{ $item->status === 'fechado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Coluna vazia para manter alinhamento --}}
                                        <td class="px-3 py-2 border-r border-gray-200"></td>

                                        {{-- Colunas de Sprint --}}
                                        @foreach ($sprints as $sprint)
                                            <td class="px-2 py-2 text-center border-r border-gray-300">
                                                @if ($item->sprint_id == $sprint->id)
                                                    <div
                                                        class="w-full h-8  rounded border border-gray-200 bg-primary-500">
                                                    </div>
                                                @else
                                                    <div>
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum projeto encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">Não há projetos cadastrados para exibir.</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
