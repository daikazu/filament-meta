<?php

declare(strict_types=1);

namespace Daikazu\FilamentMeta\Forms\Components;

use Closure;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\Support\Htmlable;

/**
 * A Section whose descendant fields are scoped under the meta state key.
 *
 * MetaSection::make('SEO')->schema([TextInput::make('seo.title')])
 * persists the field to the model's meta value at "seo.title".
 *
 * Scoping is achieved with the framework's HasState::statePath(): setting the
 * section's relative state path to the configured meta key makes every
 * descendant component resolve its absolute path beneath it (e.g. "meta.seo.title").
 */
class MetaSection extends Section
{
    public static function make(string|array|Htmlable|Closure|null $heading = null): static
    {
        return parent::make($heading)->statePath(static::metaStateKey());
    }

    protected static function metaStateKey(): string
    {
        $key = config('filament-meta.state_key', 'meta');

        return is_string($key) ? $key : 'meta';
    }
}
