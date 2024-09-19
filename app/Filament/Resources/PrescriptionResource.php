<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrescriptionResource\Pages;
use App\Filament\Resources\PrescriptionResource\RelationManagers;
use App\Models\Client;
use App\Models\Consultation;
use App\Models\Prescription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PrescriptionResource extends Resource
{
    protected static ?string $model = Prescription::class;

    protected static ?string $navigationIcon = 'heroicon-s-adjustments-horizontal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('consultation_id')
                    ->required(),
                Forms\Components\TextInput::make('ingredient')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nature')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('reaction')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('comment')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('status')
                    ->maxLength(255)
                    ->default(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('consultation_id')
                    ->formatStateUsing(
                        function ($record) {
                            // Retrieve the client name based on the client ID
                            $consultation = Consultation::find($record->consultation_id);
                            $clientid = $consultation->client_id;
                            $client = Client::find($clientid);


                            // Check if the product exists to avoid runtime errors
                            if ($client) {
                                
                                return $client->firstname." ".$client->lastname;
                            }


                            // Return the original value if client not found
                        }
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('ingredient')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nature')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reaction')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comment')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
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
                Tables\Actions\Action::make('Prescr')
                    ->button()
                    ->icon('heroicon-o-arrow-down-tray')
                    //send the record in url as id to download
                    ->url(function ($record) {
                        return url("prescriptions/pdf/download?id={$record->consultation_id}");
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
            'index' => Pages\ListPrescriptions::route('/'),
            'create' => Pages\CreatePrescription::route('/create'),
            'edit' => Pages\EditPrescription::route('/{record}/edit'),
        ];
    }
}
