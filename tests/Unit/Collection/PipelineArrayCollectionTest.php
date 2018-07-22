<?php

declare(strict_types=1);

namespace Moon\Moon\Collection;

use Moon\Moon\Exception\InvalidArgumentException;
use Moon\Moon\Pipeline\MatchablePipelineInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class PipelineArrayCollectionTest extends TestCase
{
    public function testAdd()
    {
        $pipelineOne = $this->prophesize(MatchablePipelineInterface::class)->reveal();
        $pipelineTwo = $this->prophesize(MatchablePipelineInterface::class)->reveal();
        $pipelineThree = $this->prophesize(MatchablePipelineInterface::class)->reveal();

        $collection = new MatchablePipelineArrayCollection();
        $collection->add($pipelineOne);
        $collection->add($pipelineTwo);
        $collection->add($pipelineThree);

        $reflection = new ReflectionProperty($collection, 'pipelines');
        $reflection->setAccessible(true);
        $this->assertSame([$pipelineOne, $pipelineTwo, $pipelineThree], $reflection->getValue($collection));
    }

    public function testConstructorThrowInvalidArgumentExcpetion()
    {
        $this->expectException(InvalidArgumentException::class);
        new MatchablePipelineArrayCollection(['string']);
    }

    public function testAddArray()
    {
        $pipelineOne = $this->prophesize(MatchablePipelineInterface::class)->reveal();
        $pipelineTwo = $this->prophesize(MatchablePipelineInterface::class)->reveal();
        $pipelineThree = $this->prophesize(MatchablePipelineInterface::class)->reveal();

        $collection = new MatchablePipelineArrayCollection();
        $collection->addArray([$pipelineOne, $pipelineTwo, $pipelineThree]);

        $reflection = new ReflectionProperty($collection, 'pipelines');
        $reflection->setAccessible(true);
        $this->assertSame([$pipelineOne, $pipelineTwo, $pipelineThree], $reflection->getValue($collection));
    }

    public function testAddArrayThrowInvalidArgumentExcpetion()
    {
        $this->expectException(InvalidArgumentException::class);
        $collection = new MatchablePipelineArrayCollection();
        $collection->addArray(['string']);
    }

    public function testMerge()
    {
        $pipelineOne = $this->prophesize(MatchablePipelineInterface::class)->reveal();
        $pipelineTwo = $this->prophesize(MatchablePipelineInterface::class)->reveal();
        $pipelineThree = $this->prophesize(MatchablePipelineInterface::class)->reveal();
        $pipelineFour = $this->prophesize(MatchablePipelineInterface::class)->reveal();

        $collectionOne = new MatchablePipelineArrayCollection([$pipelineOne, $pipelineTwo]);
        $collectionTwo = new MatchablePipelineArrayCollection([$pipelineThree, $pipelineFour]);
        $collectionOne->merge($collectionTwo);

        $reflection = new ReflectionProperty(MatchablePipelineArrayCollection::class, 'pipelines');
        $reflection->setAccessible(true);
        $this->assertSame([$pipelineOne, $pipelineTwo, $pipelineThree, $pipelineFour], $reflection->getValue($collectionOne));
    }

    public function testToArray()
    {
        $pipelineOne = $this->prophesize(MatchablePipelineInterface::class)->reveal();
        $pipelineTwo = $this->prophesize(MatchablePipelineInterface::class)->reveal();

        $collection = new MatchablePipelineArrayCollection([$pipelineOne, $pipelineTwo]);

        $this->assertSame([$pipelineOne, $pipelineTwo], $collection->toArray());
    }

    public function testGetIterator()
    {
        $pipelineOne = $this->prophesize(MatchablePipelineInterface::class)->reveal();
        $pipelineTwo = $this->prophesize(MatchablePipelineInterface::class)->reveal();
        $pipelineThree = $this->prophesize(MatchablePipelineInterface::class)->reveal();
        $pipelineFour = $this->prophesize(MatchablePipelineInterface::class)->reveal();

        $pipelines = [$pipelineOne, $pipelineTwo, $pipelineThree, $pipelineFour];
        $collection = new MatchablePipelineArrayCollection([$pipelineOne, $pipelineTwo]);
        $i = 0;
        foreach ($collection as $key => $pipeline) {
            $this->assertSame($key, $i);
            $this->assertSame($pipelines[$i], $pipeline);
            ++$i;
        }
    }
}
