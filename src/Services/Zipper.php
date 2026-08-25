<?php declare(strict_types=1);

namespace Chiiya\Common\Services;

use Chiiya\Common\Exceptions\ZipperException;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class Zipper
{
    protected ?string $zipPath = null;

    public function __construct(
        protected ZipArchive $zip,
    ) {}

    /**
     * Open a zip archive.
     */
    public function open(string $path): self
    {
        $this->openZipFile($path);
        $this->zipPath = $path;

        return $this;
    }

    /**
     * Extract a zipped file.
     *
     * @throws ZipperException
     */
    public function unzip(?string $path = null): string
    {
        if ($path !== null) {
            $this->open($path);
        }

        throw_if($this->zipPath === null, ZipperException::class, 'Missing path to zip archive.');

        $information = pathinfo($this->zipPath);
        $path = $this->getTemporaryFileLocation($information['filename']);
        $this->zip->extractTo($path);
        $this->zip->close();

        return $path;
    }

    /**
     * Create a new zip archive at the specified location.
     *
     * @throws ZipperException
     */
    public function create(string $filename): self
    {
        $this->zipPath = $this->getTemporaryFileLocation($filename);

        $status = $this->zip->open($this->zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        throw_if($status !== true, ZipperException::class, 'Could not create archive. ZIPARCHIVE-ERROR-CODE: '.$status);

        return $this;
    }

    /**
     * Add a single file to the zip archive.
     * Uses streams internally for memory efficiency.
     *
     * @param string $filePath Path to the file on disk
     * @param string|null $localName Name to use inside the zip (defaults to basename)
     *
     * @throws ZipperException
     */
    public function addFile(string $filePath, ?string $localName = null): self
    {
        throw_unless(file_exists($filePath), ZipperException::class, "File not found: {$filePath}");

        throw_unless(is_readable($filePath), ZipperException::class, "File not readable: {$filePath}");

        $localName ??= basename($filePath);

        throw_unless(
            $this->zip->addFile($filePath, $localName),
            ZipperException::class,
            "Failed to add file: {$filePath}",
        );

        return $this;
    }

    /**
     * Add all files from a directory to the zip archive recursively.
     * Only adds files, preserving directory structure inside the zip.
     *
     * @param string $directory Path to the directory
     * @param string|null $baseInZip Base path inside the zip (optional)
     *
     * @throws ZipperException
     */
    public function addDirectory(string $directory, ?string $baseInZip = null): self
    {
        throw_unless(is_dir($directory), ZipperException::class, "Directory not found: {$directory}");

        $realPath = realpath($directory);

        throw_if($realPath === false, ZipperException::class, "Could not resolve directory: {$directory}");

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($realPath),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );

        foreach ($files as $file) {
            if (! $file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = mb_substr($filePath, mb_strlen($realPath) + 1);

                // Add base path if specified
                if ($baseInZip !== null) {
                    $relativePath = mb_rtrim($baseInZip, '/').'/'.mb_ltrim($relativePath, '/');
                }

                throw_unless(
                    $this->zip->addFile($filePath, $relativePath),
                    ZipperException::class,
                    "Failed to add file: {$filePath}",
                );
            }
        }

        return $this;
    }

    /**
     * Add multiple files to the zip archive.
     *
     * @param array<string, string> $files Array of [localName => filePath]
     *
     * @throws ZipperException
     */
    public function addFiles(array $files): self
    {
        foreach ($files as $localName => $filePath) {
            if (is_numeric($localName)) {
                // If no local name specified, use basename
                $this->addFile($filePath);
            } else {
                $this->addFile($filePath, $localName);
            }
        }

        return $this;
    }

    /**
     * Finalize and close the zip archive.
     *
     * @throws ZipperException
     */
    public function close(): string
    {
        throw_if($this->zipPath === null, ZipperException::class, 'No zip archive has been created.');

        throw_unless($this->zip->close(), ZipperException::class, 'Failed to close zip archive.');

        return $this->zipPath;
    }

    /**
     * Get the path to the created zip file.
     */
    public function getZipPath(): ?string
    {
        return $this->zipPath;
    }

    /**
     * Check whether zip archive is valid.
     */
    public function check(string $path): bool
    {
        try {
            $this->openZipFile($path, ZipArchive::CHECKCONS);
            $this->zipPath = $path;
            $this->close();
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * Get a list of files in archive.
     */
    public function listFiles(): array
    {
        $list = [];

        for ($i = 0; $i < $this->zip->numFiles; ++$i) {
            $name = $this->zip->getNameIndex($i);

            throw_if($name === false, ZipperException::class, 'Failed to list files.');

            $list[] = $name;
        }

        return $list;
    }

    /**
     * Get the location for the temporary file that we're creating.
     */
    protected function getTemporaryFileLocation(string $filename): string
    {
        $path = mb_rtrim(config('utilities.tmp_path'), DIRECTORY_SEPARATOR);

        return $path.DIRECTORY_SEPARATOR.$filename;
    }

    /**
     * Open a zip file.
     */
    protected function openZipFile(string $path, ?int $flags = null): self
    {
        $status = $flags !== null ? $this->zip->open($path, $flags) : $this->zip->open($path);

        throw_if($status !== true, ZipperException::class, 'Could not open archive. ZIPARCHIVE-ERROR-CODE: '.$status);

        return $this;
    }
}
