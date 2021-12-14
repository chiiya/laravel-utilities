<?php

namespace Chiiya\Common\Services;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Exception;

class CodeService
{
    /**
     * Numbers only.
     */
    public const PATTERN_NUMBERS = '1234567890';

    /**
     * Numbers and uppercase characters without similar looking ones (IJL1O0) and W (problematic character length)
     */
    public const PATTERN_NUMBERS_AND_UPPERCASE = '23456789ABCDEFGHKMNPQRSTUVXYZ';

    /**
     * Alphanumeric characters without similar looking ones (IJL1O0) and W (problematic character length)
     */
    public const PATTERN_ALPHANUMERIC = '23456789ABCDEFGHKMNPQRSTUVXYZabcdefghkmnpqrstuvxyz';

    /** @var string[] */
    protected array $codes = [];
    protected CsvWriter $writer;

    public function __construct(CsvWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * Generate $amount of codes from alphabet $characters using $pattern.
     *
     * @throws Exception
     */
    public function generate(
        int $amount,
        string $pattern = '####-####-####',
        string $characters = self::PATTERN_NUMBERS_AND_UPPERCASE
    ): array {
        $codes = [];
        $count = $amount;

        while ($count > 0) {
            $code = $this->generateOne($pattern, $characters);
            if (isset($codes[$code]) === false) {
                $codes[$code] = true;
                $count--;
            }
        }

        $this->codes = array_keys($codes);

        return $this->codes;
    }

    /**
     * Export generated codes to CSV files. Returns file paths.
     *
     * @throws IOException
     * @throws WriterNotOpenedException
     */
    public function export(string $path, int $perFile = 1000000): array
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $date = date('Ymdhi');
        $batch = 1;
        $file = "{$path}/codes-{$date}-{$batch}.csv";

        $this->writer->open($file);

        foreach ($this->codes as $i => $code) {
            if ($i >= $perFile) {
                $this->writer->close();
                $batch++;
                $file = "{$path}/codes-{$date}-{$batch}.csv";
                $this->writer->open($file);
            }

            $this->writer->write([$code]);
        }

        $this->writer->close();

        return array_map(fn (int $batch) =>  "{$path}/codes-{$date}-{$batch}.csv", range(1, $batch));
    }

    /**
     * Get all generated codes.
     */
    public function getCodes(): array
    {
        return $this->codes;
    }

    /**
     * Generate one random code with the configured pattern and character set.
     *
     * @throws Exception
     */
    protected function generateOne(string $pattern, string $characters): string
    {
        return implode('', array_map(function (string $char) use ($characters) {
            return $char === '#' ? $characters[random_int(0, strlen($characters) - 1)] : $char;
        }, str_split($pattern)));
    }
}
