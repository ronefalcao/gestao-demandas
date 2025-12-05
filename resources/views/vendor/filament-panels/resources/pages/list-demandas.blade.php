<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <div class="flex flex-col gap-y-6">
        <x-filament-panels::resources.tabs />

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

        {{ $this->table }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>
</x-filament-panels::page>

@push('scripts')
<script>
    (function() {
        const storageKey = 'demanda-filters';
        const componentId = @js($this->getId());
        let filtersApplied = false;
        
        // Função para extrair filtros da URL
        function extractFiltersFromUrl() {
            const urlParams = new URLSearchParams(window.location.search);
            const filters = {};
            
            // Extrair todos os parâmetros de filtro
            urlParams.forEach((value, key) => {
                // Padrão: tableFilters[filter_name][value] = value
                const match = key.match(/tableFilters\[([^\]]+)\]\[value\]/);
                if (match) {
                    filters[match[1]] = value;
                }
            });
            
            return filters;
        }
        
        // Função para salvar filtros no sessionStorage
        function saveFilters() {
            try {
                const filters = extractFiltersFromUrl();
                // Salvar mesmo se não houver filtros (para limpar)
                sessionStorage.setItem(storageKey, JSON.stringify(filters));
            } catch (e) {
                console.error('Erro ao salvar filtros:', e);
            }
        }
        
        // Função para aplicar filtros via Livewire e forçar atualização
        function applyFiltersViaLivewire(filters) {
            try {
                if (window.Livewire && window.Livewire.find) {
                    const component = window.Livewire.find(componentId);
                    if (component && component.__instance) {
                        let hasChanges = false;
                        const filtersToApply = {};
                        
                        // Preparar todos os filtros para aplicar
                        Object.keys(filters).forEach(key => {
                            if (filters[key]) {
                                filtersToApply[key] = filters[key];
                            }
                        });
                        
                        if (Object.keys(filtersToApply).length === 0) {
                            return false;
                        }
                        
                        // Aplicar todos os filtros e disparar eventos de mudança
                        Object.keys(filtersToApply).forEach(key => {
                            const filterKey = `tableFilters.${key}.value`;
                            
                            try {
                                // Usar o método correto do Livewire para atualizar
                                component.set(filterKey, filtersToApply[key], false);
                                hasChanges = true;
                            } catch (e) {
                                console.warn('Erro ao definir filtro ' + key, e);
                            }
                        });
                        
                        if (hasChanges) {
                            // Forçar atualização do componente e disparar eventos de mudança
                            setTimeout(() => {
                                try {
                                    // Disparar atualização do Livewire para aplicar os filtros
                                    if (component.$refresh) {
                                        component.$refresh();
                                    } else if (component.call) {
                                        component.call('$refresh');
                                    }
                                    
                                    // Também tentar disparar eventos nos elementos do filtro
                                    Object.keys(filtersToApply).forEach(key => {
                                        // Encontrar o elemento select do filtro e disparar evento change
                                        const filterSelect = document.querySelector(`select[wire\\:model*="tableFilters.${key}"]`);
                                        if (filterSelect) {
                                            // Disparar evento change para forçar atualização
                                            filterSelect.dispatchEvent(new Event('change', { bubbles: true }));
                                            filterSelect.dispatchEvent(new Event('input', { bubbles: true }));
                                        }
                                    });
                                    
                                    // Atualizar URL para sincronizar
                                    setTimeout(() => {
                                        const newParams = new URLSearchParams(window.location.search);
                                        Object.keys(filtersToApply).forEach(key => {
                                            if (filtersToApply[key]) {
                                                newParams.set(`tableFilters[${key}][value]`, filtersToApply[key]);
                                            }
                                        });
                                        const newUrl = window.location.pathname + (newParams.toString() ? '?' + newParams.toString() : '');
                                        window.history.replaceState({}, '', newUrl);
                                        saveFilters();
                                    }, 200);
                                } catch (e) {
                                    console.error('Erro ao atualizar após aplicar filtros', e);
                                    // Se falhar, usar redirecionamento como fallback
                                    const newParams = new URLSearchParams(window.location.search);
                                    Object.keys(filtersToApply).forEach(key => {
                                        if (filtersToApply[key]) {
                                            newParams.set(`tableFilters[${key}][value]`, filtersToApply[key]);
                                        }
                                    });
                                    const newUrl = window.location.pathname + (newParams.toString() ? '?' + newParams.toString() : '');
                                    window.location.href = newUrl;
                                }
                            }, 100);
                            
                            filtersApplied = true;
                            return true;
                        }
                    }
                }
            } catch (e) {
                console.error('Erro ao aplicar filtros via Livewire:', e);
            }
            return false;
        }
        
        // Função para restaurar filtros do sessionStorage
        function restoreFilters() {
            try {
                const savedFilters = sessionStorage.getItem(storageKey);
                if (!savedFilters) {
                    return false;
                }
                
                const filters = JSON.parse(savedFilters);
                
                // Verificar se há filtros para restaurar
                const hasFiltersToRestore = Object.keys(filters).some(key => filters[key]);
                if (!hasFiltersToRestore) {
                    return false;
                }
                
                const urlParams = new URLSearchParams(window.location.search);
                const hasFiltersInUrl = Array.from(urlParams.keys()).some(key => key.startsWith('tableFilters'));
                
                // Se já houver filtros na URL, não restaurar
                if (hasFiltersInUrl) {
                    return false;
                }
                
                // Usar URL para aplicar filtros (mais confiável no Filament)
                // O Filament lê os filtros da URL quando a página carrega
                Object.keys(filters).forEach(key => {
                    if (filters[key]) {
                        urlParams.set(`tableFilters[${key}][value]`, filters[key]);
                    }
                });
                
                const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                
                // Se a URL atual não tem os filtros, aplicar via Livewire ou redirecionar
                if (window.location.href !== newUrl) {
                    // Tentar aplicar via Livewire primeiro (para evitar recarregar a página)
                    if (window.Livewire && window.Livewire.find) {
                        try {
                            const component = window.Livewire.find(componentId);
                            if (component) {
                                // Aplicar filtros via Livewire
                                const applied = applyFiltersViaLivewire(filters);
                                if (applied) {
                                    // URL será atualizada dentro de applyFiltersViaLivewire
                                    return true;
                                }
                            }
                        } catch (e) {
                            console.warn('Não foi possível aplicar via Livewire, usando redirecionamento', e);
                        }
                    }
                    
                    // Fallback: redirecionar com filtros na URL (mais confiável)
                    // Isso garante que o Filament leia os filtros corretamente
                    window.location.href = newUrl;
                    return true;
                }
                
                return false;
            } catch (e) {
                console.error('Erro ao restaurar filtros:', e);
                return false;
            }
        }
        
        // Aguardar o Livewire estar pronto
        function initialize() {
            // Resetar flag quando inicializar
            filtersApplied = false;
            
            const urlParams = new URLSearchParams(window.location.search);
            const hasFiltersInUrl = Array.from(urlParams.keys()).some(key => key.startsWith('tableFilters'));
            
            // Se não houver filtros na URL, tentar restaurar
            if (!hasFiltersInUrl) {
                // Aguardar um pouco para garantir que o Livewire está pronto
                setTimeout(() => {
                    if (!filtersApplied) {
                        restoreFilters();
                    }
                }, 400);
            } else {
                // Se já houver filtros na URL, salvar eles
                saveFilters();
            }
            
            // Observar mudanças via Livewire
            if (window.Livewire) {
                setupLivewireHooks();
            } else {
                document.addEventListener('livewire:init', setupLivewireHooks);
            }
        }
        
        function setupLivewireHooks() {
            // Salvar filtros quando Livewire atualizar
            Livewire.hook('morph.updated', function({ component }) {
                if (component.id === componentId) {
                    setTimeout(() => {
                        saveFilters();
                    }, 100);
                }
            });
            
            // Quando o componente for montado/atualizado, verificar se precisa restaurar filtros
            Livewire.hook('component.init', function({ component }) {
                if (component.id === componentId) {
                    setTimeout(() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const hasFiltersInUrl = Array.from(urlParams.keys()).some(key => key.startsWith('tableFilters'));
                        if (!hasFiltersInUrl && !filtersApplied) {
                            restoreFilters();
                        }
                    }, 500);
                }
            });
        }
        
        // Observar mudanças na URL
        let lastUrl = window.location.href;
        
        // Salvar quando a URL mudar
        const urlObserver = setInterval(function() {
            const currentUrl = window.location.href;
            if (currentUrl !== lastUrl) {
                lastUrl = currentUrl;
                // Se a URL mudou, pode ser que filtros tenham sido aplicados
                const urlParams = new URLSearchParams(window.location.search);
                const hasFiltersInUrl = Array.from(urlParams.keys()).some(key => key.startsWith('tableFilters'));
                if (hasFiltersInUrl) {
                    saveFilters();
                }
            }
        }, 500);
        
        // Limpar quando a página descarregar
        window.addEventListener('beforeunload', function() {
            clearInterval(urlObserver);
            saveFilters();
        });
        
        // Usar popstate para detectar navegação do navegador
        window.addEventListener('popstate', function() {
            saveFilters();
            lastUrl = window.location.href;
            setTimeout(() => {
                initialize();
            }, 100);
        });
        
        // Inicializar quando o DOM estiver pronto
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initialize);
        } else {
            initialize();
        }
        
        // Inicializar quando Livewire navegar (voltar para a página)
        document.addEventListener('livewire:navigated', function() {
            setTimeout(() => {
                filtersApplied = false;
                // Verificar se estamos na página de lista
                const isListPage = window.location.pathname.includes('/demandas') && 
                                   !window.location.pathname.includes('/create') &&
                                   !window.location.pathname.match(/\/demandas\/[^\/]+$/);
                
                if (isListPage) {
                    initialize();
                }
            }, 300);
        });
        
        // Também escutar eventos de navegação do Livewire
        document.addEventListener('livewire:init', function() {
            Livewire.hook('morph.updating', function({ component }) {
                if (component.id === componentId) {
                    saveFilters();
                }
            });
        });
    })();
</script>
@endpush

