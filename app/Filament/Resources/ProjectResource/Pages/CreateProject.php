<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
{
    if (isset($data['due_date'])) {
        $data['due_date'] = Carbon::parse($data['due_date'])->format('Y-m-d');
    }
    
    return $data;
}
}
