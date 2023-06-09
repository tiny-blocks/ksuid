<?php

namespace TinyBlocks\Ksuid\Internal;

use DateTime;
use TinyBlocks\Encoder\Base62;

final class Timestamp
{
    public const EPOCH = 1400000000;

    private readonly int $time;

    private function __construct(private readonly int $value, private readonly int $epoch)
    {
        $this->time = $this->value - $this->epoch;
    }

    public static function from(int $value): Timestamp
    {
        return new Timestamp(value: $value, epoch: 0);
    }

    public static function fromBytes(string $value): Timestamp
    {
        $bytes = Base62::decode(value: $value);
        $timestamp = substr($bytes, 0, -16);
        $timestamp = substr($timestamp, -4);
        $timestamp = (array)unpack("Nuint", $timestamp);

        return new Timestamp(value: $timestamp["uint"], epoch: 0);
    }

    public static function fromAdjustedCurrentTime(): Timestamp
    {
        return new Timestamp(value: time(), epoch: self::EPOCH);
    }

    public static function format(int $timestamp): string
    {
        return (new DateTime("@$timestamp"))->format('Y-m-d H:i:s O T');
    }

    public function getValue(): int
    {
        return $this->time;
    }

    public function getUnixTime(): int
    {
        return $this->time + self::EPOCH;
    }
}
