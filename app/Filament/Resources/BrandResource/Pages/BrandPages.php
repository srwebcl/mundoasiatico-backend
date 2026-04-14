<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;

class ListBrands extends ListRecords
{
    protected static string $resource = BrandResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;
}

class EditBrand extends EditRecord
{
    protected static string $resource = BrandResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
