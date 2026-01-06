<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Filament::serving(function () {
            Filament::registerNavigationItems([
                NavigationItem::make()
                    ->label('Abertas')
                    ->icon('heroicon-o-inbox')
                    ->group('Demandas')
                    ->sort(1)
                    ->url(\App\Filament\Resources\DemandaResource::getUrl('abertas'))
                    ->visible(fn () => Auth::check() && Auth::user() && (Auth::user()->canManageSystem() || Auth::user()->isGestor() || Auth::user()->isUsuario() || Auth::user()->isAnalista())),
                
                NavigationItem::make()
                    ->label('Desenvolvimento')
                    ->icon('heroicon-o-code-bracket')
                    ->group('Demandas')
                    ->sort(2)
                    ->url(\App\Filament\Resources\DemandaResource::getUrl('desenvolvimento'))
                    ->visible(fn () => Auth::check() && Auth::user() && (Auth::user()->canManageSystem() || Auth::user()->isGestor() || Auth::user()->isUsuario() || Auth::user()->isAnalista())),
                
                NavigationItem::make()
                    ->label('Concluídas')
                    ->icon('heroicon-o-check-circle')
                    ->group('Demandas')
                    ->sort(3)
                    ->url(\App\Filament\Resources\DemandaResource::getUrl('concluidas'))
                    ->visible(fn () => Auth::check() && Auth::user() && (Auth::user()->canManageSystem() || Auth::user()->isGestor() || Auth::user()->isUsuario() || Auth::user()->isAnalista())),
                
                NavigationItem::make()
                    ->label('Publicadas')
                    ->icon('heroicon-o-globe-alt')
                    ->group('Demandas')
                    ->sort(4)
                    ->url(\App\Filament\Resources\DemandaResource::getUrl('publicadas'))
                    ->visible(fn () => Auth::check() && Auth::user() && (Auth::user()->canManageSystem() || Auth::user()->isGestor() || Auth::user()->isUsuario() || Auth::user()->isAnalista())),
            ]);
        });
    }
}
