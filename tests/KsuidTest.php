<?php

declare(strict_types=1);

namespace Test\TinyBlocks\Ksuid;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Ksuid\Exceptions\InvalidKsuidForInspection;
use TinyBlocks\Ksuid\Exceptions\InvalidPayloadSize;
use TinyBlocks\Ksuid\Ksuid;

final class KsuidTest extends TestCase
{
    private string $defaultTimezone;

    protected function setUp(): void
    {
        $this->defaultTimezone = date_default_timezone_get();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->defaultTimezone);
    }

    public function testRandomThenGeneratesValueOfEncodedSize(): void
    {
        /** @When a random Ksuid is generated */
        $ksuid = Ksuid::random();

        /** @Then it exposes raw bytes and a Base62 value of the encoded size */
        self::assertSame(20, strlen($ksuid->bytes()));
        self::assertSame(Ksuid::ENCODED_SIZE, strlen($ksuid->value()));
    }

    public function testFromWhenTimestampGivenThenComputesUnixTime(): void
    {
        /** @Given a timestamp in seconds since the Ksuid epoch */
        $timestamp = 107611700;

        /** @When a Ksuid is created from the timestamp */
        $ksuid = Ksuid::fromTimestamp(value: $timestamp);

        /** @Then it keeps the timestamp and derives the Unix time */
        self::assertSame($timestamp, $ksuid->timestamp());
        self::assertSame(1507611700, $ksuid->unixTime());
    }

    public function testFromPayloadWhenEncodedValueThenDecodesToKsuid(): void
    {
        /** @Given a Base62-encoded Ksuid value */
        $value = '0o5Fs0EELR0fUjHjbCnEtdUwQe3';

        /** @When a Ksuid is created from the encoded payload */
        $ksuid = Ksuid::fromPayload(value: $value);

        /** @Then it exposes raw bytes and a Base62 value of the encoded size */
        self::assertSame(20, strlen($ksuid->bytes()));
        self::assertSame(Ksuid::ENCODED_SIZE, strlen($ksuid->value()));
    }

    public function testValueWhenEncodedShorterThanSizeThenLeftPadded(): void
    {
        /** @Given a Ksuid whose payload and timestamp encode below the fixed size */
        $ksuid = Ksuid::from(payload: str_repeat("\x00", 16), timestamp: 0);

        /** @When the Base62 value is retrieved */
        $value = $ksuid->value();

        /** @Then the value is left-padded to the encoded size */
        self::assertSame(Ksuid::ENCODED_SIZE, strlen($value));
    }

    #[DataProvider('providerForValidKsuids')]
    public function testInspectFromWhenValidKsuidThenReturnsComponents(
        string $ksuid,
        string $timezone,
        array $expected
    ): void {
        /** @Given a valid Base62 Ksuid and a target timezone */

        /** @And the timezone is applied to the runtime */
        date_default_timezone_set($timezone);

        /** @When the Ksuid is inspected */
        $components = Ksuid::inspectFrom(ksuid: $ksuid);

        /** @Then the formatted time, payload, and timestamp match the expected components */
        self::assertSame($expected, $components);
    }

    public function testFromWhenPayloadAndTimestampThenEncodesToExpectedValue(): void
    {
        /** @Given a raw payload */
        $payload = (string)hex2bin('9850EEEC191BF4FF26F99315CE43B0C8');

        /** @And a timestamp in seconds since the Ksuid epoch */
        $timestamp = 107611700;

        /** @When a Ksuid is created from the payload and timestamp */
        $ksuid = Ksuid::from(payload: $payload, timestamp: $timestamp);

        /** @Then it encodes to the expected Base62 value and components */
        self::assertSame($timestamp, $ksuid->timestamp());
        self::assertSame(bin2hex($payload), $ksuid->payload());
        self::assertSame('0uk1Hbc9dQ9pxyTqJ93IUrfhdGq', $ksuid->value());
        self::assertSame(20, strlen($ksuid->bytes()));
    }

    #[DataProvider('providerForInvalidPayloadSizes')]
    public function testFromWhenPayloadSizeInvalidThenThrowsInvalidPayloadSize(string $payload, int $currentSize): void
    {
        /** @Given a payload whose size differs from the required payload size */

        /** @Then an InvalidPayloadSize is raised describing the size mismatch */
        $template = 'Current length is <%s> bytes. Payload size must be exactly <%s> bytes.';
        $this->expectException(InvalidPayloadSize::class);
        $this->expectExceptionMessage(sprintf($template, $currentSize, 16));

        /** @When a Ksuid is created from the undersized payload */
        Ksuid::from(payload: $payload, timestamp: 0);
    }

    public function testInspectFromWhenLengthInvalidThenThrowsInvalidKsuidForInspection(): void
    {
        /** @Given a value whose length is not the Ksuid encoded size */
        $ksuid = random_bytes(5);

        /** @Then an InvalidKsuidForInspection is raised describing the invalid value */
        $template = 'The KSUID <%s> is not valid for inspection.';
        $this->expectException(InvalidKsuidForInspection::class);
        $this->expectExceptionMessage(sprintf($template, $ksuid));

        /** @When the value is inspected */
        Ksuid::inspectFrom(ksuid: $ksuid);
    }

    public static function providerForValidKsuids(): array
    {
        return [
            'Ksuid 2QzPUGEaAKHhVcQYrqQodbiZat1 in America/Sao_Paulo' => [
                'ksuid'    => '2QzPUGEaAKHhVcQYrqQodbiZat1',
                'timezone' => 'America/Sao_Paulo',
                'expected' => [
                    'time'      => '2023-06-09 20:30:50 -0300 -03',
                    'payload'   => '464932c1194da98e752145d72b8f0aab',
                    'timestamp' => 286353450
                ]
            ],
            'Ksuid 0ujzPyRiIAffKhBux4PvQdDqMHY in America/Sao_Paulo' => [
                'ksuid'    => '0ujzPyRiIAffKhBux4PvQdDqMHY',
                'timezone' => 'America/Sao_Paulo',
                'expected' => [
                    'time'      => '2017-10-10 01:46:20 -0300 -03',
                    'payload'   => '73fc1aa3b2446246d6e89fcd909e8fe8',
                    'timestamp' => 107610780
                ]
            ],
            'Ksuid 0ujzPyRiIAffKhBux4PvQdDqMHY in America/New_York' => [
                'ksuid'    => '0ujzPyRiIAffKhBux4PvQdDqMHY',
                'timezone' => 'America/New_York',
                'expected' => [
                    'time'      => '2017-10-10 00:46:20 -0400 EDT',
                    'payload'   => '73fc1aa3b2446246d6e89fcd909e8fe8',
                    'timestamp' => 107610780
                ]
            ],
            'Ksuid 0ujzPyRiIAffKhBux4PvQdDqMHY in Europe/London' => [
                'ksuid'    => '0ujzPyRiIAffKhBux4PvQdDqMHY',
                'timezone' => 'Europe/London',
                'expected' => [
                    'time'      => '2017-10-10 05:46:20 +0100 BST',
                    'payload'   => '73fc1aa3b2446246d6e89fcd909e8fe8',
                    'timestamp' => 107610780
                ]
            ]
        ];
    }

    public static function providerForInvalidPayloadSizes(): array
    {
        return [
            'Empty payload'      => [
                'payload'     => '',
                'currentSize' => 0
            ],
            'Three-byte payload' => [
                'payload'     => 'ABC',
                'currentSize' => 3
            ]
        ];
    }
}
