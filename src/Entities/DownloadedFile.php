<?php declare(strict_types=1);

namespace Chiiya\Common\Entities;

class DownloadedFile
{
    public function __construct(
        private string $path,
    ) {}

    public function getPath(): string
    {
        return $this->getPath();
    }

    public function getFileName(): string
    {
        $info = pathinfo($this->path);

        return $info['basename'];
    }

    public function delete(): void
    {
        unlink($this->path);
    }
}
