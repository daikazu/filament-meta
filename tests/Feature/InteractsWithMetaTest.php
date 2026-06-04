<?php

declare(strict_types=1);

use Workbench\App\Filament\Resources\ProductResource\Pages\CreateProduct;
use Workbench\App\Filament\Resources\ProductResource\Pages\EditProduct;
use Workbench\App\Models\Product;

use function Pest\Livewire\livewire;

it('persists meta fields when creating via the resource page', function (): void {
    livewire(CreateProduct::class)
        ->fillForm([
            'name' => 'Widget',
            'meta.seo.title' => 'Best Widget',
            'meta.seo.description' => 'A great widget',
            'meta.featured' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::firstWhere('name', 'Widget');

    expect($product)->not->toBeNull()
        ->and($product->meta->get('seo.title'))->toBe('Best Widget')
        ->and($product->meta->get('seo.description'))->toBe('A great widget')
        ->and($product->meta->boolean('featured'))->toBeTrue();
});

it('hydrates the edit form from meta and persists updates', function (): void {
    $product = Product::create(['name' => 'Widget']);
    $product->meta->setMany(['seo' => ['title' => 'Old'], 'featured' => false]);

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertFormSet([
            'meta.seo.title' => 'Old',
            'meta.featured' => false,
        ])
        ->fillForm([
            'meta.seo.title' => 'New',
            'meta.featured' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = $product->fresh();

    expect($fresh->meta->get('seo.title'))->toBe('New')
        ->and($fresh->meta->boolean('featured'))->toBeTrue();
});

it('writes no meta when only non-meta fields are filled', function (): void {
    livewire(CreateProduct::class)
        ->fillForm(['name' => 'Bare'])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::firstWhere('name', 'Bare');

    expect($product)->not->toBeNull()
        ->and($product->meta->all())->toBe([])
        ->and($product->meta->has('featured'))->toBeFalse()
        ->and($product->meta->has('seo'))->toBeFalse();
});

it('persists only the populated meta key and skips empty siblings', function (): void {
    livewire(CreateProduct::class)
        ->fillForm([
            'name' => 'Featured only',
            'meta.featured' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::firstWhere('name', 'Featured only');

    expect($product->meta->boolean('featured'))->toBeTrue()
        ->and($product->meta->has('seo'))->toBeFalse();
});

it('removes a cleared nested leaf while preserving its sibling', function (): void {
    $product = Product::create(['name' => 'Widget']);
    $product->meta->setMany(['seo' => ['title' => 'Title', 'description' => 'Desc']]);

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm(['meta.seo.title' => ''])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = $product->fresh();

    expect($fresh->meta->get('seo.title'))->toBeNull()
        ->and($fresh->meta->get('seo.description'))->toBe('Desc');
});

it('forgets a meta key when all of its fields are cleared', function (): void {
    $product = Product::create(['name' => 'Widget']);
    $product->meta->setMany([
        'seo' => ['title' => 'Title', 'description' => 'Desc'],
        'featured' => true,
    ]);

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'meta.seo.title' => '',
            'meta.seo.description' => '',
            'meta.featured' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = $product->fresh();

    expect($fresh->meta->has('seo'))->toBeFalse()
        ->and($fresh->meta->all())->not->toHaveKey('seo')
        ->and($fresh->meta->boolean('featured'))->toBeFalse();
});

it('persists a falsey meta value rather than pruning it', function (): void {
    $product = Product::create(['name' => 'Widget']);
    $product->meta->setMany(['featured' => true]);

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm(['meta.featured' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = $product->fresh();

    expect($fresh->meta->has('featured'))->toBeTrue()
        ->and($fresh->meta->boolean('featured'))->toBeFalse();
});
