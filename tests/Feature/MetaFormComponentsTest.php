<?php

declare(strict_types=1);

use Workbench\App\Filament\Resources\MetaComponentsResource\Pages\CreateMetaComponents;
use Workbench\App\Filament\Resources\MetaComponentsResource\Pages\EditMetaComponents;
use Workbench\App\Models\Product;

use function Pest\Livewire\livewire;

it('persists a MetaSection scoped field to meta', function (): void {
    livewire(CreateMetaComponents::class)
        ->fillForm([
            'name' => 'Sectioned',
            'meta.seo.title' => 'Scoped Title',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::firstWhere('name', 'Sectioned');

    expect($product)->not->toBeNull()
        ->and($product->meta->get('seo.title'))->toBe('Scoped Title');
});

it('hydrates a MetaSection scoped field on the edit page', function (): void {
    $product = Product::create(['name' => 'Sectioned']);
    $product->meta->setMany(['seo' => ['title' => 'Existing']]);

    livewire(EditMetaComponents::class, ['record' => $product->getRouteKey()])
        ->assertFormSet([
            'meta.seo.title' => 'Existing',
        ])
        ->fillForm([
            'meta.seo.title' => 'Updated',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->fresh()->meta->get('seo.title'))->toBe('Updated');
});

it('persists a MetaRepeater array to meta', function (): void {
    livewire(CreateMetaComponents::class)
        ->fillForm([
            'name' => 'Repeated',
            'meta.links' => [
                ['url' => 'https://a.test'],
                ['url' => 'https://b.test'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::firstWhere('name', 'Repeated');
    $links = $product->meta->get('links');

    expect($links)->toBeArray()
        ->and(array_values($links))->toHaveCount(2)
        ->and(collect($links)->pluck('url')->all())
        ->toBe(['https://a.test', 'https://b.test']);
});

it('persists a MetaBuilder blocks structure to meta', function (): void {
    livewire(CreateMetaComponents::class)
        ->fillForm([
            'name' => 'Built',
            'meta.content' => [
                [
                    'type' => 'paragraph',
                    'data' => ['text' => 'Hello world'],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::firstWhere('name', 'Built');
    $content = $product->meta->get('content');

    expect($content)->toBeArray()
        ->and(array_values($content))->toHaveCount(1)
        ->and(array_values($content)[0]['type'])->toBe('paragraph')
        ->and(array_values($content)[0]['data']['text'])->toBe('Hello world');
});
