<?php

declare(strict_types=1);

namespace Daikazu\FilamentMeta;

use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Panel registration hook. The components and the InteractsWithMeta trait work
 * without registering the plugin; this exists for future panel-wide defaults
 * and so apps can opt in via ->plugin(FilamentMetaPlugin::make()).
 */
class FilamentMetaPlugin implements Plugin
{
    public static function make(): static
    {
        /** @var static $plugin */
        $plugin = app(static::class);

        return $plugin;
    }

    public function getId(): string
    {
        return 'filament-meta';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
