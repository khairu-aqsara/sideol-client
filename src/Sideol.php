<?php

declare(strict_types=1);

namespace Picsi\Sideolclient;

use Picsi\Sideolclient\Exceptions\SideolApiError;
use Picsi\Sideolclient\Exceptions\NativeFunctionError;
use Picsi\Sideolclient\Exceptions\NoOutputFileInResponse;
use Picsi\Sideolclient\Modules\Chromium;
use Picsi\Sideolclient\Modules\LibreOffice;
use Picsi\Sideolclient\Modules\PdfEngines;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function count;
use function fclose;
use function fopen;
use function fwrite;
use function preg_match;

use const DIRECTORY_SEPARATOR;

/**
 * @version 7.0.0
 * @author Khairu Aqsara Sudirman
 * */

class Sideol
{
    public static function chromium(string $baseUrl): Chromium
    {
        return new Chromium($baseUrl);
    }

    public static function libreOffice(string $baseUrl): LibreOffice
    {
        return new LibreOffice($baseUrl);
    }

    public static function pdfEngines(string $baseUrl): PdfEngines
    {
        return new PdfEngines($baseUrl);
    }

    /**
     * Sends a request to Sideol and throws an exception if the status code
     * is not 200.
     *
     * @throws SideolApiError
     */
    public static function send(RequestInterface $request, ?ClientInterface $client = null): ResponseInterface
    {
        $client   = $client ?: Psr18ClientDiscovery::find();
        $response = $client->sendRequest($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            throw SideolApiError::createFromResponse($response);
        }

        return $response;
    }

    /**
     * Handles a request to Sideol and saves the output file if any.
     * On success, returns the filename based on the 'Content-Disposition' header.
     *
     * @throws SideolApiError
     * @throws NoOutputFileInResponse
     * @throws NativeFunctionError
     */
    public static function save(RequestInterface $request, string $dirPath, ?ClientInterface $client = null): string
    {
        $response = self::send($request, $client);

        $contentDisposition = $response->getHeader('Content-Disposition');
        if (count($contentDisposition) === 0) {
            throw new NoOutputFileInResponse();
        }

        $filename = false;
        foreach ($contentDisposition as $value) {
            if (preg_match('/filename="(.+?)"/', $value, $matches)) {
                $filename = $matches[1];
                break;
            }

            if (preg_match('/filename=([^; ]+)/', $value, $matches)) {
                $filename = $matches[1];
                break;
            }
        }

        if ($filename === false) {
            throw new NoOutputFileInResponse();
        }

        $file = fopen($dirPath . DIRECTORY_SEPARATOR . $filename, 'w');
        if ($file === false) {
            throw NativeFunctionError::createFromLastPhpError();
        }

        if (fwrite($file, $response->getBody()->getContents()) === false) {
            throw NativeFunctionError::createFromLastPhpError();
        }

        if (fclose($file) === false) {
            throw NativeFunctionError::createFromLastPhpError();
        }

        return $filename;
    }
}