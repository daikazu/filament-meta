<?php

declare(strict_types=1);

namespace Daikazu\FilamentMeta\Tables\Columns;

use Daikazu\LaravelMeta\Contracts\HasMetadata;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

/**
 * A table column that renders a model's meta value.
 *
 * MetaTableColumn::make('seo.title') reads $record->meta()->get('seo.title')
 * and renders it through Filament's TextColumn view (formatting, badge,
 * copyable, etc. are all inherited).
 *
 * The column name ($key) is a meta key, NOT a real model attribute or
 * relationship. We therefore override state resolution with getStateUsing():
 * when that callback is set, HasCellState::getState() evaluates it instead of
 * getStateFromRecord(), so Filament never attempts to data_get() a
 * non-existent attribute/relationship off the record.
 *
 * Because the value is resolved at display time (not a DB column),
 * `->sortable()` / `->searchable()` are NOT supported — sort/filter on meta via
 * the core's `orderByMeta()` / `whereMeta()` scopes (e.g. a MetaFilter) instead.
 */
class MetaTableColumn extends TextColumn
{
    public static function make(?string $name = null): static
    {
        $column = parent::make($name);

        $key = $column->getName();

        return $column->getStateUsing(
            static fn (Model $record): mixed => $record instanceof HasMetadata
                ? $record->meta()->get($key)
                : null,
        );
    }
}
