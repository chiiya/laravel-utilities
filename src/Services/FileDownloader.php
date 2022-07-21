<?php declare(strict_types=1);

namespace Chiiya\Common\Services;

use Chiiya\Common\Entities\DownloadedFile;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Filesystem\Filesystem;

class FileDownloader
{
    public function __construct(
        protected Client $client,
        protected Filesystem $filesystem,
    ) {}

    /**
     * Download file from given URL.
     */
    public function download(string $url): DownloadedFile
    {
        $info = pathinfo($url);
        $filename = $info['basename'];
        $path = $this->getTemporaryFileLocation($filename);
        $resource = Utils::tryFopen($path, 'w');
        $stream = Utils::streamFor($resource);
        $this->client->get($url, ['sink' => $stream]);

        return new DownloadedFile($path);
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
