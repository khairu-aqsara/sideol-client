<?php 

declare(strict_types=1);

namespace Picsi\Sideolclient;

use Picsi\Sideolclient\Exceptions\NativeFunctionError;

use function hrtime;
use function is_numeric;

/**
 * @version 7.0.0
 * @author Khairu Aqsara Sudirman
 * */

final class ExecutionTime implements Index
{
    public function create(): string
    {
        $index = hrtime(true);
        if (! is_numeric($index)) {
            throw NativeFunctionError::createFromLastPhpError();
        }

        return $index . '';
    }
}