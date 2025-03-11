<?php declare(strict_types=1);

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
        $values = mb_rtrim($values, ', ');

        $query = 'INSERT INTO '.$this->instance->getTable().' ';
        $columns = '('.implode(', ', array_map(fn ($column) => '`'.$column.'`', $this->bulkInsertColumns())).')';
        $fields = implode(', ', array_map(fn ($column) => $column.'='.$column, $this->bulkInsertUpdatedColumns()));

        $query .= $columns.' VALUES '.$values.' ON DUPLICATE KEY UPDATE '.$fields.';';

        DB::statement($query);
    }

    abstract protected function bulkInsertColumns(): array;

    protected function bulkInsertUpdatedColumns(): array
    {
        return [$this->bulkInsertColumns()[0]];
    }
}
