<?php

declare(strict_types=1);

namespace TinyBlocks\Ksuid;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Ksuid\Internal\Exceptions\InvalidKsuidForInspection;
use TinyBlocks\Ksuid\Internal\Timestamp;

final class KsuidTest extends TestCase
{
    public function testRandom(): void
    {
        /** @When I generate a random KSUID */
        $ksuid = Ksuid::random();

        /** @Then a KSUID should be generated with the expected lengths */
        self::assertSame(20, strlen($ksuid->getBytes()));
        self::assertSame(Ksuid::ENCODED_SIZE, strlen($ksuid->getValue()));
    }

    #[DataProvider('providerForTestInspectFrom')]
    public function testInspectFrom(string $ksuid, string $timezone, array $expected): void
    {
        /** @Given a KSUID and a specific timezone */
        $default = date_default_timezone_get();
        date_default_timezone_set($timezone);

        /** @When I inspect the KSUID */
        $actual = Ksuid::inspectFrom(ksuid: $ksuid);

        /** @Then the result should contain the correct time, payload, and timestamp */
        self::assertSame($expected, $actual);
        date_default_timezone_set($default);
    }

    public function testFromPayload(): void
    {
        /** @Given a specific Base62 encoded value */
        $value = '0o5Fs0EELR0fUjHjbCnEtdUwQe3';

        /** @When I generate a KSUID from this payload */
        $ksuid = Ksuid::fromPayload(value: $value);

        /** @Then a KSUID should be generated with the expected lengths */
        self::assertSame(20, strlen($ksuid->getBytes()));
        self::assertSame(Ksuid::ENCODED_SIZE, strlen($ksuid->getValue()));
    }

    public function testFromTimestamp(): void
    {
        /** @Given a Unix timestamp */
        $value = time();

        /** @When I generate a KSUID from this timestamp */
        $ksuid = Ksuid::fromTimestamp(value: $value);

        /** @Then a KSUID should have the corresponding timestamp and encoded value */
        self::assertSame($value, $ksuid->getTimestamp());
        self::assertSame($value + Timestamp::EPOCH, $ksuid->getUnixTime());
        self::assertSame(20, strlen($ksuid->getBytes()));
        self::assertSame(Ksuid::ENCODED_SIZE, strlen($ksuid->getValue()));
    }

    public function testFromPayloadAndTimestamp(): void
    {
        /** @Given a specific payload and timestamp */
        $payload = hex2bin("9850EEEC191BF4FF26F99315CE43B0C8");
        $timestamp = 107611700;

        /** @When I generate a KSUID from this payload and timestamp */
        $ksuid = Ksuid::from(payload: $payload, timestamp: $timestamp);

        /** @Then the KSUID should match the expected payload, timestamp, and encoded value */
        self::assertSame(bin2hex($payload), $ksuid->getPayload());
        self::assertSame($timestamp, $ksuid->getTimestamp());
        self::assertSame('0uk1Hbc9dQ9pxyTqJ93IUrfhdGq', $ksuid->getValue());
        self::assertSame(20, strlen($ksuid->getBytes()));
        self::assertSame(Ksuid::ENCODED_SIZE, strlen($ksuid->getValue()));
    }

    public function testGetValueAppliesPaddingToFixedLength(): void
    {
        /** @Given a payload that generates any length less than 27 characters */
        $payload = hex2bin("00000000000000000000000000000000");
        $ksuid = Ksuid::from(payload: $payload, timestamp: 0);

        /** @When I retrieve the encoded value */
        $encodedValue = $ksuid->getValue();

        /** @Then the value should be exactly 27 characters long, with padding applied as needed */
        self::assertEquals(27, strlen($encodedValue));
    }

    public function testExceptionWhenInvalidKsuidForInspection(): void
    {
        /** @Given an invalid KSUID */
        $ksuid = random_bytes(5);
        $template = 'The KSUID <%s> is not valid for inspection.';

        /** @Then an InvalidKsuidForInspection exception should be thrown */
        $this->expectException(InvalidKsuidForInspection::class);
        $this->expectExceptionMessage(sprintf($template, $ksuid));

        /** @When I attempt to inspect the invalid KSUID */
        Ksuid::inspectFrom(ksuid: $ksuid);
    }

    public static function providerForTestInspectFrom(): array
    {
        return [
            'KSUID 2QzPUGEaAKHhVcQYrqQodbiZat1' => [
                'ksuid'    => '2QzPUGEaAKHhVcQYrqQodbiZat1',
                'timezone' => 'America/Sao_Paulo',
                'expected' => [
                    'time'      => '2023-06-09 20:30:50 -0300 -03',
                    'payload'   => '464932c1194da98e752145d72b8f0aab',
                    'timestamp' => 286353450
                ]
            ],
            'KSUID 0ujzPyRiIAffKhBux4PvQdDqMHY' => [
                'ksuid'    => '0ujzPyRiIAffKhBux4PvQdDqMHY',
                'timezone' => 'America/Sao_Paulo',
                'expected' => [
                    'time'      => '2017-10-10 01:46:20 -0300 -03',
                    'payload'   => '73fc1aa3b2446246d6e89fcd909e8fe8',
                    'timestamp' => 107610780
                ]
            ]
        ];
    }
}
