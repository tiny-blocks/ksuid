<?php

namespace TinyBlocks\Ksuid;

use PHPUnit\Framework\TestCase;

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
}
