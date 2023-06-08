<?php

namespace TinyBlocks\Ksuid\Internal;

use PHPUnit\Framework\TestCase;
use TinyBlocks\Ksuid\Internal\Exceptions\InvalidPayloadSize;

class PayloadTest extends TestCase
{
    /**
     * @dataProvider providerForTestExceptionWhenInvalidPayloadSize
     */
    public function testExceptionWhenInvalidPayloadSize(string $invalidData, int $currentSize): void
    {
        /** @Given a invalid data */
        $data = $invalidData;
        $template = 'Current length is <%s> bytes. Payload size must be exactly <%s> bytes.';

        /** @Then an exception indicating that the payload size is invalid should occur */
        $this->expectException(InvalidPayloadSize::class);
        $this->expectExceptionMessage(sprintf($template, $currentSize, Payload::PAYLOAD_BYTES));

        /** @When requesting the creation of the payload with invalid data */
        Payload::from(value: $data);
    }

    public function providerForTestExceptionWhenInvalidPayloadSize(): array
    {
        return [
            [
                'invalidData' => '',
                'currentSize' => 0
            ],
            [
                'invalidData' => 'ABC',
                'currentSize' => 3
            ]
        ];
    }
}
