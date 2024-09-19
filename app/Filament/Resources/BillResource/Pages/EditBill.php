<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use App\Models\Bill;
use App\Models\Bill_info;
use App\Models\Client;
use App\Models\Consultation;
use App\Models\Product;
use App\Models\Stock;
use Filament\Actions;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBill extends EditRecord
{
    protected static string $resource = BillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    //mount data 
    public function mount($record): void
    {
        parent::mount($record);  
        $bill=Bill::find($record);
        $consultationinfo=Consultation::find($bill->consultation_id);   
        $clientinfo=Client::find($consultationinfo->client_id);
        $fullname=$clientinfo->firstname." ".$clientinfo->lastname;
        $billinfos=Bill_info::where('bill_id',$record)->get()->toArray();
        $productsinfo = [];
        if(!empty($billinfos)) {
            foreach ($billinfos as $billinfo) {
                $productinfo= [ 
                    'product_id' => $billinfo['product_id'],
                    'stock' => '',
                    'quantity' => $billinfo['quantity'],
                    'price' => '',
                    'subtotal' => $billinfo['subtotal']                
                ];
            }

        }
        else{
            $productinfo = [] ;
        }
       
        //dd($consultationinfo);  
        $this->form->fill([
            'consultation_id' => $consultationinfo->id,
            'names' => $fullname,
            'dates' => $bill->dates,
            'consultation_cost' => $bill->consultation_cost,
            'billinfo' => $productinfo,
            // Add other fields as needed
        ]);
        //$this->record = $this->resolveRecord($record);

    }

    public function form(Form $form): Form
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


                        TextInput::make('names')
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

                        TextInput::make('consultation_cost')
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
                            ->relationship('billinfos')
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


                                TextInput::make('stock')
                                    ->disabled()
                                    ->live(),

                                TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->live()
                                    ->minvalue(1)
                                    ->maxvalue(fn($get) => $get('stock'))
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $selectedquantity = $state;
                                        $set('subtotal', $selectedquantity * $get('price'));
                                    }),


                               TextInput::make('price')
                                    ->required()
                                    ->live()
                                    ->maxLength(255),

                                TextInput::make('subtotal')
                                    ->required()
                                    ->live()
                                    ->maxLength(255),




                            ]),

                    ]),


                Select::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'momo' => 'Momo',
                        'bank' => 'Bank',
                    ])
                    ->required(),
            ]);
    }
   
}
