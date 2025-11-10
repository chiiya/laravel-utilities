<?php declare(strict_types=1);

namespace Chiiya\Common\Services;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Writer\CSV\Options;
use OpenSpout\Writer\CSV\Writer;
use OpenSpout\Writer\Exception\WriterNotOpenedException;

class CsvWriter
{
    protected Writer $writer;

    /**
     * Open a CSV file for writing.
     *
     * @throws IOException
     */
    public function open(string $path, string $delimiter = ';'): void
    {
        $options = new Options;
        $options->FIELD_DELIMITER = $delimiter;
        $this->writer = new Writer($options);
        $this->writer->openToFile($path);
    }

    /**
     * Write a single row from array.
     *
     * @throws IOException
     * @throws WriterNotOpenedException
     */
    public function write(array|Row $data): void
    {
        $row = $data instanceof Row ? $data : Row::fromValues($data);
        $this->writer->addRow($row);
    }

    /**
     * Close writer after processing.
     */
    public function close(): void
    {
        $this->writer->close();
    }
}
