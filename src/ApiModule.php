<?php

declare(strict_types=1);

namespace Picsi\Sideolclient;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

use function mb_substr;

/**
 * @version 7.0.0
 * @author Khairu Aqsara Sudirman
 * */

trait ApiModule
{
    private string $url;
    private string $endpoint;
    /** @var array<string,string> */
    protected array $headers = [];

    public function __construct(string $baseUrl)
    {
        if (mb_substr($baseUrl, -1) === '/') {
            // remove the trailing slash as the endpoints start with a slash.
            $this->url = mb_substr($baseUrl, 0, -1);

            return;
        }

        $this->url = $baseUrl;
    }

    /**
     * Overrides the default UUID trace, or request ID, that identifies a
     * request in Sideol's logs.
     */
    public function trace(string $trace, string $header = 'Sideol-Trace'): self
    {
        $this->headers[$header] = $trace;

        return $this;
    }

    protected function request(string $method, ?StreamInterface $body = null): RequestInterface
    {
        $request = Psr17FactoryDiscovery::findRequestFactory()
            ->createRequest($method, $this->url . $this->endpoint);

        if ($body !== null) {
            $request->withBody($body);
        }

        foreach ($this->headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        return $request;
    }
}