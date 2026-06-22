<?php

namespace App\Filament\Resources\ScheduledPaymentResource\Pages;

use App\Filament\Resources\ScheduledPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScheduledPayment extends EditRecord
{
    protected static string $resource = ScheduledPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
