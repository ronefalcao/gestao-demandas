<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use App\Models\Projeto;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class PlanejamentoTimelineWidget extends Widget
{
    protected static string $view = 'filament.widgets.planejamento-timeline-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        // Desabilitar este widget, usar PlanejamentoGanttWidget
        return false;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();

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

        // Para cada projeto, buscar seus itens através das features
        foreach ($projetos as $projeto) {
            $itensQuery = Item::query()
                ->join('features', 'itens.feature_id', '=', 'features.id')
                ->where('features.projeto_id', $projeto->id)
                ->with(['feature.status', 'sprint'])
                ->select('itens.*')
                ->orderBy('itens.numero');

            $projeto->itens = $itensQuery->get();
        }

        // Preparar dados para a view
        $this->data = [
            'projetos' => $projetos,
        ];
    }

    public function getViewData(): array
    {
        return $this->data;
    }
}
