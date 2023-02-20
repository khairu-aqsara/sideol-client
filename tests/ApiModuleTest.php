<?php

declare(strict_types=1);

use Picsi\Sideolclient\Test\DummyApiModule;

it(
    'creates a valid request with a trace header',
    function (): void {
        $dummy   = new DummyApiModule('https://my.url/');
        $request = $dummy
            ->trace('debug')
            ->build();

        expect($dummy->getUrl())->toBe('https://my.url');
        expect($request->getHeader('Sideol-Trace'))->toMatchArray(['debug']);
    }
);