<?php

declare(strict_types=1);

use Picsi\Sideolclient\Exceptions\SideolApiError;
use Picsi\Sideolclient\Exceptions\NoOutputFileInResponse;
use Picsi\Sideolclient\Sideol;
use Picsi\Sideolclient\Test\DummyClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

it(
    'sends a request',
    function (): void {
        $response = new Response(200, ['Sideol-Trace' => 'debug']);
        $client   = new DummyClient($response);

        $response = Sideol::send(new Request('POST', 'https://my.url'), $client);

        expect($response)->not()->toBeNull();
    }
);

it(
    'sends a request and throws an exception if response is not 2xx',
    function (bool $withTrace): void {
        $response = new Response(400, $withTrace ? ['Sideol-Trace' => 'debug'] : [], 'Bad Request');
        $client   = new DummyClient($response);

        try {
            Sideol::send(new Request('POST', 'https://my.url'), $client);
        } catch (SideolApiError $e) {
            expect($e->getCode())->toEqual(400);
            expect($e->getMessage())->toEqual('Bad Request');
            expect($e->getSideolTrace())->toEqual($withTrace ? 'debug' : '');
            expect($e->getResponse())->toBe($response);

            throw $e;
        }
    }
)->with([
    true,
    false,
])->throws(SideolApiError::class);

it(
    'saves the output file',
    function (): void {
        $response = new Response(200, ['Content-Disposition' => 'attachment; filename=my.pdf']);
        $client   = new DummyClient($response);

        $filename = Sideol::save(new Request('POST', 'https://my.url'), sys_get_temp_dir(), $client);

        expect(unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'my.pdf'))->toBeTrue();
        expect($filename)->toEqual('my.pdf');
    }
);

it(
    'throws an exception if there is no attachment',
    function (?string $contentDisposition): void {
        $response = new Response(200, $contentDisposition === null ? [] : ['Content-Disposition' => $contentDisposition]);
        $client   = new DummyClient($response);

        Sideol::save(new Request('POST', 'https://my.url'), sys_get_temp_dir(), $client);
    }
)->with([
    null,
    'no attachment',
])->throws(NoOutputFileInResponse::class);