<?php

namespace Chiiya\Common\Services;

use GuzzleHttp\Client;
use Illuminate\Contracts\Filesystem\Filesystem;

class FileDownloader
{
    protected Client $client;
    protected Filesystem $filesystem;

    /**
     * FileDownloader constructor.
     */
    public function __construct(Client $client, Filesystem $filesystem)
    {
        $this->client = $client;
        $this->filesystem = $filesystem;
    }

    /**
     * Download file from given URL.
     */
    public function download(string $url): string
    {
        $info = pathinfo($url);
        $filename = $info['basename'];
        $this->createTemporaryFile($filename);
        $this->client->get($url, ['sink' => $this->getTemporaryFileLocation($filename)]);

        return $this->getTemporaryFileLocation($filename);
    }

    /**
     * Delete the temporary file.
     */
    public function deleteTemporaryFile(string $location): void
    {
        $this->filesystem->delete($location);
    }

    /**
     * Get the location for the temporary file that we're creating.
     */
    protected function getTemporaryFileLocation(string $filename): string
    {
        return storage_path('app/tmp').'/'.$filename;
    }

    /**
     * Create the temporary download file.
     */
    protected function createTemporaryFile(string $filename): void
    {
        fopen($this->getTemporaryFileLocation($filename), 'wb');
    }
}
