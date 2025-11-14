<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Filament::serving(function (): void {
            FilamentColor::register([
                'primary' => Color::hex('#667EEA'),
                'secondary' => Color::hex('#08253D'),
            ]);
        });
    }
}
