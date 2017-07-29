<?php

declare(strict_types=1);

namespace Moon\Moon;

use Fig\Http\Message\RequestMethodInterface;
use Moon\Moon\Collection\PipelineArrayCollection;
use Moon\Moon\Collection\PipelineCollectionInterface;
use Moon\Moon\Pipeline\HttpPipeline;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use function array_push;
use function md5;
use function mt_rand;
use function uniqid;

class RouterTest extends TestCase
{
    /**
     * @dataProvider stagesDataProvider
     */
    public function testConstruct($stages)
    {
        $prefix = md5(uniqid((string)mt_rand(), true));
        $router = new Router($prefix, $stages);

        $prefixReflectionProperty = new ReflectionProperty(Router::class, 'prefix');
        $prefixReflectionProperty->setAccessible(true);
        $prefixSetInRouter = $prefixReflectionProperty->getValue($router);

        $stagesReflectionProperty = new ReflectionProperty(Router::class, 'routerStages');
        $stagesReflectionProperty->setAccessible(true);
        $stagesSetInRouter = $stagesReflectionProperty->getValue($router);

        $pipelineCollectionReflectionProperty = new ReflectionProperty(Router::class, 'pipelineCollection');
        $pipelineCollectionReflectionProperty->setAccessible(true);
        $pipelineCollectionSetInRouter = $pipelineCollectionReflectionProperty->getValue($router);

        $this->assertSame($prefixSetInRouter, $prefix);
        $this->assertSame($stagesSetInRouter, $stages);
        $this->assertInstanceOf(PipelineArrayCollection::class, $pipelineCollectionSetInRouter);
    }

    /**
     * @dataProvider stagesDataProvider
     */
    public function testGet($stages)
    {
        $firstPrefix = md5(uniqid((string)mt_rand(), true));
        $secondPrefix = md5(uniqid((string)mt_rand(), true));

        $router = new Router($firstPrefix, $stages);
        $router->get($secondPrefix, $stages);

        $this->toNameThisOne($router, $stages, $firstPrefix . $secondPrefix, [RequestMethodInterface::METHOD_GET]);
    }

    /**
     * @dataProvider stagesDataProvider
     */
    public function testPost($stages)
    {
        $firstPrefix = md5(uniqid((string)mt_rand(), true));
        $secondPrefix = md5(uniqid((string)mt_rand(), true));

        $router = new Router($firstPrefix, $stages);
        $router->post($secondPrefix, $stages);

        $this->toNameThisOne($router, $stages, $firstPrefix . $secondPrefix, [RequestMethodInterface::METHOD_POST]);
    }

    /**
     * @dataProvider stagesDataProvider
     */
    public function testPut($stages)
    {
        $firstPrefix = md5(uniqid((string)mt_rand(), true));
        $secondPrefix = md5(uniqid((string)mt_rand(), true));

        $router = new Router($firstPrefix, $stages);
        $router->put($secondPrefix, $stages);

        $this->toNameThisOne($router, $stages, $firstPrefix . $secondPrefix, [RequestMethodInterface::METHOD_PUT]);
    }

    /**
     * @dataProvider stagesDataProvider
     */
    public function testPatch($stages)
    {
        $firstPrefix = md5(uniqid((string)mt_rand(), true));
        $secondPrefix = md5(uniqid((string)mt_rand(), true));

        $router = new Router($firstPrefix, $stages);
        $router->patch($secondPrefix, $stages);

        $this->toNameThisOne($router, $stages, $firstPrefix . $secondPrefix, [RequestMethodInterface::METHOD_PATCH]);
    }

    /**
     * @dataProvider stagesDataProvider
     */
    public function testDelete($stages)
    {
        $firstPrefix = md5(uniqid((string)mt_rand(), true));
        $secondPrefix = md5(uniqid((string)mt_rand(), true));

        $router = new Router($firstPrefix, $stages);
        $router->delete($secondPrefix, $stages);

        $this->toNameThisOne($router, $stages, $firstPrefix . $secondPrefix, [RequestMethodInterface::METHOD_DELETE]);
    }

    /**
     * @dataProvider stagesDataProvider
     */
    public function testOptions($stages)
    {
        $firstPrefix = md5(uniqid((string)mt_rand(), true));
        $secondPrefix = md5(uniqid((string)mt_rand(), true));

        $router = new Router($firstPrefix, $stages);
        $router->options($secondPrefix, $stages);

        $this->toNameThisOne($router, $stages, $firstPrefix . $secondPrefix, [RequestMethodInterface::METHOD_OPTIONS]);
    }

    /**
     * @dataProvider stagesDataProvider
     */
    public function testHead($stages)
    {
        $firstPrefix = md5(uniqid((string)mt_rand(), true));
        $secondPrefix = md5(uniqid((string)mt_rand(), true));

        $router = new Router($firstPrefix, $stages);
        $router->head($secondPrefix, $stages);

        $this->toNameThisOne($router, $stages, $firstPrefix . $secondPrefix, [RequestMethodInterface::METHOD_HEAD]);
    }

