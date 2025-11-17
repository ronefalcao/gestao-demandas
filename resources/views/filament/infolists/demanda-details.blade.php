<div class="space-y-6">
    {{-- Informações Básicas --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Informações Básicas</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Número</p>
                <p class="mt-1 text-sm text-gray-900">{{ $record->numero }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Data</p>
                <p class="mt-1 text-sm text-gray-900">{{ $record->data->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Cliente</p>
                <p class="mt-1 text-sm text-gray-900">{{ $record->cliente->nome ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Projeto</p>
                <p class="mt-1 text-sm text-gray-900">{{ $record->projeto->nome ?? '-' }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-sm font-medium text-gray-500">Módulo</p>
                <p class="mt-1 text-sm text-gray-900">{{ $record->modulo ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Detalhes --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Detalhes</h3>
        <div class="space-y-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Descrição</p>
                <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $record->descricao ?? '-' }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Solicitante</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->solicitante->nome ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Responsável</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $record->responsavel->nome ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Status</p>
                    <p class="mt-1">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                            style="background-color: {{ $record->status->cor ?? '#6b7280' }}20; color: {{ $record->status->cor ?? '#6b7280' }};">
                            {{ $record->status->nome ?? '-' }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Prioridade</p>
                    <p class="mt-1">
                        @php
                            $prioridadeCor = match ($record->prioridade ?? 'media') {
                                'baixa' => ['bg' => '#10b981', 'text' => '#065f46'], // verde
                                'media' => ['bg' => '#f59e0b', 'text' => '#92400e'], // amarela
                                'alta' => ['bg' => '#ef4444', 'text' => '#991b1b'], // vermelha
                                default => ['bg' => '#6b7280', 'text' => '#374151'],
                            };
                            $prioridadeLabel = match ($record->prioridade ?? 'media') {
                                'baixa' => 'Baixa',
                                'media' => 'Média',
                                'alta' => 'Alta',
                                default => ucfirst($record->prioridade ?? 'Média'),
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                            style="background-color: {{ $prioridadeCor['bg'] }}20; color: {{ $prioridadeCor['text'] }};">
                            {{ $prioridadeLabel }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Observações --}}
    @if ($record->observacao)
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Observações</h3>
            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $record->observacao }}</p>
        </div>
    @endif

    {{-- Informações de Controle --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Informações de Controle</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Criado em</p>
                <p class="mt-1 text-sm text-gray-900">{{ $record->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Atualizado em</p>
                <p class="mt-1 text-sm text-gray-900">{{ $record->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>
