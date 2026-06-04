<?php

declare(strict_types=1);

namespace Daikazu\FilamentMeta\Forms\Components;

use Filament\Forms\Components\Builder;

/**
 * A Builder bound under the meta state key, storing its blocks as a meta value.
 *
 * MetaBuilder::make('content') maps the builder's state to the model's meta
 * value at "content" by naming the field "{stateKey}.content" — Field::make()
 * sets the component name and (dotted) state path from the given name.
 */
class MetaBuilder extends Builder
{
    public static function make(?string $name = null): static
    {
        return parent::make(static::metaStateKey().'.'.$name);
    }

    protected static function metaStateKey(): string
    {
        $key = config('filament-meta.state_key', 'meta');

        return is_string($key) ? $key : 'meta';
    }
}
