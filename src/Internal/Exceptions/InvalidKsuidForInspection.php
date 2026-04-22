<?php

declare(strict_types=1);

namespace TinyBlocks\Ksuid\Internal\Exceptions;

use RuntimeException;

final class InvalidKsuidForInspection extends RuntimeException
{
    public function __construct(string $ksuid)
    {
        $template = 'The KSUID <%s> is not valid for inspection.';

        parent::__construct(message: sprintf($template, $ksuid));
    }
}
