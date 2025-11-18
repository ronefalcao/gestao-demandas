@php
    $record = $getRecord();
    $livewire = $getLivewire();
    $itemId = $livewire->itemId ?? null;
    $formData = $livewire->itemFormData ?? [];
    $sprints = \App\Models\Sprint::orderBy('numero', 'desc')->get();
@endphp

<div>
    <form wire:submit.prevent="saveItem" class="space-y-4">
        {{-- Linha 1: Número (se editando) + Título + Sprint + Status --}}
        <div class="grid grid-cols-4 gap-4">
            @if ($itemId)
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">Número</label>
                    <input type="text" value="{{ $formData['numero'] ?? '' }}" disabled
                        class="w-full py-1 px-1.5 text-xs rounded border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">
                        Título <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model.defer="itemFormData.titulo" value="{{ $formData['titulo'] ?? '' }}"
                        class="w-full py-1 px-1.5 text-xs rounded border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-white"
                        required>
                    @error('itemFormData.titulo')
                        <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @else
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">
                        Título <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model.defer="itemFormData.titulo" value="{{ $formData['titulo'] ?? '' }}"
                        class="w-full py-1 px-1.5 text-xs rounded border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-white"
                        required>
                    @error('itemFormData.titulo')
                        <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">Sprint</label>
                <select wire:model.defer="itemFormData.sprint_id"
                    class="w-full py-1 px-1.5 text-xs rounded border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-white">
                    <option value="">Selecione</option>
                    @foreach ($sprints as $sprint)
                        <option value="{{ $sprint->id }}"
                            {{ ($formData['sprint_id'] ?? null) == $sprint->id ? 'selected' : '' }}>
                            {{ $sprint->numero }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">
                    Status <span class="text-red-500">*</span>
                </label>
                <select wire:model.defer="itemFormData.status"
                    class="w-full py-1 px-1.5 text-xs rounded border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-white"
                    required>
                    <option value="aberto" {{ ($formData['status'] ?? 'aberto') == 'aberto' ? 'selected' : '' }}>Aberto
                    </option>
                    <option value="fechado" {{ ($formData['status'] ?? '') == 'fechado' ? 'selected' : '' }}>Fechado
                    </option>
                </select>
                @error('itemFormData.status')
                    <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Linha 2: Figma + US + Descrição (2 colunas) --}}
        <div class="grid grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">Figma</label>
                <input type="url" wire:model.defer="itemFormData.figma" value="{{ $formData['figma'] ?? '' }}"
                    class="w-full py-1 px-1.5 text-xs rounded border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-white"
                    placeholder="https://figma.com/...">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">US</label>
                <input type="text" wire:model.defer="itemFormData.us" value="{{ $formData['us'] ?? '' }}"
                    class="w-full py-1 px-1.5 text-xs rounded border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-white">
            </div>
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">
                    Descrição <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model.defer="itemFormData.descricao"
                    value="{{ $formData['descricao'] ?? '' }}"
                    class="w-full py-1 px-1.5 text-xs rounded border-gray-300 dark:border-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:text-white"
                    required>
                @error('itemFormData.descricao')
                    <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Linha 3: Botões --}}
        <div class="flex justify-end gap-2">
            @if ($itemId)
                <button type="button" wire:click="cancelEdit"
                    class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Cancelar
                </button>
            @endif
            <button type="submit"
                class="px-3 py-1.5 text-xs font-medium text-white bg-primary-600 rounded hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                {{ $itemId ? 'Atualizar Item' : 'Incluir Item' }}
            </button>
        </div>
    </form>
</div>
