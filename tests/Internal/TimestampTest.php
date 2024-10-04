<?php

declare(strict_types=1);

namespace TinyBlocks\Ksuid\Internal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TimestampTest extends TestCase
{
    #[DataProvider('providerForTestFormatWithDifferentTimezones')]
    public function testFormatWithDifferentTimezones(string $timezone, string $expected): void
    {
        /** @Given a specific timezone is set */
        $default = date_default_timezone_get();
        date_default_timezone_set($timezone);
        $timestamp = Timestamp::from(value: 107608047);

        /** @When formatting the timestamp to Unix time with timezone information */
        $actual = $timestamp->toUnixTimeFormatted();

        /** @Then the formatted timestamp matches the expected value for the timezone */
        self::assertEquals($expected, $actual);
        date_default_timezone_set($default);
    }

    public static function providerForTestFormatWithDifferentTimezones(): array
    {
        return [
            'Timezone America/Sao_Paulo' => [
                'timezone' => 'America/Sao_Paulo',
                'expected' => '2017-10-10 01:00:47 -0300 -03'
            ],
            'Timezone America/New_York'  => [
                'timezone' => 'America/New_York',
                'expected' => '2017-10-10 00:00:47 -0400 EDT'
            ],
            'Timezone Europe/London'     => [
                'timezone' => 'Europe/London',
                'expected' => '2017-10-10 05:00:47 +0100 BST'
            ]
        ];
    }
}
