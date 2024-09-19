<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsultationResource\Pages;
use App\Filament\Resources\ConsultationResource\RelationManagers;
use App\Models\Cell;
use App\Models\Client;
use App\Models\Consultation;
use App\Models\District;
use App\Models\Insurance;
use App\Models\Province;
use App\Models\Sector;
use App\Models\TypeOfConsultation;
use App\Models\User;
use App\Models\Village;
use App\Models\Bill;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Auth\Access\Response;


class ConsultationResource extends Resource
{
    protected static ?string $model = Consultation::class;

    protected static ?string $navigationIcon = 'heroicon-s-clipboard-document-list';
    protected static ?string $navigationLabel = 'Services';



    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Wizard::make([

                    Wizard\Step::make('Service details')
                        ->columns(2)
                        ->icon('heroicon-m-user')
                        ->schema([
                            Forms\Components\DatePicker::make('dates')->live()->default(now()),

                            Section::make('Current Client')
                                ->description('Enter the service details')
                                ->columns(4)
                                ->schema([
                                    Select::make('client_id')
                                        ->label('Client names')
                                        ->preload()
                                        ->live()
                                        ->options(
                                            function () {
                                                $fullnames = [];
                                                $clients = Client::where('status', '!=', '0')
                                                    ->selectRaw('CONCAT(firstname, " ", lastname) AS clientname, id')->get()->sortByDesc('id');
                                                return $clients->pluck('clientname', 'id');
                                                //->pluck('clientname', 'id')),
                                    
                                            }
                                        )
                                        ->native(false)
                                        ->afterStateUpdated(function ($state, $set) {
                                            $clientid = $state;
                                            if (!empty($clientid)) {
                                                $clients = Client::find($clientid);
                                                $insuranceid = $clients->insurance_id;
                                                $insuranceinfo = insurance::find($insuranceid);
                                                $id = $clients->nid;
                                                $insurance = Insurance::find($insuranceid);
                                                if (!empty($insurance)) {
                                                    $insuranceinfo = $insurance->name . " (" . $insurance->tm . ")";
                                                    $set('insurancetm', $insuranceinfo);
                                                    $set('insurance_id', $insuranceid);
                                                    $set('id', $id);




                                                }

                                                //dd($insurance);
                                                //dd($clients['id']);               
                                

                                            } else {
                                                $set('insurance_id', '');
                                                $set('insurancetm', '');
                                                $set('id', '');
                                            }
                                        })
                                        ->live()
                                        ->searchable(),

                                    TextInput::make('insurance_id')
                                        ->label('Insurance')
                                        ->hidden(fn($get): bool => !$get('client_id')),

                                    TextInput::make('insurancetm')
                                        ->hidden(fn($get): bool => !$get('client_id')),


                                    TextInput::make('id')
                                        ->label('NID')
                                        ->hidden(fn($get): bool => !$get('client_id')),

                                ]),

                            // Forms\Components\TextInput::make('receptionist_id')
                            //     ->required()
                            //     ->numeric(),
                            Section::make('Service Info')

                                ->columns(3)
                                ->schema([

                                    Select::make('consultation_type_id')
                                        ->relationship('consultation_type')
                                        ->label('Service type')
                                        ->placeholder('Service Type')
                                        ->options(TypeOfConsultation::all()->pluck('type', 'id'))
                                        ->searchable()
                                        ->live(),

                                    Select::make('doctor_id')
                                        ->live()
                                        ->placeholder('Select Doctor')
                                        ->options(User::all()->pluck('name', 'id'))
                                        ->searchable()
                                        ->visible(fn($get): bool => $get('consultation_type_id') == '1')
                                        ->required(fn($get): bool => $get('consultation_type_id') == '1'),

                                    Forms\Components\TextInput::make('doctor_name')
                                        ->label('Doctor names / Clinic')
                                        ->visible(fn($get): bool => $get('consultation_type_id') == '2')
                                        ->live()
                                        ->placeholder('Enter the names of the doctor'),

                                    Select::make('prescription_status')
                                        ->hint('Choose if you give glass or not')
                                        ->options([
                                            '1' => 'Give Glass',
                                            '0' => 'No Glass'
                                        ])
                                        ->live()
                                        ->hidden(fn($get): bool => !$get('consultation_type_id')),


                                ]),

                            Section::make('Medical File')
                                ->columns(2)
                                ->hidden(fn($get): bool => !$get('prescription_status'))
                                ->schema([
                                    Section::make('Right Eye')
                                        ->columns(4)
                                        ->icon('heroicon-s-eye')
                                        ->schema([
                                            Forms\Components\TextInput::make('RDVsphere')
                                                ->required(fn($get): bool => $get('consultation_id') == '2')
                                                ->Label('Sphere ')
                                                ->hint('Vision de loin')
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('RDVcylinder')
                                                ->required(fn($get): bool => $get('consultation_id') == '2')
                                                ->label('Clylnder')
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('RDVaxis')
                                                ->required(fn($get): bool => $get('consultation_id') == '2')
                                                ->label('Axis')
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('RNV')
                                                ->hint('Vision de pres')
                                                ->label('Near Vision')
                                                ->maxLength(255),

                                        ]),

                                    Section::make('Left Eye')
                                        ->columns(4)
                                        ->icon('heroicon-s-eye')
                                        ->schema([
                                            Forms\Components\TextInput::make('LDVsphere')
                                                ->required(fn($get): bool => $get('consultation_id') == '2')
                                                ->label('Sphere')
                                                ->maxLength(255)
                                                ->hint('Vision de Loin'),
                                            Forms\Components\TextInput::make('LDVcylinder')
                                                ->required(fn($get): bool => $get('consultation_id') == '2')
                                                ->label('Cylinder')
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('LDVaxis')
                                                ->required(fn($get): bool => $get('consultation_id') == '2')
                                                ->label('Axis')
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('LNV')
                                                ->hint('Vision de pres')
                                                ->label('Near Vision')
                                                ->maxLength(255),

                                        ]),
                                ]),

                            Section::make('Comments')
                                ->description('Write some comments')
                                ->columns(2)
                                ->schema([
                                    Textarea::make('distant_comment')
                                        ->maxLength(255)
                                        ->default(null),
                                    Textarea::make('near_comment')
                                        ->maxLength(255)
                                        ->default(null),

                                ]),



                            Section::make('Lens Type')
                                ->columns(4)
                                ->live()
                                ->relationship('prescription')
                                ->hidden(fn($get): bool => !$get('prescription_status'))
                                ->schema([
                                    Select::make('ingredient')
                                        ->label('Lens Type')
                                        ->options([
                                            'mineral' => 'Mineral',
                                            'organic' => 'Organic'
                                        ])
                                        ->live(),
                                    Select::make('nature')
                                        ->label('Lens Nature')
                                        ->options([
                                            'bifocal' => 'Bifocal',
                                            'progressive' => 'progressive',
                                            'single vision' => 'single vision'

                                        ])
                                        ->live(),
                                    Select::make('reaction')
                                        ->label('Sun Reaction')
                                        ->options([
                                            'clear' => 'Clear',
                                            'photochronic' => 'Photochronic'
                                        ])
                                        ->live(),

                                    Select::make('lightreaction')
                                        ->label('Light Reaction')
                                        ->options([
                                            'hc' => 'HC',
                                            'hmc' => 'HMC',
                                            'bluecut' => 'BLUE CUT',
                                            'teinture' => 'TEINTURE'

                                        ])
                                        ->live(),

                                ])

                        ])
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultsort('id', 'DESC')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('client_id')
                    ->label('Client')
                    ->numeric()
                    ->formatStateUsing(function ($record) {
                        $client = client::find($record->client_id);
                        $clients = $client->toArray();
                        $clientname = $clients['firstname'] . "" . $clients['lastname'];
                        if ($client) {
                            return $clientname;
                        }

                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('receptionist_id')
                    ->label('Receptionist')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function ($record) {
                        $user = User::find($record->receptionist_id);
                        $users = $user->toArray();
                        $username = $users['name'];
                        if ($user) {
                            return $username;
                        }

                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('doctor_id')
                    ->formatStateUsing(function ($record) {
                        $user = User::find($record->doctor_id);
                        $users = $user->toArray();
                        $username = $users['name'];
                        if ($user) {
                            return $username;
                        }

                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('type_of_consultations_id')
                    ->label('Srervice')
                    ->formatStateUsing(function ($record) {
                        $consultationtype = TypeOfConsultation::find($record->type_of_consultations_id);
                        $type = $consultationtype->toArray();
                        $consultationname = $type['type'];
                        if ($consultationtype) {
                            return $consultationname;
                        }

                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('insurance_id')
                    ->label('insurance')
                    ->numeric()
                    ->formatStateUsing(function ($record) {
                        $insurancetype = Insurance::find($record->insurance_id);
                        $insurance = $insurancetype->toArray();
                        $insurancename = $insurance['name'];
                        if ($insurancetype) {
                            return $insurancename;
                        }

                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('dates')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('prescription_status')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function ($record) {
                        if ($record->prescription_status == 1) {
                            return 'Given Glass';
                        } else {
                            return 'No Glass';
                        }
                    }),

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
                Tables\Actions\EditAction::make()
                    ->label('Consult')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\Action::make('BILL')
                    ->icon('heroicon-o-banknotes')
                    ->url(function ($record) {
                        $consultationid = $record->id;
                        $bill = bill::where('consultation_id', $consultationid)->first();
                        //dd($billid=$bill->id);
                        if ($bill) {
                            return url("admin/bills/{$bill->id}/edit");
                        }
                    }),
                Tables\Actions\Action::make('Prescr')
                    ->button()
                    ->icon('heroicon-o-arrow-down-tray')
                    //send the record in url as id to download
                    ->url(function ($record) {
                        return url("prescriptions/pdf/download?id={$record->id}");
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
            'index' => Pages\ListConsultations::route('/'),
            'create' => Pages\CreateConsultation::route('/create'),
            'edit' => Pages\EditConsultation::route('/{record}/edit'),
        ];
    }
}
