<?php

namespace App\Filament\Resources\DraftPostResource\Pages;

use App\Filament\Resources\DraftPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDraftPost extends CreateRecord
{
    protected static string $resource = DraftPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['user_id'] = auth()->id();
        return $data;
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('create');
    }
}
