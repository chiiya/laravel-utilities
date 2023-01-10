<?php declare(strict_types=1);

namespace Chiiya\Common\Services;

use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\CSV\Options;
use OpenSpout\Reader\CSV\Reader;
use OpenSpout\Reader\CSV\RowIterator;
use OpenSpout\Reader\CSV\SheetIterator;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;

class CsvReader
{
    protected Reader $reader;

    /**
     * Open a CSV file for reading.
     *
     * @throws IOException
     */
    public function open(string $path, string $delimiter = ';'): void
    {
        $options = new Options;
        $options->FIELD_DELIMITER = $delimiter;
        $this->reader = new Reader($options);
        $this->reader->open($path);
    }

    /**
     * Get the sheet iterator.
     *
     * @throws ReaderNotOpenedException
     */
    public function getSheetIterator(): SheetIterator
    {
        return $this->reader->getSheetIterator();
    }

    /**
     * Get the row iterator.
     *
     * @throws ReaderNotOpenedException
     */
    public function rows(): RowIterator
    {
        return $this->getSheetIterator()->current()->getRowIterator();
    }

    /**
     * Close the file after processing.
     */
    public function close(): void
    {
        $this->reader->close();
    }
}
