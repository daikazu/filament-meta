<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\MetaComponentsResource\Pages;

use Daikazu\FilamentMeta\Concerns\InteractsWithMeta;
use Filament\Resources\Pages\EditRecord;
use Workbench\App\Filament\Resources\MetaComponentsResource;

class EditMetaComponents extends EditRecord
{
    use InteractsWithMeta;

    protected static string $resource = MetaComponentsResource::class;
}
