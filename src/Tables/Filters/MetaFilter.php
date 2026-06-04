<?php

declare(strict_types=1);

namespace Daikazu\FilamentMeta\Tables\Filters;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * A table filter that constrains records by a meta value using the core
 * package's whereMeta() query scope.
 *
 * MetaFilter::make('status') renders a single "value" TextInput and applies
 * ->whereMeta('status', $value) when a value is provided.
 *
 * Calling ->options([...]) swaps the value input for a Select while still
 * applying whereMeta() against the chosen option.
 *
 * The filter form schema is built with schema() (HasSchema), and the query
 * is applied via query() (InteractsWithTableQuery::query()), which evaluates
 * the closure with the filter form's $data array.
 *
 * NOTE: the core package indexes meta values by PHP type, so whereMeta()
 * matches the *type* of the value passed (a string '1' will not match a
 * stored boolean true). When filtering a typed meta key, use ->castValue()
 * to coerce the raw form value to the stored type before it reaches the
 * scope.
 */
class MetaFilter extends Filter
{
    protected string $metaKey;

    /**
     * @var array<string|int, string>|null
     */
    protected ?array $metaOptions = null;

    protected ?Closure $castValueUsing = null;

    public static function make(?string $name = null): static
    {
        $filter = parent::make($name);
        $filter->metaKey = $filter->getName();

        return $filter;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildSchema();

        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? null;

            if (! filled($value)) {
                return $query;
            }

            if ($this->castValueUsing !== null) {
                $value = ($this->castValueUsing)($value);
            }

            return $query->whereMeta($this->metaKey, $value);
        });
    }

    /**
     * Switch the filter's input to a Select driven by the given options.
     *
     * @param  array<string|int, string>  $options
     */
    public function options(array $options): static
    {
        $this->metaOptions = $options;
        $this->buildSchema();

        return $this;
    }

    /**
     * Coerce the raw form value to the meta key's stored type before it is
     * handed to whereMeta() (e.g. fn ($v) => (bool) $v for a boolean key).
     */
    public function castValue(Closure $callback): static
    {
        $this->castValueUsing = $callback;

        return $this;
    }

    protected function buildSchema(): void
    {
        if ($this->metaOptions !== null) {
            $this->schema([
                Select::make('value')
                    ->label($this->getLabel())
                    ->options($this->metaOptions),
            ]);

            return;
        }

        $this->schema([
            TextInput::make('value')
                ->label($this->getLabel()),
        ]);
    }
}
