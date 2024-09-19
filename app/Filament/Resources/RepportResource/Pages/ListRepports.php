<?php

namespace App\Filament\Resources\RepportResource\Pages;

use App\Filament\Resources\RepportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepports extends ListRecords
{
    protected static string $resource = RepportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
