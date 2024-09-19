<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-s-bars-arrow-up';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Wizard::make([

                    Wizard\Step::make('Invoice info')
                        ->icon('heroicon-s-table-cells')
                        ->columns(3)
                        ->schema([
                            Select::make('invoice_id')
                                ->placeholder('Select Invoice')
                                ->options(invoice::all()->pluck('invoiceno', 'id'))
                                ->searchable()
                                ->live()
                                ->autofocus()
                                ->afterStateUpdated(function ($state, $set) {
                                    $selectedInvoiceId = $state;
                                    $invoice = invoice::find($selectedInvoiceId);
                                    $set('origin', $invoice->origin);
                                    $set('dates', $invoice->dates);
                                }),

                            Forms\Components\TextInput::make('origin')
                                ->live()
                                ->disabled()
                                ->hidden(fn($get): bool => !$get('invoice_id')),

                            Forms\Components\TextInput::make('dates')
                                ->live()
                                ->disabled()
                                ->hidden(fn($get): bool => !$get('invoice_id')),
                        ]),

                    Wizard\Step::make('Product info')
                        ->columns(1)
                        ->icon('heroicon-m-user')
                        ->schema([
                            Repeater::make('Products')
                                ->columns(3)
                                ->schema([
                                    Select::make('productid')
                                        ->label('Product')
                                        ->placeholder('Select Product')
                                        ->options(Product::all()->pluck('productname', 'id'))
                                        ->searchable()
                                        ->live(),

                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->required()
                                        ->placeholder('QTY')
                                        ->numeric(),

                                    Forms\Components\TextInput::make('purchase_price')
                                        ->label('purchase price /unit')
                                        ->placeholder('Purchase P')
                                        ->required()
                                        ->numeric(),
                                    Forms\Components\TextInput::make('selling_price')
                                        ->label('Selling price / unit')
                                        ->placeholder('Selling P')
                                        ->required()
                                        ->numeric()
                                ]),


                        ])

                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),

                Tables\Columns\TextColumn::make('invoice_id')
                    ->formatStateUsing(
                        function ($record) {
                            // Retrieve the client name based on the client ID
                            $invoice = Invoice::find($record->invoice_id);
                            // Check if the invoice exists to avoid runtime errors
                            if ($invoice) {

                                return $invoice->origin." (".$invoice->invoiceno. ")";
                            }
                        }
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_id')
                ->formatStateUsing(
                    function ($record) {                        
                        $product = Product::find($record->product_id);
                        // Check if the invoice exists to avoid runtime errors
                        if ($product) {

                            return $product->productname;
                        }
                    }
                )
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->searchable(),
                Tables\Columns\TextColumn::make('selling_price')
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
