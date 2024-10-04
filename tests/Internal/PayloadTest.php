<?php

declare(strict_types=1);

namespace TinyBlocks\Ksuid\Internal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TinyBlocks\Ksuid\Internal\Exceptions\InvalidPayloadSize;

final class PayloadTest extends TestCase
{
    #[DataProvider('providerForTestExceptionWhenInvalidPayloadSize')]
    public function testExceptionWhenInvalidPayloadSize(string $invalidData, int $currentSize): void
    {
        /** @Given an invalid payload data of size other than expected */
        $data = $invalidData;

        /** @Then an InvalidPayloadSize exception should be thrown with the appropriate message */
        $template = 'Current length is <%s> bytes. Payload size must be exactly <%s> bytes.';
        $this->expectException(InvalidPayloadSize::class);
        $this->expectExceptionMessage(sprintf($template, $currentSize, Payload::PAYLOAD_BYTES));

        /** @When attempting to create a Payload instance with invalid data size */
        Payload::from(value: $data);
    }

    public static function providerForTestExceptionWhenInvalidPayloadSize(): array
    {
        return [
            'Empty Payload' => [
                'invalidData' => '',
                'currentSize' => 0
            ],
            'Short Payload' => [
                'invalidData' => 'ABC',
                'currentSize' => 3
            ]
        ];
    }
}
