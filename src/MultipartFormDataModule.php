<?php

declare(strict_types=1);

namespace Picsi\Sideolclient;

use Picsi\Sideolclient\Exceptions\NativeFunctionError;
use GuzzleHttp\Psr7\MultipartStream;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

use function json_encode;

/**
 * @version 7.0.0
 * @author Khairu Aqsara Sudirman
 * */

trait MultipartFormDataModule
{
    use ApiModule;

    /** @var array<array<string,mixed>> */
    private array $multipartFormData = [];

    /**
     * Overrides the default UUID output filename.
     * Note: Sideol adds the file extension automatically; you don't have to
     * set it.
     */
    public function outputFilename(string $filename): self
    {
        $this->headers['Sideol-Output-Filename'] = $filename;

        return $this;
    }

    /**
     * Sets the callback and error callback that Sideol will use to send
     * respectively the output file and the error response.
     */
    public function webhook(string $url, string $errorUrl): self
    {
        $this->headers['Sideol-Webhook-Url']       = $url;
        $this->headers['Sideol-Webhook-Error-Url'] = $errorUrl;

        return $this;
    }

    /**
     * Overrides the default HTTP method that Sideol will use to call the
     * webhook.
     *
     * Either "POST", "PATCH", or "PUT" - default "POST".
     */
    public function webhookMethod(string $method): self
    {
        $this->headers['Sideol-Webhook-Method'] = $method;

        return $this;
    }

    /**
     * Overrides the default HTTP method that Sideol will use to call the
     * error webhook.
     *
     * Either "POST", "PATCH", or "PUT" - default "POST".
     */
    public function webhookErrorMethod(string $method): self
    {
        $this->headers['Sideol-Webhook-Error-Method'] = $method;

        return $this;
    }

    /**
     * Sets the extra HTTP headers that Sideol will send alongside the
     * request to the webhook and error webhook.
     *
     * @param array<string,string> $headers
     *
     * @throws NativeFunctionError
     */
    public function webhookExtraHttpHeaders(array $headers): self
    {
        $json = json_encode($headers);
        if ($json === false) {
            throw NativeFunctionError::createFromLastPhpError();
        }

        $this->headers['Sideol-Webhook-Extra-Http-Headers'] = $json;

        return $this;
    }

    /**
     * @param mixed $value
     */
    protected function formValue(string $name, $value): void
    {
        $this->multipartFormData[] = [
            'name' => $name,
            'contents' => $value,
        ];
    }

    protected function formFile(string $filename, StreamInterface $stream): void
    {
        $this->multipartFormData[] = [
            'name' => 'files',
            'filename' => $filename,
            'contents' => $stream,
        ];
    }

    protected function request(string $method = 'POST'): RequestInterface
    {
        $body = new MultipartStream($this->multipartFormData);

        $request = Psr17FactoryDiscovery::findRequestFactory()
            ->createRequest($method, $this->url . $this->endpoint)
            ->withHeader('Content-Type', 'multipart/form-data; boundary="' . $body->getBoundary() . '"')
            ->withBody($body);

        foreach ($this->headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        return $request;
    }
}