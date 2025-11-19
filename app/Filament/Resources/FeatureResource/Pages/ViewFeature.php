<?php

namespace App\Filament\Resources\FeatureResource\Pages;

use App\Filament\Resources\FeatureResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewFeature extends ViewRecord
{
    protected static string $resource = FeatureResource::class;

    public function getTitle(): string
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make('FeatureTabs')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Informações Gerais')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Infolists\Components\Section::make('Informações da Feature')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('numero')
                                            ->label('Número'),
                                        Infolists\Components\TextEntry::make('projeto.nome')
                                            ->label('Projeto'),
                                        Infolists\Components\TextEntry::make('modulo')
                                            ->label('Módulo')
                                            ->formatStateUsing(fn ($record) => $record->modulo ? (is_object($record->modulo) ? $record->modulo->nome : $record->modulo) : '-'),
                                        Infolists\Components\TextEntry::make('titulo')
                                            ->label('Título'),
                                        Infolists\Components\TextEntry::make('descricao')
                                            ->label('Descrição')
                                            ->columnSpanFull(),
                                        Infolists\Components\TextEntry::make('status.nome')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn($record) => $record->status->cor ?? 'gray'),
                                    ])
                                    ->columns(3),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Histórico')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Infolists\Components\Section::make('Datas')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('created_at')
                                            ->label('Criado em')
                                            ->dateTime('d/m/Y H:i:s'),
                                        Infolists\Components\TextEntry::make('updated_at')
                                            ->label('Atualizado em')
                                            ->dateTime('d/m/Y H:i:s'),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
