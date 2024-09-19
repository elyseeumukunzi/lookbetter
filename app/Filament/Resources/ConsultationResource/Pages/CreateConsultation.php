<?php

namespace App\Filament\Resources\ConsultationResource\Pages;

use App\Filament\Resources\ConsultationResource;
use App\Models\Bill;
use App\Models\Client;
use App\Models\Consultation;
use App\Models\Insurance;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\TypeOfConsultation;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateConsultation extends CreateRecord
{
    protected static string $resource = ConsultationResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
{
    $data['receptionist_id'] = auth()->id();
    $data['payment_status'] = 1;

 
    return $data;
}
protected function getRedirectUrl(): string
{
    //return $this->getResource()::getUrl('index');
    return '../bills/create';
}
   protected function afterCreate()
   {    
    // $consultationid=Consultation::max('id');
    // $consultationinfo=Consultation::find($consultationid);
    // $consultationtype=TypeOfConsultation::find($consultationinfo->type_of_consultations_id);
    // $consultationcost=$consultationtype->cost;
    // $giveglasstatus=$consultationinfo->prescription_status;
    
    //     $products=Product::find(12);
    //     $productprice=$products->price;
    //     $totalbill=$consultationcost + $productprice;   

        // $bill=[
        //     'consultation_id' => $consultationid,
        //     'dates' => $consultationinfo->dates,
        //     'consultation_cost' => $consultationcost,
        //     'client_bill'=> '0',
        //     'insurance_bill' => '0',
        //     'total' => '0',
        //     'payment_method'=> '0',
        //     'payment_status' => '0'
        // ];
        // //bill::insert($bill);
        // $thisbill=bill::max('id');          

    
   }
}

