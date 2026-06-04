<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\ProductResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Workbench\App\Filament\Resources\ProductResource;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;
}
