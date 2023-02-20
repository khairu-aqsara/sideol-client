<?php

declare(strict_types=1);

namespace Picsi\Sideolclient\Modules;

/**
 * @version 7.0.0
 * @author Khairu Aqsara Sudirman
 * */

class ChromiumExtraScriptTag
{
    private string $src;

    public function __construct(string $src)
    {
        $this->src = $src;
    }

    public function getSrc(): string
    {
        return $this->src;
    }
}