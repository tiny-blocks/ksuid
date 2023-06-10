<?php

namespace TinyBlocks\Ksuid\Internal;

use PHPUnit\Framework\TestCase;

class TimestampTest extends TestCase
{
    /**
     * @dataProvider providerForTestFormatWithDifferentTimezones
     */
    public function testFormatWithDifferentTimezones(string $timezone, string $expected): void
    {
        $default = date_default_timezone_get();
        date_default_timezone_set($timezone);

        $timestamp = Timestamp::from(value: 107608047);

        $actual = $timestamp->toUnixTimeFormatted();

        self::assertEquals($expected, $actual);

        date_default_timezone_set($default);
    }

    public function providerForTestFormatWithDifferentTimezones(): array
    {
        return [
            [
                'timezone' => 'America/Sao_Paulo',
                'expected' => '2017-10-10 01:00:47 -0300 -03'
            ],
            [
                'timezone' => 'America/New_York',
                'expected' => '2017-10-10 00:00:47 -0400 EDT'
            ],
            [
                'timezone' => 'Europe/London',
                'expected' => '2017-10-10 05:00:47 +0100 BST'
            ]
        ];
    }
}
