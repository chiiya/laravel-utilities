<?php

namespace Chiiya\Common\Services;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\CSV\Writer;
use Box\Spout\Writer\Exception\WriterNotOpenedException;

class CsvWriter
{
    protected Writer $writer;

    /**
     * Open a CSV file for writing.
     *
     * @throws IOException
     */
    public function open(string $path): void
    {
        $this->writer = WriterEntityFactory::createCSVWriter();
        $this->writer->setFieldDelimiter(';');
        $this->writer->openToFile($path);
    }

    /**
     * Write a single row from array.
     *
     * @throws IOException
     * @throws WriterNotOpenedException
     */
    public function write(array $data): void
    {
        $rowFromValues = WriterEntityFactory::createRowFromArray($data);
        $this->writer->addRow($rowFromValues);
    }

    /**
     * Close writer after processing.
     */
    public function close(): void
    {
        $this->writer->close();
    }
}
