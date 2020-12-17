<?php

namespace Chiiya\Common\Services;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Box\Spout\Reader\XLSX\Reader;
use Box\Spout\Reader\XLSX\SheetIterator;

class ExcelReader
{
    /** @var Reader */
    protected $reader;

    /**
     * Open an XLSX file for reading.
     *
     * @throws IOException
     * @throws UnsupportedTypeException
     */
    public function open(string $path): void
    {
        $this->reader = ReaderEntityFactory::createReaderFromFile($path);
        $this->reader->setTempFolder(storage_path('app/tmp'));
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
}
