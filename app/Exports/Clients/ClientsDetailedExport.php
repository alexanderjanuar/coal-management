<?php
// Updated: app/Exports/Clients/ClientsDetailedExport.php

namespace App\Exports\Clients;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Clients\Sheets\ClientsMainSheet;
use App\Exports\Clients\Sheets\ClientsContractsSheet;
use App\Exports\Clients\Sheets\ClientsPICSheet;
use App\Exports\Clients\Sheets\ClientsCoreTaxSheet;

class ClientsDetailedExport implements WithMultipleSheets
{
    protected $filters;
    protected $selectedIds;

    public function __construct($filters = [], $selectedIds = null)
    {
        $this->filters = $filters;
        $this->selectedIds = $selectedIds;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new ClientsMainSheet($this->filters, $this->selectedIds),
            new ClientsContractsSheet($this->filters, $this->selectedIds),
            new ClientsPICSheet($this->filters, $this->selectedIds),
            new ClientsCoreTaxSheet($this->filters, $this->selectedIds),
        ];
    }
}