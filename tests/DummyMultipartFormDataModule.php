<?php

declare(strict_types=1);

namespace Picsi\Sideolclient\Test;

use Picsi\Sideolclient\MultipartFormDataModule;
use Psr\Http\Message\RequestInterface;

class DummyMultipartFormDataModule
{
    use MultipartFormDataModule;

    public function build(): RequestInterface
    {
        $this->endpoint = '/foo';

        return $this->request();
    }
}