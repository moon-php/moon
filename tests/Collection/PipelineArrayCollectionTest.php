<?php

declare(strict_types=1);

namespace Moon\Moon\Collection;

use Moon\Moon\Exception\InvalidArgumentException;
use Moon\Moon\Pipeline\PipelineInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class PipelineArrayCollectionTest extends TestCase
{
    public function testAdd()
    {
        $pipelineOne = $this->prophesize(PipelineInterface::class)->reveal();
        $pipelineTwo = $this->prophesize(PipelineInterface::class)->reveal();
        $pipelineThree = $this->prophesize(PipelineInterface::class)->reveal();

        $collection = new PipelineArrayCollection();
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
        new PipelineArrayCollection(['string']);
    }

    public function testAddArray()
    {
        $pipelineOne = $this->prophesize(PipelineInterface::class)->reveal();
        $pipelineTwo = $this->prophesize(PipelineInterface::class)->reveal();
        $pipelineThree = $this->prophesize(PipelineInterface::class)->reveal();

        $collection = new PipelineArrayCollection();
        $collection->addArray([$pipelineOne, $pipelineTwo, $pipelineThree]);

        $reflection = new ReflectionProperty($collection, 'pipelines');
        $reflection->setAccessible(true);
        $this->assertSame([$pipelineOne, $pipelineTwo, $pipelineThree], $reflection->getValue($collection));
    }

    public function testAddArrayThrowInvalidArgumentExcpetion()
    {
        $this->expectException(InvalidArgumentException::class);
        $collection = new PipelineArrayCollection();
        $collection->addArray(['string']);
    }

    public function testMerge()
    {
        $pipelineOne = $this->prophesize(PipelineInterface::class)->reveal();
        $pipelineTwo = $this->prophesize(PipelineInterface::class)->reveal();
        $pipelineThree = $this->prophesize(PipelineInterface::class)->reveal();
        $pipelineFour = $this->prophesize(PipelineInterface::class)->reveal();

        $collectionOne = new PipelineArrayCollection([$pipelineOne, $pipelineTwo]);
        $collectionTwo = new PipelineArrayCollection([$pipelineThree, $pipelineFour]);
        $collectionOne->merge($collectionTwo);

        $reflection = new ReflectionProperty(PipelineArrayCollection::class, 'pipelines');
        $reflection->setAccessible(true);
        $this->assertSame([$pipelineOne, $pipelineTwo, $pipelineThree, $pipelineFour], $reflection->getValue($collectionOne));
    }

    public function testToArray()
    {
        $pipelineOne = $this->prophesize(PipelineInterface::class)->reveal();
        $pipelineTwo = $this->prophesize(PipelineInterface::class)->reveal();

        $collection = new PipelineArrayCollection([$pipelineOne, $pipelineTwo]);

        $this->assertSame([$pipelineOne, $pipelineTwo], $collection->toArray());
    }

    public function testGetIterator()
    {
        $pipelineOne = $this->prophesize(PipelineInterface::class)->reveal();
        $pipelineTwo = $this->prophesize(PipelineInterface::class)->reveal();
        $pipelineThree = $this->prophesize(PipelineInterface::class)->reveal();
        $pipelineFour = $this->prophesize(PipelineInterface::class)->reveal();

        $pipelines = [$pipelineOne, $pipelineTwo, $pipelineThree, $pipelineFour];
        $collection = new PipelineArrayCollection([$pipelineOne, $pipelineTwo]);
        $i = 0;
        foreach ($collection as $key => $pipeline) {
            $this->assertSame($key, $i);
            $this->assertSame($pipelines[$i], $pipeline);
            $i++;
        }
    }
}