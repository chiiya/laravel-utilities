<?php declare(strict_types=1);

namespace Chiiya\Common\Services;

use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Exception\InvalidSheetNameException;
use Box\Spout\Writer\Exception\WriterAlreadyOpenedException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Box\Spout\Writer\XLSX\Writer;

class ExcelWriter
{
    protected Writer $writer;

    /**
     * Open a XLSX file for reading.
     *
     * @throws IOException
     * @throws WriterAlreadyOpenedException
     */
    public function open(string $path): void
    {
        $this->writer = WriterEntityFactory::createXLSXWriter();
        $this->writer->setTempFolder(storage_path('app/tmp'));
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
    public function addHeaderRow(array $data): void
    {
        $style = (new StyleBuilder)
            ->setFontBold()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::DARK_BLUE)
            ->build();

        $row = WriterEntityFactory::createRowFromArray($data, $style);
        $this->writer->addRow($row);
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
