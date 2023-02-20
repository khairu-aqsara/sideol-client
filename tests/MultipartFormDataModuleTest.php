<?php

declare(strict_types=1);

use Picsi\Sideolclient\Exceptions\NativeFunctionError;
use Picsi\Sideolclient\Test\DummyMultipartFormDataModule;

it(
    'creates a valid request with given HTTP headers',
    function (): void {
        $dummy   = new DummyMultipartFormDataModule('https://my.url/');
        $request = $dummy
            ->outputFilename('my_filename')
            ->webhook('https://my.webhook.url', 'https://my.webhook.error.url')
            ->webhookMethod('POST')
            ->webhookErrorMethod('PUT')
            ->webhookExtraHttpHeaders([
                'My-Webhook-Http-Header' => 'HTTP header content',
                'My-Second-Webhook-Http-Header' => 'Second HTTP header content',
            ])
            ->build();

        expect($request->getHeader('Sideol-Output-Filename'))->toMatchArray(['my_filename']);
        expect($request->getHeader('Sideol-Webhook-Url'))->toMatchArray(['https://my.webhook.url']);
        expect($request->getHeader('Sideol-Webhook-Error-Url'))->toMatchArray(['https://my.webhook.error.url']);
        expect($request->getHeader('Sideol-Webhook-Method'))->toMatchArray(['POST']);
        expect($request->getHeader('Sideol-Webhook-Error-Method'))->toMatchArray(['PUT']);

        $json = json_encode([
            'My-Webhook-Http-Header' => 'HTTP header content',
            'My-Second-Webhook-Http-Header' => 'Second HTTP header content',
        ]);
        if ($json === false) {
            throw NativeFunctionError::createFromLastPhpError();
        }

        expect($request->getHeader('Sideol-Webhook-Extra-Http-Headers'))->toMatchArray([$json]);
    }
);