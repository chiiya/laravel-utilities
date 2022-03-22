<?php declare(strict_types=1);

namespace Chiiya\Common\Services;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\CSV\Reader;
use Box\Spout\Reader\CSV\RowIterator;
use Box\Spout\Reader\CSV\SheetIterator;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;

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
        $this->reader = ReaderEntityFactory::createCSVReader();
        $this->reader->setFieldDelimiter($delimiter);
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
