<?php declare(strict_types=1);

namespace Chiiya\Common\Services;

use Chiiya\Common\Exceptions\ZipperException;
use ZipArchive;

class Zipper
{
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
     * Get the location for the temporary file that we're creating.
     */
    protected function getTemporaryFileLocation(string $filename): string
    {
        $path = rtrim(config('utilities.tmp_path'), DIRECTORY_SEPARATOR);

        return $path.DIRECTORY_SEPARATOR.$filename;
    }
}
