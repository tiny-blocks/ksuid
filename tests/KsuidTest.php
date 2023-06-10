<?php

namespace TinyBlocks\Ksuid;

use Exception;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Ksuid\Internal\Exceptions\InvalidKsuidForInspection;
use TinyBlocks\Ksuid\Internal\Timestamp;

class KsuidTest extends TestCase
{
    public function testRandom(): void
    {
        $ksuid = Ksuid::random();

        self::assertEquals(20, strlen($ksuid->getBytes()));
        self::assertEquals(Ksuid::ENCODED_SIZE, strlen($ksuid->getValue()));
    }

    public function testFromPayload(): void
    {
        /** @Given a value */
        $value = '0o5Fs0EELR0fUjHjbCnEtdUwQe3';

        /** @When I generate a KSUID with this value */
        $ksuid = Ksuid::fromPayload(value: $value);

        /** @Then a KSUID must be generated */
        self::assertEquals(20, strlen($ksuid->getBytes()));
        self::assertEquals(Ksuid::ENCODED_SIZE, strlen($ksuid->getValue()));
    }

    public function testFromTimestamp(): void
    {
        /** @Given a value */
        $value = time();

        /** @When I generate a KSUID with this value */
        $ksuid = Ksuid::fromTimestamp(value: $value);

        /** @Then a KSUID must be generated */
        self::assertEquals($value, $ksuid->getTimestamp());
        self::assertEquals($value + Timestamp::EPOCH, $ksuid->getUnixTime());
        self::assertEquals(20, strlen($ksuid->getBytes()));
        self::assertEquals(Ksuid::ENCODED_SIZE, strlen($ksuid->getValue()));
    }

    public function testFromPayloadAndTimestamp(): void
    {
        /** @Given a payload */
        $payload = hex2bin("9850EEEC191BF4FF26F99315CE43B0C8");

        /** @And a timestamp */
        $timestamp = 107611700;

        /** @When I generate a KSUID with this value */
        $ksuid = Ksuid::from(payload: $payload, timestamp: $timestamp);

        /** @Then a KSUID must be generated */
        self::assertEquals(bin2hex($payload), $ksuid->getPayload());
        self::assertEquals($timestamp, $ksuid->getTimestamp());
        self::assertEquals('0uk1Hbc9dQ9pxyTqJ93IUrfhdGq', $ksuid->getValue());
        self::assertEquals(20, strlen($ksuid->getBytes()));
        self::assertEquals(Ksuid::ENCODED_SIZE, strlen($ksuid->getValue()));
    }

    /**
     * @dataProvider providerForTestInspectFrom
     */
    public function testInspectFrom(string $ksuid, array $expected): void
    {
        $actual = Ksuid::inspectFrom(ksuid: $ksuid);

        self::assertEquals($expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function testExceptionWhenInvalidKsuidForInspection(): void
    {
        /** @Given a invalid KSUID */
        $ksuid = random_bytes(5);
        $template = 'The KSUID <%s> is not valid for inspection.';

        /** @Then an exception indicating that KSUID is invalid for inspection should occur */
        $this->expectException(InvalidKsuidForInspection::class);
        $this->expectExceptionMessage(sprintf($template, $ksuid));

        Ksuid::inspectFrom(ksuid: $ksuid);
    }

    public function providerForTestInspectFrom(): array
    {
        return [
            [
                'ksuid'    => '2QzPUGEaAKHhVcQYrqQodbiZat1',
                'expected' => [
                    'time'      => '2023-06-09 20:30:50 -0300 -03',
                    'payload'   => '464932c1194da98e752145d72b8f0aab',
                    'timestamp' => 286353450
                ]
            ],
            [
                'ksuid'    => '0ujzPyRiIAffKhBux4PvQdDqMHY',
                'expected' => [
                    'time'      => '2017-10-10 01:46:20 -0300 -03',
                    'payload'   => '73fc1aa3b2446246d6e89fcd909e8fe8',
                    'timestamp' => 107610780
                ]
            ]
        ];
    }
}
