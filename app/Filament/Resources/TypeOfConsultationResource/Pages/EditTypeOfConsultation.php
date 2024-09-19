<?php

namespace App\Filament\Resources\TypeOfConsultationResource\Pages;

use App\Filament\Resources\TypeOfConsultationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTypeOfConsultation extends EditRecord
{
    protected static string $resource = TypeOfConsultationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
