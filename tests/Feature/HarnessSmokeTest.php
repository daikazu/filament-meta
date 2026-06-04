<?php

declare(strict_types=1);

use Workbench\App\Filament\Resources\ProductResource\Pages\CreateProduct;

use function Pest\Livewire\livewire;

it('renders the create page with meta fields', function (): void {
    livewire(CreateProduct::class)
        ->assertOk()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('meta.seo.title');
});
