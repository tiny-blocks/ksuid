<?php

declare(strict_types=1);

namespace TinyBlocks\Ksuid;

use TinyBlocks\Encoder\Base62;
use TinyBlocks\Ksuid\Internal\Exceptions\InvalidKsuidForInspection;
use TinyBlocks\Ksuid\Internal\Payload;
use TinyBlocks\Ksuid\Internal\Timestamp;

final readonly class Ksuid
{
    public const int ENCODED_SIZE = 27;

    private function __construct(private Payload $payload, private Timestamp $timestamp)
    {
    }

    public static function random(): Ksuid
    {
        return new Ksuid(payload: Payload::random(), timestamp: Timestamp::fromAdjustedCurrentTime());
    }

    public static function from(string $payload, int $timestamp): Ksuid
    {
        return new Ksuid(payload: Payload::from(value: $payload), timestamp: Timestamp::from(value: $timestamp));
    }

    public static function fromPayload(string $value): Ksuid
    {
        return new Ksuid(payload: Payload::fromBytes(value: $value), timestamp: Timestamp::fromBytes(value: $value));
    }

    public static function fromTimestamp(int $value): Ksuid
    {
        return new Ksuid(payload: Payload::random(), timestamp: Timestamp::from(value: $value));
    }

    public static function inspectFrom(string $ksuid): array
    {
        if (strlen($ksuid) !== self::ENCODED_SIZE) {
            throw new InvalidKsuidForInspection(ksuid: $ksuid);
        }

        $ksuid = self::fromPayload(value: $ksuid);

        return [
            'time'      => $ksuid->timestamp->toUnixTimeFormatted(),
            'payload'   => $ksuid->getPayload(),
            'timestamp' => $ksuid->getTimestamp()
        ];
    }

    public function getValue(): string
    {
        $encoder = Base62::from(value: $this->getBytes());
        $encoded = $encoder->encode();

        return str_pad($encoded, self::ENCODED_SIZE, '0', STR_PAD_LEFT);
    }

    public function getBytes(): string
    {
        return sprintf('%s%s', pack('N', $this->timestamp->getValue()), $this->payload->getValue());
    }

    public function getPayload(): string
    {
        return bin2hex($this->payload->getValue());
    }

    public function getUnixTime(): int
    {
        return $this->timestamp->getUnixTime();
    }

    public function getTimestamp(): int
    {
        return $this->timestamp->getValue();
    }
}
