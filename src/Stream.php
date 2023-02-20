<?php

declare(strict_types=1);

namespace Picsi\Sideolclient;

use Picsi\Sideolclient\Exceptions\NativeFunctionError;
use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

use function basename;
use function fopen;
use function fwrite;

/**
 * @version 7.0.0
 * @author Khairu Aqsara Sudirman
 * */

class Stream
{
    private string $filename;
    private StreamInterface $stream;

    public static function path(string $path, ?string $filename = null): self
    {
        $filename ??= basename($path);

        return new self($filename, new LazyOpenStream($path, 'r'));
    }

    public static function string(string $filename, string $str): self
    {
        $inmemory = fopen('php://memory', 'rb+');

        if ($inmemory === false) {
            throw NativeFunctionError::createFromLastPhpError();
        }

        if (fwrite($inmemory, $str) === false) {
            throw NativeFunctionError::createFromLastPhpError();
        }

        return new self($filename, Utils::streamFor($inmemory));
    }

    public function __construct(string $filename, StreamInterface $stream)
    {
        $this->filename = $filename;
        $this->stream   = $stream;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getStream(): StreamInterface
    {
        return $this->stream;
    }
}