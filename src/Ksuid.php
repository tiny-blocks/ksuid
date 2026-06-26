<?php

declare(strict_types=1);

namespace TinyBlocks\Ksuid;

use TinyBlocks\Encoder\Base62;
use TinyBlocks\Ksuid\Exceptions\InvalidKsuidForInspection;
use TinyBlocks\Ksuid\Exceptions\InvalidPayloadSize;
use TinyBlocks\Ksuid\Internal\Payload;
use TinyBlocks\Ksuid\Internal\Timestamp;

/**
 * Globally unique, partially time-sortable identifier (KSUID).
 */
final readonly class Ksuid
{
    public const int ENCODED_SIZE = 27;

    private function __construct(private Payload $payload, private Timestamp $timestamp)
    {
    }

    /**
     * Creates a Ksuid from a raw payload and a timestamp.
     *
     * @param string $payload The raw payload bytes.
     * @param int $timestamp The timestamp in seconds since the Ksuid epoch.
     * @return Ksuid The created instance.
     * @throws InvalidPayloadSize If the payload does not have the exact required size.
     */
    public static function from(string $payload, int $timestamp): Ksuid
    {
        return new Ksuid(payload: Payload::from(value: $payload), timestamp: Timestamp::from(value: $timestamp));
    }

    /**
     * Creates a Ksuid from a random payload and the current time.
     *
     * @return Ksuid The created instance.
     */
    public static function random(): Ksuid
    {
        return new Ksuid(payload: Payload::random(), timestamp: Timestamp::fromCurrentTime());
    }

    /**
     * Creates a Ksuid from a Base62-encoded value.
     *
     * @param string $value The Base62-encoded Ksuid value.
     * @return Ksuid The created instance.
     * @throws InvalidPayloadSize If the decoded payload does not have the exact required size.
     */
    public static function fromPayload(string $value): Ksuid
    {
        return new Ksuid(payload: Payload::fromBytes(value: $value), timestamp: Timestamp::fromBytes(value: $value));
    }

    /**
     * Returns the components encoded in a Base62 Ksuid.
     *
     * @param string $ksuid The Base62-encoded Ksuid value.
     * @return array<string, int|string> The formatted time, payload, and timestamp.
     * @throws InvalidKsuidForInspection If the value is not a valid Ksuid.
     */
    public static function inspectFrom(string $ksuid): array
    {
        if (strlen($ksuid) !== self::ENCODED_SIZE) {
            throw new InvalidKsuidForInspection(ksuid: $ksuid);
        }

        $ksuid = Ksuid::fromPayload(value: $ksuid);

        return [
            'time'      => $ksuid->timestamp->toUnixTimeFormatted(),
            'payload'   => $ksuid->payload(),
            'timestamp' => $ksuid->timestamp()
        ];
    }

    /**
     * Creates a Ksuid from a timestamp with a random payload.
     *
     * @param int $value The timestamp in seconds since the Ksuid epoch.
     * @return Ksuid The created instance.
     */
    public static function fromTimestamp(int $value): Ksuid
    {
        return new Ksuid(payload: Payload::random(), timestamp: Timestamp::from(value: $value));
    }

    /**
     * Returns the raw byte representation of the Ksuid.
     *
     * @return string The raw bytes.
     */
    public function bytes(): string
    {
        return sprintf('%s%s', pack('N', $this->timestamp->getValue()), $this->payload->getValue());
    }

    /**
     * Returns the Base62-encoded Ksuid value.
     *
     * @return string The Base62-encoded value.
     */
    public function value(): string
    {
        $encoder = Base62::from(value: $this->bytes());
        $encoded = $encoder->encode();

        return str_pad($encoded, self::ENCODED_SIZE, '0', STR_PAD_LEFT);
    }

    /**
     * Returns the payload as a hexadecimal string.
     *
     * @return string The hexadecimal payload.
     */
    public function payload(): string
    {
        return bin2hex($this->payload->getValue());
    }

    /**
     * Returns the Unix time in seconds.
     *
     * @return int The Unix time.
     */
    public function unixTime(): int
    {
        return $this->timestamp->getUnixTime();
    }

    /**
     * Returns the Ksuid timestamp.
     *
     * @return int The timestamp in seconds since the Ksuid epoch.
     */
    public function timestamp(): int
    {
        return $this->timestamp->getValue();
    }
}
