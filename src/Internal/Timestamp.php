<?php

namespace TinyBlocks\Ksuid\Internal;

use TinyBlocks\Encoder\Base62;

final class Timestamp
{
    public const EPOCH = 1400000000;

    private function __construct(private readonly int $value)
    {
    }

    public static function from(int $value): Timestamp
    {
        return new Timestamp(value: $value);
    }

    public static function fromBytes(string $value): Timestamp
    {
        $bytes = Base62::decode(value: $value);
        $timestamp = substr($bytes, 0, -16);
        $timestamp = substr($timestamp, -4);
        $timestamp = (array)unpack("Nuint", $timestamp);

        return new Timestamp(value: $timestamp["uint"]);
    }

    public static function fromAdjustedCurrentTime(): Timestamp
    {
        return new Timestamp(value: time() - self::EPOCH);
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
