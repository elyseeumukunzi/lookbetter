<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-s-briefcase';

    public static function form(Form $form): Form
    {
        return $form
        ->columns(3)
            ->schema([
                Section::make('Product info')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('productname')
                    ->placeholder('Product name')
                    ->required()
                    ->maxLength(255),

                Select::make('category_id')
                    ->options(Category::all()->pluck('name', 'id')),

                Select::make('brand_id')
                    ->options(Brand::all()->pluck('name', 'id'))
                    ->default(1),

                

               select::make('model')
               ->hint('Only for lens')
                    ->options([
                        'Single Vision' => 'Single Vision',
                        'Bifocal' => 'Bifocal',
                        'Progressive' => 'Progressive'
                    ])
                    ,
                ]),
                section::make('Selling details')
                ->columns(4)
                ->schema([

                    Forms\Components\TextInput::make('price')
                    ->required()
                    ->placeholder('Product price')
                    ->maxLength(255),

                    select::make('selling_unit')
                    ->required()                    
                    ->options([
                        'pcs' => 'PIECE',
                        'box' => 'BOX',
                        'Pair' => 'Pair',
                        
                    ])
                    ->default('pcs'),

                    Forms\Components\TextInput::make('Quantity')
                    ->required()
                    ->numeric()
                    ->placeholder('Available quantity'),

                
               select::make('status')
               ->options([
                '0' => 'Not Available',
                '1' => 'Available'
               ]),
                    
                ]),
               

              
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),

                Tables\Columns\TextColumn::make('productname')
                ->searchable(),

                  
                
                Tables\Columns\TextColumn::make('price')
                    ->searchable(),
                Tables\Columns\TextColumn::make('selling_unit')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('status'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
