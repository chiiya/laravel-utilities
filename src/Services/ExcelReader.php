<?php declare(strict_types=1);

namespace Chiiya\Common\Services;

use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Reader\XLSX\SheetIterator;

class ExcelReader
{
    protected Reader $reader;

    /**
     * Open an XLSX file for reading.
     *
     * @throws IOException
     */
    public function open(string $path): void
    {
        $options = new Options;
        $options->setTempFolder(storage_path('app/tmp'));
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
     * Close the reader.
     */
    public function close(): void
    {
        $this->reader->close();
    }
}
