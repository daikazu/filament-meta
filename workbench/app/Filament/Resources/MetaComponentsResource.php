<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources;

use Daikazu\FilamentMeta\Forms\Components\MetaBuilder;
use Daikazu\FilamentMeta\Forms\Components\MetaRepeater;
use Daikazu\FilamentMeta\Forms\Components\MetaSection;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Workbench\App\Filament\Resources\MetaComponentsResource\Pages\CreateMetaComponents;
use Workbench\App\Filament\Resources\MetaComponentsResource\Pages\EditMetaComponents;
use Workbench\App\Filament\Resources\MetaComponentsResource\Pages\ListMetaComponents;
use Workbench\App\Models\Product;

class MetaComponentsResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $slug = 'meta-components';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),

            MetaSection::make('SEO')->schema([
                TextInput::make('seo.title'),
            ]),

            MetaRepeater::make('links')
                ->schema([
                    TextInput::make('url'),
                ]),

            MetaBuilder::make('content')
                ->blocks([
                    Block::make('paragraph')
                        ->schema([
                            TextInput::make('text'),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListMetaComponents::route('/'),
            'create' => CreateMetaComponents::route('/create'),
            'edit' => EditMetaComponents::route('/{record}/edit'),
        ];
    }
}
