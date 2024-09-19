<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use App\Models\Bill;
use App\Models\Bill_info;
use App\Models\Consultation;
use App\Models\Insurance;
use App\Models\Stock;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBill extends CreateRecord
{
    protected static string $resource = BillResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
        
    }

    protected function handleRecordCreation(array $data): Model
    {
        //dd($data);
        $bill = [];
        $billinfo = [];
        $consultationid = $data['consultation_id'];
        $dates = $data['dates'];
        $consultationcost = $data['consultation_cost'];
        $paymentmethod = $data['payment_method'];
        $productinfo = $data['billinfo'];
        $totalproductscost = 0;
        foreach ($productinfo as $product) {
            $totalproductscost = $totalproductscost + $product['subtotal'];
        }
        $insuranceinfo = Insurance::find(Consultation::find($consultationid)->insurance_id);
        $clientbill = ($totalproductscost * $insuranceinfo->tm) / 100;
        $insurancebill = $totalproductscost - $clientbill;
        $totalclientbill = $clientbill + $consultationcost;
        $totalbill = $totalproductscost + $consultationcost;
        $bill = [
            'consultation_id' => $consultationid,
            'dates' => $dates,
            'consultation_cost' => $consultationcost,
            'product_cost' => $totalproductscost, //total product cost
            'client_bill' => $clientbill, //client bill
            'insurance_bill' => $insurancebill,//insurance total bill
            'total' => $totalbill, //totalbill
            'payment_method' => $paymentmethod,
            'payment_status' => 1
        ];
        bill::insert($bill);
        $billid=Bill::max('id');
        foreach ($productinfo as $product) {
            $productid=$product['product_id'];
            $quantity=$product['quantity'];
            $stockinfo=Stock::where('product_id', $productid)->first();
            $currentquantity=$stockinfo->quantity;
            $newquantity=$currentquantity - $quantity;
            $stockinfo->update([
                'quantity' => $newquantity
            ]);
            $billinfo = [
                'bill_id' => $billid,
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'subtotal' => $product['subtotal']
            ];
        }
        Bill_info::insert($billinfo);
        return new bill();

    }


}
