<?php

namespace App\Filament\Resources\ScheduledPaymentResource\Pages;

use App\Filament\Resources\ScheduledPaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListScheduledPayments extends ListRecords
{
    protected static string $resource = ScheduledPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // \Filament\Actions\CreateAction::make(),
        ];
    }
}
