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

        // Estruturar dados hierarquicamente: Projeto > Feature > Item
        $estrutura = [];

        foreach ($projetos as $projeto) {
            // Buscar itens do projeto através das features
            $itens = Item::query()
                ->join('features', 'itens.feature_id', '=', 'features.id')
                ->where('features.projeto_id', $projeto->id)
                ->with(['feature', 'sprint'])
                ->select('itens.*')
                ->orderBy('features.numero')
                ->orderBy('itens.numero')
                ->get();

            // Agrupar itens por feature
            $features = [];
            foreach ($itens as $item) {
                $featureId = $item->feature_id;
                if (!isset($features[$featureId])) {
                    $features[$featureId] = [
                        'feature' => $item->feature,
                        'itens' => [],
                    ];
                }
                $features[$featureId]['itens'][] = $item;
            }

            if (!empty($features) || $projeto->features()->exists()) {
                $estrutura[] = [
                    'projeto' => $projeto,
                    'features' => array_values($features),
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