<?php

declare(strict_types=1);

use Daikazu\FilamentMeta\FilamentMetaPlugin;

it('make() returns a FilamentMetaPlugin instance', function (): void {
    expect(FilamentMetaPlugin::make())->toBeInstanceOf(FilamentMetaPlugin::class);
});

it('getId() returns filament-meta', function (): void {
    expect(FilamentMetaPlugin::make()->getId())->toBe('filament-meta');
});
