<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\MetaComponentsResource\Pages;

use Daikazu\FilamentMeta\Concerns\InteractsWithMeta;
use Filament\Resources\Pages\CreateRecord;
use Workbench\App\Filament\Resources\MetaComponentsResource;

class CreateMetaComponents extends CreateRecord
{
    use InteractsWithMeta;

    protected static string $resource = MetaComponentsResource::class;
}
