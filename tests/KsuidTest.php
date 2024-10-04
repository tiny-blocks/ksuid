<?php

declare(strict_types=1);

namespace TinyBlocks\Ksuid;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Ksuid\Internal\Exceptions\InvalidKsuidForInspection;
use TinyBlocks\Ksuid\Internal\Exceptions\InvalidPayloadException; // Assuming this exception exists
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

    // Additional test cases added below

    public function testFromInvalidPayload(): void
    {
        /** @Then an InvalidPayloadException should be thrown for an empty payload */
        $this->expectException(InvalidPayloadException::class);
        Ksuid::from('', time()); // Assuming an empty payload is invalid.
    }

    public function testFromFutureTimestamp(): void
    {
        /** @Given a future Unix timestamp */
        $futureTimestamp = time() + 1000000; // Future timestamp

        /** @When I generate a KSUID from the future timestamp */
        $ksuid = Ksuid::fromPayload(Payload::random()->getValue(), $futureTimestamp);

        /** @Then the KSUID should have the future timestamp */
        self::assertEquals($futureTimestamp, $ksuid->getTimestamp());
    }

    public function testGetValueConsistency(): void
    {
        /** @Given a random KSUID */
        $ksuid = Ksuid::random();

        /** @When I retrieve the value multiple times */
        $value = $ksuid->getValue();

        /** @Then the value should remain consistent */
        self::assertEquals($value, $ksuid->getValue());
    }

    public function testGetPayloadHexConversion(): void
    {
        /** @Given a specific payload */
        $payloadValue = 'example_payload'; // Use an actual payload value
        $ksuid = Ksuid::from($payloadValue, time());

        /** @Then the payload should match the hex conversion */
        self::assertEquals(bin2hex($payloadValue), $ksuid->getPayload());
    }

    public function testGetUnixTime(): void
    {
        /** @Given a Unix timestamp */
        $currentTimestamp = time();
        $ksuid = Ksuid::from(Payload::random()->getValue(), $currentTimestamp);

        /** @Then the Unix time should match the timestamp */
        self::assertEquals($currentTimestamp, $ksuid->getUnixTime());
    }

    public function testInspectFromInvalidLength(): void
    {
        /** @Given a KSUID with an invalid length */
        $this->expectException(InvalidKsuidForInspection::class);
        Ksuid::inspectFrom('short'); // Shorter than ENCODED_SIZE
    }

    public function testFromTimestampValidPayload(): void
    {
        /** @Given a Unix timestamp */
        $currentTimestamp = time();
        $ksuid = Ksuid::fromTimestamp($currentTimestamp);

        /** @Then the KSUID should have a random payload */
        self::assertNotEmpty($ksuid->getPayload());
        self::assertEquals($currentTimestamp, $ksuid->getTimestamp());
    }
}
