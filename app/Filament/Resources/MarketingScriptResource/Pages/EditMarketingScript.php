<?php

namespace App\Filament\Resources\MarketingScriptResource\Pages;

use App\Filament\Resources\MarketingScriptResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketingScript extends EditRecord
{
    protected static string $resource = MarketingScriptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
