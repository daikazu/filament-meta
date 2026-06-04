# daikazu/filament-meta

Filament v5 form components, table columns, and table filters for [`daikazu/laravel-meta`](https://github.com/daikazu/laravel-meta) typed model metadata. Write `TextInput::make('meta.seo.title')` and `Toggle::make('meta.featured')` in your resource forms — the package handles hydration from and persistence to the model's `MetaBag` automatically.

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | ^8.3 |
| Laravel | 11, 12, or 13 |
| Filament | ^5.0 |
| daikazu/laravel-meta | (installed separately) |

Your Eloquent model must use the `HasMeta` trait **and implement the `HasMetadata` interface** from `daikazu/laravel-meta`:

```php
use Daikazu\LaravelMeta\Concerns\HasMeta;
use Daikazu\LaravelMeta\Contracts\HasMetadata;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements HasMetadata
{
    use HasMeta;
}
```

The interface adds no code (the trait already satisfies it) — it's the contract `filament-meta` uses to recognise a metadata model. Records that don't implement `HasMetadata` are simply left untouched by the form trait and the `MetaTableColumn`.

---

## Installation

```bash
composer require daikazu/filament-meta
```

The service provider is auto-discovered. To publish the config file:

```bash
php artisan vendor:publish --tag="filament-meta-config"
```

### Optional panel registration

The components and `InteractsWithMeta` trait work without registering the plugin. If you want to opt in to panel-wide integration (reserved for future defaults), add the plugin to your panel:

```php
use Daikazu\FilamentMeta\FilamentMetaPlugin;

$panel->plugin(FilamentMetaPlugin::make())
```

---

## Core pattern

Add `use InteractsWithMeta;` to your resource's `CreateRecord` and `EditRecord` page classes. That is the only required change — the trait wires up all hydration and persistence hooks automatically.

### Example: ProductResource

**`app/Filament/Resources/ProductResource.php`**

```php
use Daikazu\FilamentMeta\Tables\Columns\MetaTableColumn;
use Daikazu\FilamentMeta\Tables\Filters\MetaFilter;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),
            TextInput::make('meta.seo.title'),
            Textarea::make('meta.seo.description'),
            Toggle::make('meta.featured'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                MetaTableColumn::make('seo.title'),
                MetaTableColumn::make('featured'),
            ])
            ->filters([
                MetaFilter::make('status'),
                MetaFilter::make('featured')
                    ->options([
                        '1' => 'Featured',
                        '0' => 'Not featured',
                    ])
                    ->castValue(fn (mixed $value): bool => (bool) $value),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit'   => EditProduct::route('/{record}/edit'),
        ];
    }
}
```

**`app/Filament/Resources/ProductResource/Pages/CreateProduct.php`**

```php
use Daikazu\FilamentMeta\Concerns\InteractsWithMeta;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use InteractsWithMeta;

    protected static string $resource = ProductResource::class;
}
```

**`app/Filament/Resources/ProductResource/Pages/EditProduct.php`**

```php
use Daikazu\FilamentMeta\Concerns\InteractsWithMeta;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use InteractsWithMeta;

    protected static string $resource = ProductResource::class;
}
```

Fields named `meta.*` map directly to the model's metadata. No `mutateFormData*` override is needed in your page class.

---

## Form components

### `MetaSection`

A `Section` whose child fields are scoped under the meta state key. Children use meta-relative names — the `meta.` prefix is added by the section.

```php
use Daikazu\FilamentMeta\Forms\Components\MetaSection;
use Filament\Forms\Components\TextInput;

MetaSection::make('SEO')->schema([
    TextInput::make('seo.title'),
    TextInput::make('seo.description'),
])
```

`TextInput::make('seo.title')` inside the section resolves to `meta.seo.title` in the form state, which persists to the model's `seo.title` meta key.

Using `MetaSection` is a convenience. You can also write `TextInput::make('meta.seo.title')` directly in your schema without the section wrapper — both approaches produce the same result.

### `MetaRepeater`

A `Repeater` bound to a meta key that stores an array value.

```php
use Daikazu\FilamentMeta\Forms\Components\MetaRepeater;
use Filament\Forms\Components\TextInput;

MetaRepeater::make('links')
    ->schema([
        TextInput::make('url'),
    ])
```

Stores the repeater items as the `links` meta key.

### `MetaBuilder`

A `Builder` bound to a meta key that stores its blocks structure.

```php
use Daikazu\FilamentMeta\Forms\Components\MetaBuilder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;

MetaBuilder::make('content')
    ->blocks([
        Block::make('paragraph')
            ->schema([
                TextInput::make('text'),
            ]),
    ])
```

Stores the builder blocks as the `content` meta key.

---

## Table column

`MetaTableColumn` extends Filament's `TextColumn` and reads its value from `$record->meta->get($key)` rather than a model attribute. All `TextColumn` modifiers (badges, copyable, formatting, etc.) are available.

```php
use Daikazu\FilamentMeta\Tables\Columns\MetaTableColumn;

MetaTableColumn::make('seo.title')
MetaTableColumn::make('featured')
```

The argument is a meta key, not a model attribute name.

> **`->sortable()` / `->searchable()` are not supported.** The column resolves its display value through `getStateUsing` from `$record->meta`, not from a database column — so Filament's built-in sort/search (which target a real SQL column matching the column name) would query a non-existent column. To sort or filter by a meta value, use the core package's query scopes instead: `orderByMeta($key)` for ordering and `whereMeta($key, $value)` for filtering (e.g. via a `MetaFilter`, below).

---

## Table filter

`MetaFilter` extends Filament's `Filter` and applies `->whereMeta($key, $value)` from the core package.

### Text input (default)

```php
use Daikazu\FilamentMeta\Tables\Filters\MetaFilter;

MetaFilter::make('status')
```

Renders a `TextInput` labeled "Status". When a value is entered, the table is constrained to records where the `status` meta key matches.

### Select with options

```php
MetaFilter::make('featured')
    ->options([
        '1' => 'Featured',
        '0' => 'Not featured',
    ])
    ->castValue(fn (mixed $value): bool => (bool) $value)
```

Passing `->options()` replaces the text input with a `Select`.

### `->castValue(Closure $callback)`

The core package's `whereMeta()` scope is type-indexed — it matches both the value and its PHP type. A form `Select` always yields a string (`'1'`, `'0'`), but if the stored meta value is a boolean, the string `'1'` will not match `true`. Use `->castValue()` to coerce the raw form string to the correct stored type before it reaches `whereMeta()`.

```php
->castValue(fn (mixed $value): bool => (bool) $value)
```

---

## Configuration

`config/filament-meta.php`:

```php
return [
    'state_key' => 'meta',
];
```

`state_key` is the top-level key under which metadata fields are namespaced in the Filament form state. Changing it to, say, `'attributes'` means your fields would be written as `TextInput::make('attributes.seo.title')`. All components — `MetaSection`, `MetaRepeater`, `MetaBuilder`, `MetaFilter`, and the `InteractsWithMeta` trait — read this config value automatically.

---

## How it works

`InteractsWithMeta` overrides three Filament page hooks:

- **`mutateFormDataBeforeFill`** (edit only): reads `$record->meta->all()` and injects the full metadata tree into `$data[$stateKey]`, so `meta.*` fields resolve on initial render.
- **`mutateFormDataBeforeSave` / `mutateFormDataBeforeCreate`**: extracts `$data[$stateKey]` into a `$bufferedMeta` property and removes it from `$data`, ensuring the model's `fill()` never receives a `meta` key (which would collide with the read-only `meta` accessor on `HasMeta` models). On save it also records the record's currently-stored top-level meta keys, so cleared keys can be forgotten.
- **`afterSave` / `afterCreate`**: prunes the buffered metadata (see *Save semantics* below) and calls `$record->meta->setMany(...)` for what remains, then `forgetMany(...)` for any previously-stored key the user emptied — all after the record exists in the database (important for `afterCreate`, where the record has just been inserted and now has a primary key).

The mapping logic lives in `Support\MetaState` (three static pure helpers: `inject`, `extract`, and `prune`), keeping the trait thin and the logic independently unit-testable.

### Save semantics

Saving **syncs the meta keys that have form fields** — it does not blindly write the entire form state:

- **Untouched/empty fields write nothing.** Filament includes every field in form state (an empty `TextInput` is `null`, an untouched `Toggle` is `false`). Before persisting, the buffered state is pruned: `null` and `''` leaves are removed and any sub-array that becomes empty is dropped, so a record created with only `name` filled writes **no** meta rows.
- **Cleared fields are removed.** Emptying one leaf of a key (e.g. clearing `meta.seo.title` while `meta.seo.description` stays) rewrites that key with only the remaining leaves. Clearing **every** field of a key forgets the key entirely.
- **`false` / `0` are preserved on edit.** Real falsey values (`false`, `0`, `0.0`) are not pruned. Because Filament cannot tell an untouched widget default from a deliberate value, a top-level falsey scalar is only persisted when its key was already stored on the record (i.e. on edit). On a *create* form, set a falsey value through a key that also carries a non-empty field, or persist it manually (see below).
- **Meta keys with no corresponding form field are left untouched.** The trait only ever writes or forgets keys that appear in the form state (or were stored and then cleared). Meta you manage outside the form is never deleted.

For manual control outside the form lifecycle, use the core `MetaBag` API directly — `$record->meta->sync([...])` (replace the whole set, forgetting anything not provided), `$record->meta->setMany([...])`, or `$record->meta->forget('key')` / `forgetMany([...])`.

---

## Caveats

### Value-object cast keys can't be edited as nested `meta.*` fields

If a meta key is cast to a value object in the core package (e.g. `'seo' => SeoData::class`), it **cannot** be edited through nested `meta.seo.*` form fields. The form round-trips a plain array (Filament hydrates from / dehydrates to `array`), not the value object, so persisting would attempt to overwrite the cast key with a bare array and lose the object's typed shape.

For form-editable metadata, use scalar or plain-array meta keys. If you must surface a value object in a form, manage it manually — hydrate the fields from the object in `mutateFormDataBeforeFill` and rebuild the object in `afterSave`/`afterCreate` yourself, rather than relying on the `meta.*` field convention.

### Don't set `state_key` to a real fillable column name

`state_key` (default `meta`) is the form-state key the trait extracts and strips from the data **before** the model is filled. If you set it to the name of an actual fillable database column on the model, that column's value will be removed from the data and never written by `fill()`. Keep `state_key` distinct from every real attribute name on your models.

---

## Testing and quality

- Tests: [Pest](https://pestphp.com/) 4 with [Orchestra Testbench](https://packages.tools/testbench/) and `pest-plugin-livewire` for full Filament Livewire page testing.
- Static analysis: [PHPStan](https://phpstan.org/) / [Larastan](https://github.com/larastan/larastan) at maximum level (`level: max`).
- Code style: [Laravel Pint](https://laravel.com/docs/pint).

---

## License

MIT — Copyright (c) Mike Wall. See [LICENSE.md](LICENSE.md) for details.
