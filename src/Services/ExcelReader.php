<?php declare(strict_types=1);

namespace Chiiya\Common\Services;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Box\Spout\Reader\XLSX\Reader;
use Box\Spout\Reader\XLSX\SheetIterator;
use Iterator;

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
        $this->reader = ReaderEntityFactory::createXLSXReader();
        $this->reader->setTempFolder(storage_path('app/tmp'));
        $this->reader->open($path);
    }

    /**
     * Get the sheet iterator.
     *
     * @throws ReaderNotOpenedException
     */
    public function getSheetIterator(): Iterator|SheetIterator
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
