<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\Stock;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
        
    }

    protected function handleRecordCreation(array $data): Model
    {
        //dd($data);
        $productinfo=$data;
            $bulkinsertdata = [
                'category_id' => $productinfo['category_id'],
                'brand_id' => $productinfo['brand_id'],
                'productname' => $productinfo['productname'],
                'model' => $productinfo['model'],
                'price' => $productinfo['price'],
                'selling_unit' => $productinfo['selling_unit'],
                'status'  => $productinfo['status']
            ];
            
        
        Product::insert($bulkinsertdata);    
        $thisproduct=Product::max('id');
        $stockinfo = [
            'product_id' => $thisproduct,
            'invoice_id' => 1,
            'quantity' => $productinfo['Quantity'],
            'price' => $productinfo['price'],
            'status' => $productinfo['status']
        ];   
        Stock::create($stockinfo);
        return new Stock();
        
    }
}
