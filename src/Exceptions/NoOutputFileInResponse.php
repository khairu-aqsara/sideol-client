<?php

declare(strict_types=1);

namespace Picsi\Sideolclient\Exceptions;

use Exception;

/**
 * @version 7.0.0
 * @author Khairu Aqsara Sudirman
 * */

final class NoOutputFileInResponse extends Exception
{
    public function __construct()
    {
        parent::__construct('No file in the Sideol API response');
    }
}