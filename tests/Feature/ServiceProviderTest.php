<?php

declare(strict_types=1);

use Illuminate\Support\Facades\View;

// Regression guard: the package ships no Blade views. Registering a
// "filament-meta" view namespace pointed at a non-existent resources/views
// directory makes `php artisan view:cache` (run on deploy) crash with
// Symfony Finder's "directory does not exist" error. Keep views unregistered.
it('does not register a filament-meta view namespace', function (): void {
    expect(View::getFinder()->getHints())->not->toHaveKey('filament-meta');
});
