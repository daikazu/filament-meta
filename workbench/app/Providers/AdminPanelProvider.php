<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Filament\Panel;
use Filament\PanelProvider;
use Workbench\App\Filament\Resources\MetaComponentsResource;
use Workbench\App\Filament\Resources\ProductResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->resources([
                ProductResource::class,
                MetaComponentsResource::class,
            ]);
    }
}
