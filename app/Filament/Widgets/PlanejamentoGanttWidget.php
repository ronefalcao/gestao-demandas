<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use App\Models\Projeto;
use App\Models\Sprint;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class PlanejamentoGanttWidget extends Widget
{
    protected static string $view = 'filament.widgets.planejamento-gantt-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && ($user->canManageSystem() || $user->isPlanejador() || $user->isGestor());
    }

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();

        // Buscar sprints ordenadas por número
        $sprints = Sprint::orderBy('numero')->get();

        // Buscar projetos que o planejador tem acesso
        if ($user->isAdmin()) {
            $projetos = Projeto::where('ativo', true)
                ->orderBy('nome')
                ->get();
        } else {
            $projetosIds = $user->projetos()->pluck('projetos.id');
            $projetos = Projeto::whereIn('id', $projetosIds)
                ->where('ativo', true)
                ->orderBy('nome')
                ->get();
        }

        // Estruturar dados hierarquicamente: Projeto > Módulo > Feature > Item
        $estrutura = [];

        foreach ($projetos as $projeto) {
            // Buscar todas as features do projeto com status e módulo
            $todasFeatures = $projeto->features()->with(['status', 'modulo'])->orderBy('numero')->get();

            // Buscar itens do projeto através das features
            $itens = Item::query()
                ->join('features', 'itens.feature_id', '=', 'features.id')
                ->where('features.projeto_id', $projeto->id)
                ->with(['feature.status', 'feature.modulo', 'sprint'])
                ->select('itens.*')
                ->orderBy('features.numero')
                ->orderBy('itens.numero')
                ->get();

            // Agrupar itens por feature
            $itensPorFeature = [];
            foreach ($itens as $item) {
                $featureId = $item->feature_id;
                if (!isset($itensPorFeature[$featureId])) {
                    $itensPorFeature[$featureId] = [];
                }
                $itensPorFeature[$featureId][] = $item;
            }

            // Agrupar features por módulo
            $modulos = [];
            foreach ($todasFeatures as $feature) {
                $moduloId = $feature->modulo_id;
                $moduloNome = null;
                $moduloObjeto = null;
                
                // Obter nome do módulo
                try {
                    if ($feature->modulo_id && $feature->relationLoaded('modulo') && $feature->modulo) {
                        $moduloObjeto = $feature->modulo;
                        if (is_object($moduloObjeto)) {
                            $moduloNome = $moduloObjeto->nome;
                        }
                    }
                    
                    // Fallback: tentar obter do atributo direto (campo antigo)
                    if (!$moduloNome && isset($feature->getAttributes()['modulo'])) {
                        $moduloAttr = $feature->getAttributes()['modulo'];
                        if (!empty($moduloAttr)) {
                            if (is_string($moduloAttr)) {
                                $moduloNome = $moduloAttr;
                            } elseif (is_object($moduloAttr) && isset($moduloAttr->nome)) {
                                $moduloNome = $moduloAttr->nome;
                                $moduloObjeto = $moduloAttr;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignorar erros
                    $moduloNome = null;
                }
                
                // Usar módulo_id como chave, ou 'sem-modulo' se não tiver
                $chaveModulo = $moduloId ? $moduloId : 'sem-modulo-' . ($moduloNome ?? 'sem-nome');
                
                if (!isset($modulos[$chaveModulo])) {
                    $modulos[$chaveModulo] = [
                        'modulo' => $moduloObjeto,
                        'moduloNome' => $moduloNome ?? 'Sem Módulo',
                        'features' => [],
                    ];
                }
                
                $modulos[$chaveModulo]['features'][] = [
                    'feature' => $feature,
                    'itens' => $itensPorFeature[$feature->id] ?? [],
                ];
            }

            if (!empty($modulos)) {
                $estrutura[] = [
                    'projeto' => $projeto,
                    'modulos' => array_values($modulos),
                ];
            }
        }

        // Preparar dados para a view
        $this->data = [
            'sprints' => $sprints,
            'estrutura' => $estrutura,
        ];
    }

    public function getViewData(): array
    {
        return $this->data;
    }
}