<?php

declare(strict_types=1);

namespace App\Support;


interface ExportHelperInterface
{
    public function getExportHeaders(): array;

    public function getExportData(): \Generator;
}
