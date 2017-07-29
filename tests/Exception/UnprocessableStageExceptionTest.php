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
    public function testConstructProperlySetStage($stage)
    {
        $exception = new UnprocessableStageException($stage);
        $this->assertSame($exception->getStage(), $stage);
    }

    public function stagesDataProvider()
    {
        return [
            ['invalid stage'], [12], [new SplStack()]
        ];
    }
}