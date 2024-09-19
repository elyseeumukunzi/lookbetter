<?php

namespace App\Filament\Resources;

use App\Exports\ExportBill;
use App\Exports\ExportDailyRepport;
use App\Filament\Resources\BillResource\Pages;
use App\Filament\Resources\BillResource\RelationManagers;
use App\Models\Bill;
use App\Models\Bill_info;
use App\Models\Client;
use App\Models\Consultation;
use App\Models\Insurance;
use App\Models\Product;
use App\Models\TypeOfConsultation;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;


class BillResource extends Resource
{
    protected static ?string $model = Bill::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Service info')
                    ->columns(3)
                    ->schema([

                        // select::make('consultation_id')
                        //     ->label('Client')
                        //     ->options([
                        //         '1' => 'UMUKUNZI Elysee',
                        //         '2' => 'Nkundiye Ildephonse',
                        //         '3' => 'Kamana Yve'
                        //     ]),

                        Select::make('consultation_id')
                            ->options(function () {
                                $consultations = consultation::all()->sortByDesc('id');
                                $consultationinfo = [];
                                foreach ($consultations as $consultation) {
                                    $clientid = $consultation->client_id;
                                    $clientinfo = client::find($clientid);
                                    $fullname = $clientinfo->firstname . " " . $clientinfo->lastname;
                                    $consultationinfo[$consultation->id] = $fullname . ' (' . $consultation->dates . ')';

                                }
                                return $consultationinfo;
                            })
                            ->default(consultation::max('id'))

                            ->required()
                            ->afterStateUpdated(function ($state, $set) {
                                $consultationid = $state;
                                $consultationinfo = Consultation::find($consultationid);
                                $clientinfo = Client::find($consultationinfo->client_id);
                                $fullname = $clientinfo->firstname . " " . $clientinfo->lastname;

                                $set('names', $fullname);
                                $set('dates', $consultationinfo->dates);
                            })
                            ->live(),


                        Forms\Components\TextInput::make('names')
                            ->live()
                            ->disabled()
                            ->maxLength(255),
                        DatePicker::make('dates')
                            ->live()
                            ->default(now()),


                    ]),


                Section::make("Service cost")
                    ->description('Set consultation costs inf any')
                    ->hidden(fn($get): bool => !$get('consultation_id'))
                    ->schema([

                        Forms\Components\TextInput::make('consultation_cost')
                            ->label('Service cost')
                            ->required()
                            ->maxLength(255)
                            ->default(5000),

                    ]),


                Section::make('Products')
                    ->description('Select all product and consumables to be paid within their respective prices')
                    ->schema([
                        Repeater::make('billinfo')
                            ->columns(3)
                            ->schema([
                                Select::make('product_id')
                                    ->options(Product::all()->pluck('productname', 'id'))
                                    ->required()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $productid = $state;
                                        //$from = $get('from');
                                        //$billinfos = billinfo:: where('billid', $record->id)->get();                                            
                                        $products = Stock::where('product_id', $productid)->first();
                                        $set('stock', $products->quantity);
                                        $set('price', $products->price);

                                    })
                                    ->live(),


                                Forms\Components\TextInput::make('stock')
                                    ->disabled()
                                    ->live(),

                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->live()
                                    ->minvalue(1)
                                    ->maxvalue(fn($get) => $get('stock'))
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $selectedquantity = $state;
                                        $set('subtotal', $selectedquantity * $get('price'));
                                    }),


