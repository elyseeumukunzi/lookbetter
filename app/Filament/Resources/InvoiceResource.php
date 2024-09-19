<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-s-table-cells';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('dates')
                    ->required(),
                Forms\Components\TextInput::make('invoiceno')
                    ->required()
                    ->placeholder('Write Invoice No')
                    ->maxLength(255),
                Forms\Components\TextInput::make('origin')
                    ->required()
                    ->placeholder('Invoice origin')
                    ->maxLength(255),
                Forms\Components\TextInput::make('tin')                    
                    ->placeholder('Seller TIN')
                    ->maxLength(255),
                Forms\Components\TextInput::make('contacts')
                    ->required()
                    ->placeholder('Write seller phone No')
                    ->minLength(10)
                    ->maxLength(10),

                Textarea::make('description')
                    ->default(null)
                    ->placeholder('Write some comments'),

                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->maxLength(255)
                    ->default(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dates')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoiceno')
                    ->searchable(),
                Tables\Columns\TextColumn::make('origin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contacts')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
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
                Tables\Actions\Action::make('PDF')
                    ->button()
                    ->icon('heroicon-o-arrow-down-tray')
                    //send the record in url as id to download
                    ->url(function ($record) {
                        return url("invoice/pdf/download?id={$record->id}");
                    })

                    ->openUrlInNewTab(),
            
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
