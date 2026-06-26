<?php

declare(strict_types=1);

namespace TinyBlocks\Ksuid\Exceptions;

use RuntimeException;

/**
 * Raised when a payload does not have the exact required size.
 */
final class InvalidPayloadSize extends RuntimeException
{
    public function __construct(int $currentSize, int $payloadBytes)
    {
        $template = 'Current length is <%s> bytes. Payload size must be exactly <%s> bytes.';

        parent::__construct(message: sprintf($template, $currentSize, $payloadBytes));
    }
}
