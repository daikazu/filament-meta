<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources;

use Daikazu\FilamentMeta\Tables\Columns\MetaTableColumn;
use Daikazu\FilamentMeta\Tables\Filters\MetaFilter;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Workbench\App\Filament\Resources\ProductResource\Pages\CreateProduct;
use Workbench\App\Filament\Resources\ProductResource\Pages\EditProduct;
use Workbench\App\Filament\Resources\ProductResource\Pages\ListProducts;
use Workbench\App\Models\Product;

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

    /**
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
