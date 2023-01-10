<?php declare(strict_types=1);

namespace Chiiya\Common\Services;

use Exception;
use Illuminate\Filesystem\Filesystem;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Symfony\Component\Console\Helper\ProgressBar;

class CodeService
{
    /**
     * Numbers only.
     *
     * @var string
     */
    final public const SET_NUMBERS = '1234567890';

    /**
     * Numbers and uppercase characters without similar looking ones (IJL1O0) and W (problematic character length).
     *
     * @var string
     */
    final public const SET_NUMBERS_AND_UPPERCASE = '23456789ABCDEFGHKMNPQRSTUVXYZ';

    /**
     * Alphanumeric characters without similar looking ones (IJL1O0) and W (problematic character length).
     *
     * @var string
     */
    final public const SET_ALPHANUMERIC = '23456789ABCDEFGHKMNPQRSTUVXYZabcdefghkmnpqrstuvxyz';
    protected array $existing = [];
    protected array $codes = [];

    public function __construct(
        protected CsvReader $reader,
        protected CsvWriter $writer,
        protected Filesystem $filesystem,
    ) {}

    /**
     * Import previously generated codes from CSV files into memory.
     *
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    public function import(string $path, ?ProgressBar $bar = null): void
    {
        $files = $this->filesystem->files($path);

        foreach ($files as $file) {
            $this->reader->open($file->getRealPath());

            foreach ($this->reader->rows() as $row) {
                $value = trim($row->getCellAtIndex(0)->getValue());
                $this->existing[$value] = $value;
            }
            $this->reader->close();
            $bar?->advance();
        }
    }

    /**
     * Generate $amount of codes from alphabet $characters using $pattern.
     *
     * @throws Exception
     */
    public function generate(
        int $amount,
        string $pattern = '####-####-####',
        string $characters = self::SET_NUMBERS_AND_UPPERCASE,
        ?ProgressBar $bar = null,
    ): void {
        $count = $amount;

        while ($count > 0) {
            $code = $this->generateOne($pattern, $characters);

            if (! isset($this->codes[$code]) && ! isset($this->existing[$code])) {
                $this->codes[$code] = true;
                --$count;
                $bar?->advance();
            }
        }

        $this->codes = array_keys($this->codes);
    }

    /**
     * Export generated codes to CSV files. Returns file paths.
     *
     * @throws IOException
     * @throws WriterNotOpenedException
     */
    public function export(string $path, int $perFile = 1000000, ?ProgressBar $bar = null): array
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $date = date('Ymdhi');
        $batch = 1;
        $count = 0;
        $file = "{$path}/codes-{$date}-{$batch}.csv";

        $this->writer->open($file);

        foreach ($this->codes as $code) {
            if ($count >= $perFile) {
                $this->writer->close();
                ++$batch;
                $count = 0;
                $file = "{$path}/codes-{$date}-{$batch}.csv";
                $this->writer->open($file);
            }

            $this->writer->write([$code]);
            $bar?->advance();
            ++$count;
        }

        $this->writer->close();

        return array_map(fn (int $batch) => "{$path}/codes-{$date}-{$batch}.csv", range(1, $batch));
    }

    /**
     * Get all generated codes.
     */
    public function getCodes(): array
    {
        return $this->codes;
    }

    /**
     * Get the amount of imported & generated codes.
     */
    public function count(): int
    {
        return count($this->codes);
    }

    /**
     * Merge existing codes into $codes array for importing.
     */
    public function mergeExisting(): void
    {
        $this->codes = array_merge($this->codes, $this->existing);
    }

    /**
     * Generate one random code with the configured pattern and character set.
     *
     * @throws Exception
     */
    public function generateOne(string $pattern, string $characters): string
    {
        return implode(
            '',
            array_map(fn (string $char) => $char === '#' ? $characters[random_int(
                0,
                mb_strlen($characters) - 1,
            )] : $char, mb_str_split($pattern)),
        );
    }
}
