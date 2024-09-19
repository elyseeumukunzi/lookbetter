<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RepportResource\Pages;
use App\Filament\Resources\RepportResource\RelationManagers;
use App\Models\Repport;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RepportResource extends Resource
{
    protected static ?string $model = Repport::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('type')
                    ->options([
                        'daily' => 'Daily',
                        'custom' => 'Custom'
                    ])
                    ->default('daily')
                    ->live(),
                Section::make('Date range')
                    ->columns('2')
                    ->schema([
                        Forms\Components\DatePicker::make('from_date')
                            ->default(now())
                            ->maxDate(now()),

                        Forms\Components\DatePicker::make('to_date')
                            ->default(now())
                            ->maxDate(now()),

                    ]),

                Forms\Components\TextInput::make('total_tax')
                    ->numeric()
                    ->default(0)
                    ->label('Total tax if you paid any in that range')
                    ->maxLength(255),

                Toggle::make('sms_sent_status')
                    ->label('Send SMS')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No')
                    ->searchable(),

                Tables\Columns\TextColumn::make('from_date')
                    ->searchable(),
                Tables\Columns\TextColumn::make('to_date')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_sales')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cash_at_hand')
                    ->searchable(),

                Tables\Columns\TextColumn::make('cash_at_partners')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_expence')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_tax')
                    ->searchable(),

                Tables\Columns\ToggleColumn::make('sms_sent_status'),

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
                Filter::make('SMS STATUS')
                    ->form([
                        Forms\Components\Select::make('status')
                        ->options([
                            '1' => 'Sent',
                            '0' => 'Not sent'
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {

                        return $query
                            ->when(
                                $data['status'],
                                fn(Builder $query, $data): Builder => $query->where('sms_sent_status',$data),
                            );
                    }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListRepports::route('/'),
            'create' => Pages\CreateRepport::route('/create'),
            'edit' => Pages\EditRepport::route('/{record}/edit'),
        ];
    }
}
