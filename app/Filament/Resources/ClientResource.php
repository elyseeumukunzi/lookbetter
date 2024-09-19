<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Cell;
use App\Models\Client;
use App\Models\District;
use App\Models\Insurance;
use App\Models\Province;
use App\Models\Sector;
use App\Models\Village;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Section::make('Patient Info')
                    ->icon('heroicon-o-user')
                    ->description("Provide personal client's information")
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('firstname')
                            ->required()
                            ->placeholder('Type Family name here')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('lastname')
                            ->required()
                            ->placeholder('Type church name here')
                            ->maxLength(255),
                        select::make('sex')
                            ->required()
                            ->placeholder('Gender')
                            ->options([
                                'M' => 'Male',
                                'F' => 'Female'
                            ])
                            ->native(false),
                        Forms\Components\TextInput::make('nid')
                            ->placeholder('16 digits NID')
                            ->hintIcon('heroicon-s-credit-card')
                            ->maxLength(16)
                            ->minLength(16),

                        DatePicker::make('dob')
                            ->required(false)
                            ->placeholder('Date Of birth')
                            ->hintIcon('heroicon-s-calendar'),

                        Section::make('Residence info')
                            ->icon('heroicon-o-map-pin')
                            ->description("Where is located the client")
                            ->columns(3)
                            ->schema([
                                Select::make('province')
                                    ->required()
                                    ->live()
                                    ->searchable()
                                    ->options(Province::all()->pluck('provincename', 'provincecode')),

                                Select::make('district')
                                    ->required()
                                    ->searchable()
                                    ->hidden(fn($get): bool => !$get('province'))
                                    ->preload()
                                    ->options(function ($get) {
                                        return District::where('provincecode', $get('province'))->pluck('districtname', 'DistrictId');
                                    })
                                    ->live(),
                                Select::make('sector')
                                    ->required()
                                    ->searchable()
                                    ->hidden(fn($get): bool => !$get('district'))
                                    ->options(function ($get) {
                                        return Sector::where('DistrictCode', $get('district'))->pluck('SectorName', 'SectorId');
                                    })
                                    ->live(),

                                Select::make('cell')
                                    ->required()
                                    ->searchable()
                                    ->hidden(fn($get): bool => !$get('sector'))
                                    ->options(function ($get) {
                                        return Cell::where('SectorCode', $get('sector'))->pluck('CellName', 'CellCode');
                                    })
                                    ->live(),

                                Select::make('village')
                                    ->searchable()
                                    ->hidden(fn($get): bool => !$get('cell'))
                                    ->options(function ($get) {
                                        return Village::where('cellcode', $get('cell'))->pluck('VillageName', 'VillageCode');
                                    })
                                    ->live(),
                            ]),


                        Section::make('insurance info')
                            ->icon('heroicon-s-credit-card')
                            ->description('Describe more about the insurance')
                            ->columns(3)
                            ->schema([
                                Select::make('insurance_id')
                                    ->label('Select Insurance')
                                    ->options(insurance::all()->pluck('name', 'id'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $selectedinsurance = $state;
                                        //dd($state);
                                        $insurance = insurance::find($selectedinsurance);
                                        $set('tm', $insurance->tm);
                                    }),

                                Forms\Components\TextInput::make('tm')
                                    ->label('Ticket Moderateur %')
                                    ->disabled()
                                    ->live(),



                                Forms\Components\TextInput::make('cardnumber')
                                    ->placeholder('Affiliation number')
                                    ->maxLength(255)
                                    ->live()
                                    ->hidden(fn($get): bool => $get('insuranceid') == '1'),
                                Forms\Components\TextInput::make('affiliatesociety')
                                    ->placeholder('Place of work')
                                    ->maxLength(255)
                                    ->hidden(fn($get): bool => $get('insuranceid') == '1'),

                                Select::make('relationship')
                                    ->options([
                                        'HIMSELF' => 'HimSelf',
                                        'MARIED' => 'Maried',
                                        'Child' => 'Child',
                                    ])
                                    ->hidden(fn($get): bool => $get('insuranceid') == '1')
                                    ->live(),

                                Forms\Components\TextInput::make('mainmember')
                                    ->placeholder('Principal Member')
                                    ->maxLength(255)
                                    ->live()
                                    ->hidden(fn($get): bool => $get('insuranceid') == '1'),

                                Forms\Components\TextInput::make('status')
                                    ->numeric()
                                    ->default(1)
                                    ->hidden(),

                            ]),

                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('firstname')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lastname')
                    ->searchable(),
                Tables\Columns\TextColumn::make('insurance_id')
                    ->formatStateUsing(
                        function ($record) {
                            // Retrieve the client name based on the client ID
                            $insurance = insurance::where('id', $record->insurance_id)->first();
                            $insurances = $insurance->toArray();
                            $insurancename = $insurances['name'];
                            $tm = $insurances['tm'];

                            // Check if the client exists
                            if ($insurance) {
                                return $insurancename;
                            }
                        }

                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('dob')
                    ->searchable(),
                Tables\Columns\TextColumn::make('district')
                    ->formatStateUsing(
                        function ($record) {
                            // Retrieve the client name based on the client ID
                            $districts = District::where('districtid', $record->district)->first();
                            $district = $districts->toArray();
                            $districtname = $district['DistrictName'];
                            if ($districtname) {
                                return $districtname;
                            }
                        }

                    )
                    ->searchable(),
                Tables\Columns\TextColumn::make('sector')
                    ->formatStateUsing(
                        function ($record) {
                            // Retrieve the client name based on the client ID
                            $sectors = Sector::where('SectorId', $record->sector)->first();
                            $sector = $sectors->toArray();
                            $sectorname = $sector['SectorName'];
                            if ($sectorname) {
                                return $sectorname;
                            }
                        }

                    )
                    ->searchable(),

                Tables\Columns\TextColumn::make('cell')
                    ->formatStateUsing(
                        function ($record) {
                            // Retrieve the client name based on the client ID
                            $cells = cell::where('cellid', $record->cell)->first();
                            $cell = $cells->toArray();
                            $cellname = $cell['CellName'];
                            if ($cellname) {
                                return $cellname;
                            }
                        }

                    )
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
