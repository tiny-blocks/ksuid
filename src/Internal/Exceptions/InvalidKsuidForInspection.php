<?php

namespace TinyBlocks\Ksuid\Internal\Exceptions;

use RuntimeException;

final class InvalidKsuidForInspection extends RuntimeException
{
    public function __construct(private readonly string $ksuid)
    {
        $template = 'The KSUID <%s> is not valid for inspection.';
        parent::__construct(message: sprintf($template, $this->ksuid));
    }
}
