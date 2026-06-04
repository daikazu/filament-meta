<?php

declare(strict_types=1);

namespace Daikazu\FilamentMeta\Forms\Components;

use Filament\Forms\Components\Repeater;

/**
 * A Repeater bound under the meta state key, storing an array meta value.
 *
 * MetaRepeater::make('links') maps the repeater's state to the model's meta
 * value at "links" by naming the field "{stateKey}.links" — Field::make()
 * sets the component name and (dotted) state path from the given name.
 */
class MetaRepeater extends Repeater
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
