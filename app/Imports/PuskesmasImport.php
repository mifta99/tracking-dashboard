<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PuskesmasImport implements ToCollection, WithHeadingRow
{
    private $data = [];

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $this->data = $collection->toArray();
    }

    public function getData()
    {
        return $this->data;
    }
}