                                Forms\Components\TextInput::make('price')
                                    ->required()
                                    ->live()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('subtotal')
                                    ->required()
                                    ->live()
                                    ->maxLength(255),




                            ]),

                    ]),


                select::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'momo' => 'Momo',
                        'bank' => 'Bank',
                    ])
                    ->required()
                    ->default('cash'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->formatStateUsing(
                        function ($record) {
                            return 'LKBT/' . $record->id;
                        }
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('consultation_id')
                    ->numeric()
                    ->formatStateUsing(
                        function ($record) {
                            // Retrieve the client name based on the client ID
                            $consultationinfo = Consultation::find($record->consultation_id);
                            $patientid = $consultationinfo->client_id;
                            $patientinfo = Client::find($patientid);
                            $name = $patientinfo->firstname . " " . $patientinfo->lastname;

                            // Check if the client exists
                            if ($patientinfo) {
                                return $name;
                            }
                        }
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('dates')
                    ->label('Dates'),

                Tables\Columns\TextColumn::make('insurance_bill')
                    ->label('Total')
                    ->formatStateUsing(
                        function ($record) {
                            // Retrieve the client name based on the client ID
                            $consultationid = $record->consultation_id;
                            $consultationinfo = Consultation::find($consultationid);
                            $billinfo = Bill::where('consultation_id', $consultationid)->first();
                            $billid = $billinfo['id'];
                            $billproducts = Bill_info::where('bill_id', $billid)->get();
                            $productsbill = 0;
                            //dd($billproducts->toArray());
                            foreach ($billproducts as $products) {
                                $productsbill = $productsbill + $products['subtotal'];
                            }

                            if ($billproducts) {
                                return $productsbill + $billinfo['consultation_cost'];
                            }
                        }
                    )
                    ->searchable(),
                Tables\Columns\TextColumn::make('consultation_cost')
                    ->label('Service Cost')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product_cost')
                    ->searchable()
                    ->formatStateUsing(
                        function ($record) {
                            // Retrieve the client name based on the client ID
                            $consultationid = $record->consultation_id;
                            $consultationinfo = Consultation::find($consultationid);
                            $billinfo = Bill::where('consultation_id', $consultationid)->first();
                            $billid = $billinfo['id'];
                            $billproducts = Bill_info::where('bill_id', $billid)->get();
                            $productsbill = 0;
                            //dd($billproducts->toArray());
                            foreach ($billproducts as $products) {
                                $productsbill = $productsbill + $products['subtotal'];
                            }


                            if ($billproducts) {

                                return $productsbill;
                            }
                        }
                    ),


                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Insurance')
                    ->searchable()
                    ->formatStateUsing(
                        function ($record) {
                            // Retrieve the client name based on the client ID
                            $consultationid = $record->consultation_id;
                            $consultationinfo = Consultation::find($consultationid);
                            $billinfo = Bill::where('consultation_id', $consultationid)->first();
                            $billid = $billinfo['id'];
                            $billproducts = Bill_info::where('bill_id', $billid)->get();
                            $productsbill = 0;
                            //dd($billproducts->toArray());
                            foreach ($billproducts as $products) {

                                $productsbill = $productsbill + $products['subtotal'];


                            }

                            if ($billproducts) {
                                //consultation is not paid by any insurance, consultation is always private cz this is not a clinic
                                $totalbill = $productsbill + 0;
                                $insuranceid = $consultationinfo->insurance_id;
                                $insurancetm = Insurance::find($insuranceid)->tm;
                                $clientbill = ($totalbill * $insurancetm) / 100;
                                return $insurancebill = $totalbill - $clientbill;


                            }
                        }
                    ),

                Tables\Columns\BadgeColumn::make('client_bill')
                    ->label('Client')
                    ->searchable()
                    ->formatStateUsing(
                        function ($record) {
                            // Retrieve the client name based on the client ID
                            $consultationid = $record->consultation_id;
                            $consultationinfo = Consultation::find($consultationid);
                            $billinfo = Bill::where('consultation_id', $consultationid)->first();
                            $billid = $billinfo['id'];
                            $billproducts = Bill_info::where('bill_id', $billid)->get();
                            $productsbill = 0;
                            //dd($billproducts->toArray());
                            foreach ($billproducts as $products) {

                                $productsbill = $productsbill + $products['subtotal'];


                            }

                            if ($billproducts) {
                                //consultation is not paid by insurance
                                $totalbill = $productsbill + 0;
                                $insuranceid = $consultationinfo->insurance_id;
                                $insurancetm = Insurance::find($insuranceid)->tm;
                                $clientbill = ($totalbill * $insurancetm) / 100;
                                $totalclientbill = $clientbill + $billinfo['consultation_cost'];
                                return $totalclientbill . " RWF";
                            }
                        }
                    ),



            ])
            ->defaultSort('dates', 'DESC')

            ->filters([
                Filter::make('Date Range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->live()->default(today()),
                        Forms\Components\DatePicker::make('created_until')->live()->default(today()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {

                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $data): Builder => $query->whereDate('dates', '>=', $data),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $data): Builder => $query->whereDate('dates', '<=', $data),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button()
                    ->icon('heroicon-s-banknotes')
                    ->label('Bill'),

                Tables\Actions\Action::make('Prescr')
                    ->label('Facture')
                    ->button()
                    ->icon('heroicon-o-arrow-down-tray')
                    //send the record in url as id to download
                    ->url(function ($record) {
                        return url("bill/pdf/download?id={$record->id}");
                    })
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('Repport')
                        ->icon('heroicon-o-arrow-down-tray')
                        //lets send the selected rows in the url
                        ->action(function ($records) {
                            //$ids=$records->pluck('id')->toArray();
                            return Excel::download(new ExportBill($records->toArray()), 'bills.xlsx');
                        }),

                    Tables\Actions\BulkAction::make('Daily Repport')
                        ->button()
                        ->icon('heroicon-o-arrow-down-tray')
                        //lets send the selected rows in the url
                        ->action(function ($records) {
                            return Excel::download(new ExportDailyRepport($records->toArray()), 'bills.xlsx');
                        }),

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
            'index' => Pages\ListBills::route('/'),
            'create' => Pages\CreateBill::route('/create'),
            'edit' => Pages\EditBill::route('/{record}/edit'),
        ];
    }
}
