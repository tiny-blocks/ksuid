<?php

namespace TinyBlocks\Ksuid;

use TinyBlocks\Encoder\Base62;
use TinyBlocks\Ksuid\Internal\Exceptions\InvalidKsuidForInspection;
use TinyBlocks\Ksuid\Internal\Payload;
use TinyBlocks\Ksuid\Internal\Timestamp;

final class Ksuid
{
    public const ENCODED_SIZE = 27;

    private function __construct(private readonly Payload $payload, private readonly Timestamp $timestamp)
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
        $encoded = Base62::encode(value: $this->getBytes());
        $padding = self::ENCODED_SIZE - strlen($encoded);

        if ($padding > 0) {
            $encoded = str_repeat('0', $padding) . $encoded;
        }

        return $encoded;
    }

    public function getBytes(): string
    {
        return pack('N', $this->timestamp->getValue()) . $this->payload->getValue();
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
