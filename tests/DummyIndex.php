<?php

declare(strict_types=1);

namespace Picsi\Sideolclient\Test;

use Picsi\Sideolclient\Index;

final class DummyIndex implements Index
{
    public function create(): string
    {
        return 'foo';
    }
}