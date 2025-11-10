<?php declare(strict_types=1);

namespace Chiiya\Common\Services;

use Chiiya\Common\Exceptions\ZipperException;
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
     * Extract a zipped file.
     *
     * @throws ZipperException
     */
    public function unzip(string $location): string
    {
        $status = $this->zip->open($location);

        if ($status !== true) {
            throw new ZipperException('Could not open archive. ZIPARCHIVE-ERROR-CODE: '.$status);
        }

        $information = pathinfo($location);
        $location = $this->getTemporaryFileLocation($information['filename']);
        $this->zip->extractTo($location);
        $this->zip->close();

        return $location;
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

        if ($status !== true) {
            throw new ZipperException('Could not create archive. ZIPARCHIVE-ERROR-CODE: '.$status);
        }

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
        if (! file_exists($filePath)) {
            throw new ZipperException("File not found: {$filePath}");
        }

        if (! is_readable($filePath)) {
            throw new ZipperException("File not readable: {$filePath}");
        }

        $localName ??= basename($filePath);

        if (! $this->zip->addFile($filePath, $localName)) {
            throw new ZipperException("Failed to add file: {$filePath}");
        }

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
        if (! is_dir($directory)) {
            throw new ZipperException("Directory not found: {$directory}");
        }

        $realPath = realpath($directory);

        if ($realPath === false) {
            throw new ZipperException("Could not resolve directory: {$directory}");
        }

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

                if (! $this->zip->addFile($filePath, $relativePath)) {
                    throw new ZipperException("Failed to add file: {$filePath}");
                }
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
        if ($this->zipPath === null) {
            throw new ZipperException('No zip archive has been created.');
        }

        if (! $this->zip->close()) {
            throw new ZipperException('Failed to close zip archive.');
        }

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
     * Get the location for the temporary file that we're creating.
     */
    protected function getTemporaryFileLocation(string $filename): string
    {
        $path = mb_rtrim(config('utilities.tmp_path'), DIRECTORY_SEPARATOR);

        return $path.DIRECTORY_SEPARATOR.$filename;
    }
}
