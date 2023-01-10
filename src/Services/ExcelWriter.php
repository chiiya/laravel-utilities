<?php declare(strict_types=1);

namespace Chiiya\Common\Services;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Writer\Exception\InvalidSheetNameException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

class ExcelWriter
{
    protected Writer $writer;

    /**
     * Open a XLSX file for reading.
     *
     * @throws IOException
     */
    public function open(string $path): void
    {
        $options = new Options;
        $options->setTempFolder(storage_path('app/tmp'));
        $this->writer = new Writer($options);
        $this->writer->openToFile($path);
    }

    /**
     * Set name for current sheet.
     *
     * @param string $name *
     *
     * @throws InvalidSheetNameException
     * @throws WriterNotOpenedException
     */
    public function setCurrentSheetName(string $name): void
    {
        $sheet = $this->writer->getCurrentSheet();
        $sheet->setName($name);
    }

    /**
     * Add a new sheet and set name.
     *
     * @throws WriterNotOpenedException
     * @throws InvalidSheetNameException
     */
    public function addSheet(string $name): void
    {
        $sheet = $this->writer->addNewSheetAndMakeItCurrent();
        $sheet->setName($name);
    }

    /**
     * Add a styled header row.
     *
     * @throws IOException
     * @throws WriterNotOpenedException
     */
    public function addHeaderRow(Row|array $data): void
    {
        $style = (new Style)
            ->setFontBold()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::DARK_BLUE);
        $row = $data instanceof Row ? $data : Row::fromValues($data);
        $row->setStyle($style);
        $this->writer->addRow($row);
    }

    /**
     * Write a single row from array.
     *
     * @throws IOException
     * @throws WriterNotOpenedException
     */
    public function write(Row|array $data): void
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
