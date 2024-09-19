<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    public function mutateFormDataBeforeCreate(array $data) : array
    {
        $data['createdby']=auth()->user()->id;
        return $data;
    }
}
