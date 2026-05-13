<?php

namespace App\Filament\Resources\MarketingScriptResource\Pages;

use App\Filament\Resources\MarketingScriptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingScripts extends ListRecords
{
    protected static string $resource = MarketingScriptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
