<?php

declare(strict_types=1);

namespace Picsi\Sideolclient\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;

/**
 * @version 7.0.0
 * @author Khairu Aqsara Sudirman
 * */

final class SideolApiError extends Exception
{
	private ResponseInterface $response;

	public static function createFromResponse(ResponseInterface $response): self
	{
		$exception = new self($response->getBody()->getContents(), $response->getStatusCode());
		$exception->response = $response;
		$exception->response->getBody()->rewind();

		return $exception;
	}

	public function getSideolTrace(string $header = 'Sideol-Trace'): string
	{
		return $this->response->getHeaderLine($header);
	}

	public function getResponse(): ResponseInterface
	{
		return $this->response;
	}
}