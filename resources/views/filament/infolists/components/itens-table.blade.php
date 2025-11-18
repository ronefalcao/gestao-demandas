@php
    $itens = $getRecord()
        ->itens()
        ->orderByRaw(
            'CAST(SPLIT_PART(numero, \'.\', 1) AS INTEGER) ASC, CAST(SPLIT_PART(numero, \'.\', 2) AS INTEGER) ASC',
        )
        ->get();
    $feature = $getRecord();
    $featureId = $feature->id;
    $sprints = \App\Models\Sprint::orderBy('numero', 'desc')->get();
@endphp

<div id="loading-overlay"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998; justify-content: center; align-items: center; flex-direction: column;">
    <div
        style="background: white; padding: 20px; border-radius: 8px; display: flex; flex-direction: column; align-items: center; gap: 10px;">
        <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        <span style="color: #333;">Carregando...</span>
    </div>
</div>

<script>
    let isLoading = false;

    function chamarAcaoFilament(acaoNome, argumentos = {}) {
        console.log('=== DEBUG: Chamando ação Filament ===');
        console.log('Ação:', acaoNome);
        console.log('Argumentos:', argumentos);

        // Prevenir múltiplos cliques
        if (isLoading) {
            console.log('Já está carregando, ignorando');
            return;
        }

        isLoading = true;
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.style.display = 'flex';
            overlay.style.flexDirection = 'column';
        }

        function hideLoader() {
            isLoading = false;
            if (overlay) overlay.style.display = 'none';
        }

        try {
            if (!window.Livewire) {
                console.error('DEBUG: Livewire não está disponível');
                alert('Erro: Livewire não está disponível.');
                hideLoader();
                return;
            }

            // Buscar o componente da página principal do Filament
            // O componente ViewFeature geralmente está no elemento principal da página
            const wireElements = document.querySelectorAll('[wire\\:id]');
            console.log('DEBUG: Elementos com wire:id encontrados:', wireElements.length);

            // Procurar pelo componente da página (pode estar em um elemento com classe fi-page ou no body)
            // Vamos tentar encontrar o componente que tem o método mountAction
            let foundComponent = null;

            for (let el of wireElements) {
                const wireId = el.getAttribute('wire:id');
                if (!wireId) continue;

                try {
                    const comp = window.Livewire.find(wireId);
                    if (!comp) continue;

                    // Verificar se este componente tem o método mountAction
                    if (typeof comp.mountAction === 'function') {
                        // Verificar se o componente tem métodos relacionados a ações do Filament
                        // Componentes de página do Filament geralmente têm getCachedActions
                        if (typeof comp.getCachedActions === 'function' ||
                            typeof comp.$wire !== 'undefined' ||
                            el.closest('.fi-page') !== null) {

                            console.log('DEBUG: Componente da página encontrado:', wireId);
                            foundComponent = comp;
                            break;
                        }
                    }
                } catch (e) {
                    console.warn('DEBUG: Erro ao processar componente:', e);
                }
            }

            // Se não encontrou pelo método acima, tentar o primeiro componente com mountAction
            if (!foundComponent) {
                for (let el of wireElements) {
                    const wireId = el.getAttribute('wire:id');
                    if (!wireId) continue;

                    try {
                        const comp = window.Livewire.find(wireId);
                        if (comp && typeof comp.mountAction === 'function') {
                            console.log('DEBUG: Usando componente alternativo:', wireId);
                            foundComponent = comp;
                            break;
                        }
                    } catch (e) {
                        // Continuar procurando
                    }
                }
            }

            if (!foundComponent) {
                console.error('DEBUG: Não foi possível encontrar o componente Livewire');
                alert('Erro: Não foi possível encontrar o componente da página.');
                hideLoader();
                return;
            }

            // Chamar a ação
            try {
                console.log('DEBUG: Chamando mountAction no componente encontrado');
                const result = foundComponent.mountAction(acaoNome, argumentos);
                console.log('DEBUG: mountAction retornou:', result);

                // Se retornar Promise, aguardar
                if (result && typeof result.then === 'function') {
                    result.then((res) => {
                        console.log('DEBUG: Promise resolvida:', res);
                        // Aguardar um pouco para o modal aparecer
                        setTimeout(() => {
                            const modal = document.querySelector('[role="dialog"]');
                            const modal2 = document.querySelector('.fi-modal');
                            if (modal || modal2) {
                                console.log('DEBUG: Modal detectado após Promise!');
                            }
                            hideLoader();
                        }, 200);
                    }).catch((err) => {
                        console.error('DEBUG: Erro na Promise:', err);
                        hideLoader();
                    });
                } else {
                    // Aguardar um pouco e verificar se o modal apareceu
                    setTimeout(() => {
                        const modal = document.querySelector('[role="dialog"]');
                        const modal2 = document.querySelector('.fi-modal');
                        if (modal || modal2) {
                            console.log('DEBUG: Modal detectado!');
                        }
                        hideLoader();
                    }, 300);
                }

                // Verificar se o modal foi aberto (verificação adicional)
                const checkModal = setInterval(() => {
                    const modal = document.querySelector('[role="dialog"]');
                    const modal2 = document.querySelector('.fi-modal');
                    const modalVisible = modal && window.getComputedStyle(modal).display !== 'none';
                    const modal2Visible = modal2 && window.getComputedStyle(modal2).display !== 'none';

                    if (modalVisible || modal2Visible) {
                        console.log('DEBUG: Modal visível detectado!');
                        hideLoader();
                        clearInterval(checkModal);
                    }
                }, 100);

                // Timeout de segurança
                setTimeout(() => {
                    clearInterval(checkModal);
                    hideLoader();
                }, 3000);

            } catch (e) {
                console.error('DEBUG: Erro ao chamar mountAction:', e);
                console.error('DEBUG: Stack:', e.stack);
                alert('Erro ao abrir modal: ' + e.message);
                hideLoader();
            }

        } catch (error) {
            console.error('DEBUG: Erro geral:', error);
            alert('Erro ao abrir modal: ' + error.message);
            hideLoader();
        }
    }

    function abrirModalNovoItem() {
        chamarAcaoFilament('novo_item');
    }

    function editarItem(itemId) {
        chamarAcaoFilament('editar_item', {
            item_id: itemId
        });
    }

    // Listener para quando o modal for aberto (esconder loader)
    document.addEventListener('livewire:initialized', () => {
        console.log('DEBUG: Livewire inicializado');
    });

    // Esconder loader quando o modal abrir
    document.addEventListener('DOMContentLoaded', () => {
        // Observar mudanças no DOM para detectar quando o modal abrir
        const observer = new MutationObserver((mutations) => {
            const modal = document.querySelector('[role="dialog"]');
            if (modal && isLoading) {
                console.log('DEBUG: Modal detectado, escondendo loader');
                isLoading = false;
                const overlay = document.getElementById('loading-overlay');
                if (overlay) overlay.style.display = 'none';
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Timeout de segurança para esconder o loader após 3 segundos
        setTimeout(() => {
            if (isLoading) {
                console.log('DEBUG: Timeout, escondendo loader');
                isLoading = false;
                const overlay = document.getElementById('loading-overlay');
                if (overlay) overlay.style.display = 'none';
            }
        }, 3000);
    });
</script>


<div class="space-y-4" x-data="{ isLoading: false }">
    <div class="flex justify-end">
        <button type="button" x-on:click="abrirModalNovoItem()"
            class="fi-btn fi-btn-color-primary fi-btn-size-md inline-flex items-center gap-1.5 justify-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm ring-1 transition duration-75 bg-primary-600 text-white hover:bg-primary-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-600 dark:bg-primary-500 dark:hover:bg-primary-400 dark:focus-visible:ring-primary-500 whitespace-nowrap">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Novo
        </button>
    </div>
</div>

<div
    class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
    <div class="overflow-x-auto">
        <table class="w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
            <thead class="divide-y divide-gray-200 bg-gray-50 dark:divide-white/5 dark:bg-white/5">
                <tr class="bg-gray-50 dark:bg-white/5">
                    <th class="px-4 py-3 text-start">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Número</span>
                    </th>
                    <th class="px-4 py-3 text-start">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Título</span>
                    </th>
                    <th class="px-4 py-3 text-start">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Descrição</span>
                    </th>
                    <th class="px-4 py-3 text-start">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Sprint</span>
                    </th>
                    <th class="px-4 py-3 text-start">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Status</span>
                    </th>
                    <th class="px-4 py-3 text-start">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Figma</span>
                    </th>
                    <th class="px-4 py-3 text-start">
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">US</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                @forelse($itens as $item)
                    <tr class="transition duration-75 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer"
                        x-data="{ itemId: {{ $item->id }} }"
                        @click="
                            if (isLoading) return;
                            isLoading = true;
                            const overlay = document.getElementById('loading-overlay');
                            if (overlay) {
                                overlay.style.display = 'flex';
                                overlay.style.flexDirection = 'column';
                            }
                            
                            // Chamar função JavaScript
                            editarItem(itemId);
                        ">
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset bg-primary-50 text-primary-700 ring-primary-700/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/20">
                                {{ $item->numero }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-950 dark:text-white">{{ $item->titulo }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($item->descricao, 60) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($item->sprint)
                                <span
                                    class="inline-flex items-center gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20">
                                    {{ $item->sprint->numero }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400 dark:text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $item->status === 'fechado' ? 'bg-success-50 text-success-700 ring-success-700/10 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/20' : 'bg-warning-50 text-warning-700 ring-warning-700/10 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/20' }}">
                                {{ ucfirst($item->status ?? 'Aberto') }}
                            </span>
                        </td>
                        <td class="px-4 py-3" @click.stop>
                            @if ($item->figma)
                                <a href="{{ $item->figma }}" target="_blank"
                                    class="inline-flex items-center gap-x-1 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                    </svg>
                                    Link
                                </a>
                            @else
                                <span class="text-sm text-gray-400 dark:text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $item->us ?? '-' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            Nenhum item encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
