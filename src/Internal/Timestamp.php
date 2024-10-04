<?php

declare(strict_types=1);

namespace TinyBlocks\Ksuid\Internal;

use DateTime;
use DateTimeZone;
use TinyBlocks\Encoder\Base62;

final readonly class Timestamp
{
    public const EPOCH = 1400000000;

    private function __construct(private int $value)
    {
    }

    public static function from(int $value): Timestamp
    {
        return new Timestamp(value: $value);
    }

    public static function fromBytes(string $value): Timestamp
    {
        $decoder = Base62::from(value: $value);
        $bytes = $decoder->decode();
        $timestamp = substr($bytes, 0, -16);
        $timestamp = substr($timestamp, -4);
        $timestamp = (array)unpack('Nuint', $timestamp);

        return new Timestamp(value: $timestamp['uint']);
    }

    public static function fromAdjustedCurrentTime(): Timestamp
    {
        return new Timestamp(value: time() - self::EPOCH);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getUnixTime(): int
    {
        return $this->value + self::EPOCH;
    }

    public function toUnixTimeFormatted(): string
    {
        $timezone = new DateTimeZone(timezone: date_default_timezone_get());

        return (new DateTime())
            ->setTimezone(timezone: $timezone)
            ->setTimestamp(timestamp: $this->getUnixTime())
            ->format(format: 'Y-m-d H:i:s O T');
    }
}
