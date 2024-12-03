<?php

declare(strict_types=1);

namespace TinyBlocks\Ksuid\Internal;

use TinyBlocks\Encoder\Base62;
use TinyBlocks\Ksuid\Internal\Exceptions\InvalidPayloadSize;

final readonly class Payload
{
    public const int PAYLOAD_BYTES = 16;

    private function __construct(private string $value)
    {
        $currentSize = strlen($value);

        if ($currentSize !== self::PAYLOAD_BYTES) {
            throw new InvalidPayloadSize(currentSize: $currentSize, payloadBytes: self::PAYLOAD_BYTES);
        }
    }

    public static function random(): Payload
    {
        return new Payload(value: random_bytes(self::PAYLOAD_BYTES));
    }

    public static function from(string $value): Payload
    {
        return new Payload(value: $value);
    }

    public static function fromBytes(string $value): Payload
    {
        $decoder = Base62::from(value: $value);
        $bytes = $decoder->decode();
        $payload = substr($bytes, -self::PAYLOAD_BYTES);

        return new Payload(value: $payload);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
