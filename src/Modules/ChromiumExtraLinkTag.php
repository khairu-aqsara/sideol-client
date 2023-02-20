<?php

declare(strict_types=1);

namespace Picsi\Sideolclient\Modules;

/**
 * @version 7.0.0
 * @author Khairu Aqsara Sudirman
 * */

class ChromiumExtraLinkTag
{
    private string $href;

    public function __construct(string $href)
    {
        $this->href = $href;
    }

    public function getHref(): string
    {
        return $this->href;
    }
}