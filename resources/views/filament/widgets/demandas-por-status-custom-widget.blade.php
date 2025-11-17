<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Demandas por Status
        </x-slot>

        <style>
            .status-grid {
                display: grid;
                grid-template-columns: repeat(1, minmax(0, 1fr));
                gap: 1rem;
            }
            @media (min-width: 640px) {
                .status-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                }
            }
            @media (min-width: 1024px) {
                .status-grid {
                    grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
                }
            }
        </style>
        <div class="status-grid">
            @foreach($this->totais as $dados)
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ $dados['nome'] }}
                        </span>
                        <div class="h-8 w-8 rounded-lg text-white shadow-md flex items-center justify-center text-sm font-bold" style="background: {{ $dados['cor'] }};">
                            {{ mb_substr($dados['nome'], 0, 1) }}
                        </div>
                    </div>
                    <div class="mt-2">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $dados['total'] }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">demandas</p>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

