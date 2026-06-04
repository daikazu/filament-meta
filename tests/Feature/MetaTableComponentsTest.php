<?php

declare(strict_types=1);

use Workbench\App\Filament\Resources\ProductResource\Pages\ListProducts;
use Workbench\App\Models\Product;

use function Pest\Livewire\livewire;

function makeProduct(string $name, array $meta = []): Product
{
    $product = Product::create(['name' => $name]);

    if ($meta !== []) {
        $product->meta->setMany($meta);
    }

    return $product;
}

it('renders a meta value in a table column', function (): void {
    $product = makeProduct('Alpha', [
        'seo' => ['title' => 'Alpha SEO Title'],
        'featured' => true,
    ]);

    livewire(ListProducts::class)
        ->assertCanSeeTableRecords([$product])
        ->assertTableColumnStateSet('seo.title', 'Alpha SEO Title', $product);
});

it('renders meta values for the column on multiple records', function (): void {
    $alpha = makeProduct('Alpha', ['seo' => ['title' => 'Alpha Title']]);
    $beta = makeProduct('Beta', ['seo' => ['title' => 'Beta Title']]);

    livewire(ListProducts::class)
        ->assertCanSeeTableRecords([$alpha, $beta])
        ->assertTableColumnStateSet('seo.title', 'Alpha Title', $alpha)
        ->assertTableColumnStateSet('seo.title', 'Beta Title', $beta);
});

it('filters table records by a text-input meta value', function (): void {
    $draft = makeProduct('Draft', ['status' => 'draft']);
    $published = makeProduct('Published', ['status' => 'published']);

    livewire(ListProducts::class)
        ->assertCanSeeTableRecords([$draft, $published])
        ->filterTable('status', ['value' => 'published'])
        ->assertCanSeeTableRecords([$published])
        ->assertCanNotSeeTableRecords([$draft]);
});

it('does not constrain records when the meta filter value is empty', function (): void {
    $draft = makeProduct('Draft', ['status' => 'draft']);
    $published = makeProduct('Published', ['status' => 'published']);

    livewire(ListProducts::class)
        ->filterTable('status', ['value' => ''])
        ->assertCanSeeTableRecords([$draft, $published]);
});

it('filters table records by a select-options meta value', function (): void {
    $featured = makeProduct('Featured', ['featured' => true]);
    $plain = makeProduct('Plain', ['featured' => false]);

    livewire(ListProducts::class)
        ->assertCanSeeTableRecords([$featured, $plain])
        ->filterTable('featured', ['value' => '1'])
        ->assertCanSeeTableRecords([$featured])
        ->assertCanNotSeeTableRecords([$plain]);
});
