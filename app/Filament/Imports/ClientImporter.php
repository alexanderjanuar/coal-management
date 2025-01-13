<?php

namespace App\Filament\Imports;

use App\Models\Client;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ClientImporter extends Importer
{
    protected static ?string $model = Client::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['max:255']),
            ImportColumn::make('NPWP')
                ->requiredMapping()
                ->rules(['max:255']),
            ImportColumn::make('EFIN')
                ->requiredMapping()
                ->rules(['max:255']),
            ImportColumn::make('KPP')
                ->requiredMapping()
                ->rules(['max:255']),
            ImportColumn::make('logo')
                ->requiredMapping()
                ->rules(['max:255']),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules([]),
            ImportColumn::make('account_representative')
                ->rules(['max:255']),
            ImportColumn::make('person_in_charge')
                ->rules(['max:255']),
            ImportColumn::make('ar_phone_number')
                ->rules(['max:255']),
            ImportColumn::make('adress')
                ->rules(['max:255']),
            ImportColumn::make('email')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?Client
    {
        // return Client::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Client;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your client import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
