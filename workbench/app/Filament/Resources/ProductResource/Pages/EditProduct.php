<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\ProductResource\Pages;

use Daikazu\FilamentMeta\Concerns\InteractsWithMeta;
use Filament\Resources\Pages\EditRecord;
use Workbench\App\Filament\Resources\ProductResource;

class EditProduct extends EditRecord
{
    use InteractsWithMeta;

    protected static string $resource = ProductResource::class;
}
