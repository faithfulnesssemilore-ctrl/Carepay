<?php

namespace App\Filament\Resources\UserLimitResource\Pages;

use App\Filament\Resources\UserLimitResource;
use Filament\Resources\Pages\ListRecords;

class ListUserLimits extends ListRecords
{
    protected static string $resource = UserLimitResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
