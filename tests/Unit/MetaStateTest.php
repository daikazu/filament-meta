<?php

declare(strict_types=1);

use Daikazu\FilamentMeta\Support\MetaState;
use Workbench\App\Models\Product;

it('injects the model meta tree', function (): void {
    $product = Product::create(['name' => 'X']);
    $product->meta->setMany(['seo' => ['title' => 'T'], 'featured' => true]);

    expect(MetaState::inject($product->fresh()))
        ->toEqual(['seo' => ['title' => 'T'], 'featured' => true]);
});

it('extracts the meta sub-array and strips it from data', function (): void {
    [$data, $meta] = MetaState::extract(
        ['name' => 'X', 'meta' => ['featured' => true]],
        'meta',
    );

    expect($data)->toBe(['name' => 'X'])
        ->and($meta)->toBe(['featured' => true]);
});

it('returns an empty meta array when the key is absent', function (): void {
    [$data, $meta] = MetaState::extract(['name' => 'X'], 'meta');

    expect($data)->toBe(['name' => 'X'])->and($meta)->toBe([]);
});

it('honours a custom state key', function (): void {
    [$data, $meta] = MetaState::extract(['name' => 'X', 'attrs' => ['a' => 1]], 'attrs');

    expect($data)->toBe(['name' => 'X'])->and($meta)->toBe(['a' => 1]);
});

it('prunes null and empty-string leaves', function (): void {
    expect(MetaState::prune(['a' => null, 'b' => '', 'c' => 'keep']))
        ->toBe(['c' => 'keep']);
});

it('prunes nested empties and drops arrays that become empty', function (): void {
    expect(MetaState::prune([
        'seo' => ['title' => null, 'description' => ''],
        'featured' => 'yes',
    ]))->toBe(['featured' => 'yes']);
});

it('keeps siblings when pruning a nested empty leaf', function (): void {
    expect(MetaState::prune([
        'seo' => ['title' => null, 'description' => 'kept'],
    ]))->toBe(['seo' => ['description' => 'kept']]);
});

it('keeps false, zero, 0.0 and "0" as real values', function (): void {
    expect(MetaState::prune([
        'featured' => false,
        'count' => 0,
        'ratio' => 0.0,
        'flag' => '0',
        'gone' => null,
    ]))->toBe([
        'featured' => false,
        'count' => 0,
        'ratio' => 0.0,
        'flag' => '0',
    ]);
});

it('returns an empty array when everything is empty', function (): void {
    expect(MetaState::prune(['a' => null, 'b' => ['c' => '']]))->toBe([]);
});
