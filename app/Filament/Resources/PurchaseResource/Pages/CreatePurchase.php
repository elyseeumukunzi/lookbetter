<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Models\Stock;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;
    protected function getRedirectUrl(): string
    {
        //return $this->getResource()::getUrl('index');
        return '../stocks';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $productinfo = [];
        $products = $data['Products'];

        $invoiceid = $data['invoice_id'];

        foreach ($products as $product) {
            $bulkInsertData = [
                'invoice_id' => $invoiceid,
                'product_id' => $product['productid'],
                'quantity' => $product['quantity'],
                'purchase_price' => $product['purchase_price'],
                'selling_price' => $product['selling_price'],
            ];
            $productid = $product['productid'];

            $stockinfo = stock::where('product_id', $productid)->first();
            $currentquantity = $stockinfo->quantity;
            $newquantity = $currentquantity + $product['quantity'];
            //dd($currentquantity);
            //update stockinfo
            $stockinfo->update([
                'quantity' => $newquantity,
                'price' => $product['selling_price'],
            ]);
            Purchase::insert($bulkInsertData);
        }
        return new stock();

    }
}
