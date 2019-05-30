<?php

declare(strict_types=1);

namespace Moon\Moon\Exception;

use PHPUnit\Framework\TestCase;
use SplStack;

class UnprocessableStageExceptionTest extends TestCase
{
    /**
     * @dataProvider stagesDataProvider
     */
    public function testConstructProperlySetStage($stage, $expectedMessage)
    {
        $exception = new UnprocessableStageException($stage);
        $this->assertSame($exception->getStage(), $stage);
        $this->assertSame($exception->getMessage(), $expectedMessage);
    }

    public function stagesDataProvider(): array
    {
        return [
            [
                'invalid stage', "The stage can't be handled. Given: invalid stage",
            ], [
                12, "The stage can't be handled. Given: 12",
            ], [
                new SplStack(), "The stage can't be handled. Given: ".SplStack::class,
            ], [
                function () {
                }, "The stage can't be handled. Given: Closure",
            ],
        ];
    }
}
