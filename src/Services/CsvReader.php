<?php

namespace Chiiya\Common\Services;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\CSV\Reader;
use Box\Spout\Reader\CSV\SheetIterator;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;

class CsvReader
{
    /** @var Reader */
    protected $reader;

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
     * Close the file after processing.
     */
    public function close(): void
    {
        $this->reader->close();
    }
}
