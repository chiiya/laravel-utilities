<?php

namespace Chiiya\Common\Repositories;

use Illuminate\Support\Facades\DB;

trait HasBulkInserts
{
    /**
     * Bulk insert aral codes.
     */
    public function bulkInsert(array $data): void
    {
        $values = '';
        foreach ($data as $row) {
            $attributes = implode(', ', array_map(function ($attribute) {
                if ($attribute === null) {
                    return 'null';
                }

                return is_string($attribute) ? "'".$attribute."'" : $attribute;
            }, $row));
            $values .= '('.$attributes.'), ';
        }
        $values = rtrim($values, ', ');

        $query = 'INSERT INTO '.$this->model->getTable().' ';
        $columns = '('.implode(', ', array_map(function ($column) {
            return '`'.$column.'`';
        }, $this->bulkInsertColumns())).')';
        $field = $this->bulkInsertColumns()[0];
        $query .= $columns.' VALUES '.$values.' ON DUPLICATE KEY UPDATE '.$field.'='.$field.';';

        DB::statement($query);
    }

    abstract protected function bulkInsertColumns(): array;
}
