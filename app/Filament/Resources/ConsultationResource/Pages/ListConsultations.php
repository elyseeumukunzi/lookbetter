<?php

namespace App\Filament\Resources\ConsultationResource\Pages;

use App\Filament\Resources\ConsultationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConsultations extends ListRecords
{
    protected static string $resource = ConsultationResource::class;
    protected static ?string $title='Services';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('New service'),
        ];
    }
}
