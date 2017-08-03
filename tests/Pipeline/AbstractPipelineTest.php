<?php

declare(strict_types=1);

namespace Moon\Moon\Pipeline;

use Moon\Moon\Matchable\MatchableRequestInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use function md5;
use function mt_rand;
use function uniqid;

class AbstractPipelineTest extends TestCase
{

    /**
     * @dataProvider constructDataProvider
     */
    public function testConstruct($verb, $expectedVerb, $pattern, $stages, $expectedStages)
    {
        $httpPipeline = new HttpPipeline($verb, $pattern, $stages);

        $httpPipelineVerbsReflectionProperty = new ReflectionProperty(HttpPipeline::class, 'verbs');
        $httpPipelineVerbsReflectionProperty->setAccessible(true);
        $httpPipelineVerbSetInPipeline = $httpPipelineVerbsReflectionProperty->getValue($httpPipeline);

        $httpPipelinePatternReflectionProperty = new ReflectionProperty(HttpPipeline::class, 'pattern');
        $httpPipelinePatternReflectionProperty->setAccessible(true);
        $httpPipelinePatternSetInPipeline = $httpPipelinePatternReflectionProperty->getValue($httpPipeline);

        $httpPipelineStagesReflectionProperty = new ReflectionProperty(HttpPipeline::class, 'stages');
        $httpPipelineStagesReflectionProperty->setAccessible(true);
        $httpPipelineStagesSetInPipeline = $httpPipelineStagesReflectionProperty->getValue($httpPipeline);

        $this->assertSame($pattern, $httpPipelinePatternSetInPipeline);
        $this->assertSame($expectedVerb, $httpPipelineVerbSetInPipeline);
        $this->assertSame($expectedStages, $httpPipelineStagesSetInPipeline);
    }

    public function testMatchBy()
    {
        $matchable = $this->prophesize(MatchableRequestInterface::class);
        $matchable->match(['verbs' => ['GET'], 'pattern' => 'patternOne'])->shouldBeCalled(1)->willReturn(true);
        $matchable->match(['verbs' => ['POST'], 'pattern' => 'patternTwo'])->shouldBeCalled(1)->willReturn(false);
        $matchable = $matchable->reveal();

        $httpPipelineOne = new HttpPipeline('GET', 'patternOne');
        $httpPipelineTwo = new HttpPipeline('POST', 'patternTwo');

        $this->assertTrue($httpPipelineOne->matchBy($matchable));
        $this->assertFalse($httpPipelineTwo->matchBy($matchable));
    }

    public function testStages()
    {
        $httpPipeline = new HttpPipeline('GET', 'pattern');
        $reflection = new ReflectionProperty(HttpPipeline::class, 'stages');
        $reflection->setAccessible(true);
        $reflection->setValue($httpPipeline, ['an', 'array', 'of', 'stages']);

        $this->assertSame(['an', 'array', 'of', 'stages'], $httpPipeline->stages());
    }

    public function constructDataProvider()
    {
        $stageOne = function () {
            return '';
        };

        $stageTwo = $this->prophesize(PipelineInterface::class);
        $stageTwo->stages()->shouldBeCalled(1)->willReturn([$stageOne]);
        $stageTwo = $stageTwo->reveal();

        return [
            ['POST', ['POST'], md5(uniqid((string)mt_rand(), true)), $stageTwo, [$stageOne]],
            ['DELETE', ['DELETE'], md5(uniqid((string)mt_rand(), true)), $stageOne, [$stageOne]],
            [['POST', 'PUT'], ['POST', 'PUT'], md5(uniqid((string)mt_rand(), true)), null, []],
            ['GET', ['GET'], md5(uniqid((string)mt_rand(), true)), null, []],
        ];
    }
}