    /**
     * @dataProvider stagesDataProvider
     */
    public function testPurge($stages)
    {
        $firstPrefix = md5(uniqid((string)mt_rand(), true));
        $secondPrefix = md5(uniqid((string)mt_rand(), true));

        $router = new Router($firstPrefix, $stages);
        $router->purge($secondPrefix, $stages);

        $this->toNameThisOne($router, $stages, $firstPrefix . $secondPrefix, [RequestMethodInterface::METHOD_PURGE]);
    }

    /**
     * @dataProvider stagesDataProvider
     */
    public function testTrace($stages)
    {
        $firstPrefix = md5(uniqid((string)mt_rand(), true));
        $secondPrefix = md5(uniqid((string)mt_rand(), true));

        $router = new Router($firstPrefix, $stages);
        $router->trace($secondPrefix, $stages);

        $this->toNameThisOne($router, $stages, $firstPrefix . $secondPrefix, [RequestMethodInterface::METHOD_TRACE]);
    }

    /**
     * @dataProvider stagesDataProvider
     */
    public function testConnect($stages)
    {
        $firstPrefix = md5(uniqid((string)mt_rand(), true));
        $secondPrefix = md5(uniqid((string)mt_rand(), true));

        $router = new Router($firstPrefix, $stages);
        $router->connect($secondPrefix, $stages);

        $this->toNameThisOne($router, $stages, $firstPrefix . $secondPrefix, [RequestMethodInterface::METHOD_CONNECT]);
    }

    /**
     * @dataProvider stagesDataProvider
     */
    public function testMap($stages)
    {
        $firstPrefix = md5(uniqid((string)mt_rand(), true));
        $secondPrefix = md5(uniqid((string)mt_rand(), true));

        $router = new Router($firstPrefix, $stages);
        $router->map(
            $secondPrefix,
            [RequestMethodInterface::METHOD_HEAD, RequestMethodInterface::METHOD_GET, RequestMethodInterface::METHOD_PUT],
            $stages
        );

        $this->toNameThisOne(
            $router,
            $stages,
            $firstPrefix . $secondPrefix,
            [RequestMethodInterface::METHOD_HEAD, RequestMethodInterface::METHOD_GET, RequestMethodInterface::METHOD_PUT]
        );
    }

    public function testPipelines()
    {
        $router = new Router();
        $reflection = new ReflectionProperty($router, 'pipelineCollection');
        $reflection->setAccessible(true);
        $reflection->setValue($router, $this->prophesize(PipelineCollectionInterface::class)->reveal());
        $this->assertSame($reflection->getValue($router), $router->pipelines());
    }

    public function stagesDataProvider()
    {
        $callable = function () {
        };
        $pipeline = $this->prophesize(HttpPipeline::class)->reveal();

        return [
            [$callable, 'string', $pipeline, [$callable, 'string', $pipeline, [$callable, 'string', $pipeline]]]
        ];
    }

    private function toNameThisOne(Router $router, $stages, $expectedPattern, $expectedVerbs)
    {
        $pipelineCollectionReflectionProperty = new ReflectionProperty(Router::class, 'pipelineCollection');
        $pipelineCollectionReflectionProperty->setAccessible(true);
        $pipelineCollectionSetInRouter = $pipelineCollectionReflectionProperty->getValue($router);

        $pipelineArrayReflectionProperty = new ReflectionProperty(PipelineArrayCollection::class, 'pipelines');
        $pipelineArrayReflectionProperty->setAccessible(true);
        $httpPipelineSetInCollection = $pipelineArrayReflectionProperty->getValue($pipelineCollectionSetInRouter)[0];

        $httpPipelineVerbsReflectionProperty = new ReflectionProperty(HttpPipeline::class, 'verbs');
        $httpPipelineVerbsReflectionProperty->setAccessible(true);
        $httpPipelineVerbSetInPipeline = $httpPipelineVerbsReflectionProperty->getValue($httpPipelineSetInCollection);

        $httpPipelinePatternReflectionProperty = new ReflectionProperty(HttpPipeline::class, 'pattern');
        $httpPipelinePatternReflectionProperty->setAccessible(true);
        $httpPipelinePatternSetInPipeline = $httpPipelinePatternReflectionProperty->getValue($httpPipelineSetInCollection);

        $expectedStagesSetInHttpPipeline = [];
        array_push($expectedStagesSetInHttpPipeline, $stages, $stages);

        $this->assertSame($httpPipelinePatternSetInPipeline, $expectedPattern);
        $this->assertSame($httpPipelineSetInCollection->stages(), $expectedStagesSetInHttpPipeline);
        $this->assertSame($expectedVerbs, $httpPipelineVerbSetInPipeline);
        $this->assertInstanceOf(PipelineArrayCollection::class, $pipelineCollectionSetInRouter);
    }
}