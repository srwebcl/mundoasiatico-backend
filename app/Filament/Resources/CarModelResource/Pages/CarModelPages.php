<?php

namespace App\Filament\Resources\CarModelResource\Pages;

use App\Filament\Resources\CarModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;

class ListCarModels extends ListRecords
{
    protected static string $resource = CarModelResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}

class CreateCarModel extends CreateRecord
{
    protected static string $resource = CarModelResource::class;
}

class EditCarModel extends EditRecord
{
    protected static string $resource = CarModelResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
