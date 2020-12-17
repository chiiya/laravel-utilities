<?php

namespace Chiiya\Common\Services;

use Chiiya\Common\Exceptions\ZipperException;
use ZipArchive;

class Zipper
{
    /** @var ZipArchive */
    protected $zip;

    /**
     * Zipper constructor.
     */
    public function __construct(ZipArchive $zip)
    {
        $this->zip = $zip;
    }

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
        return storage_path('app/tmp').'/'.$filename;
    }
}
