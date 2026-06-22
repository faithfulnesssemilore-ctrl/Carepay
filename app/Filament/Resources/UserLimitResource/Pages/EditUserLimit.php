<?php

namespace App\Filament\Resources\UserLimitResource\Pages;

use App\Filament\Resources\UserLimitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserLimit extends EditRecord
{
    protected static string $resource = UserLimitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
