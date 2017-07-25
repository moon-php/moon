Defining Moon\App api

```php
<?php

$app = new \Moon\Moon\App(/*Container instance*/);

// Add generic pipelines to you app
$app->pipe([ClassOne::class,ClassTwo::class,ClassThree::class]);
$app->pipe(ClassOne::class);


$httpUserPipeline = new \Moon\Moon\Pipeline\HttpPipeline(['POST', 'PUT'], '/', [ClassOne::class,ClassTwo::class]);
$httpContactPipeline = new \Moon\Moon\Pipeline\HttpPipeline('GET', '/users/{id::\d+}', ClassThree::class);
$httpProductPipeline = new \Moon\Moon\Pipeline\HttpPipeline('GET', '/users/{id}/edit', ClassTFour::class);
$httpPrizesPipeline = new \Moon\Moon\Pipeline\HttpPipeline('PUT', '::/users/(?<attributeName>\d+)', ClassFive::class);
$httpProductPipeline->pipe([ClassOne::class,ClassTwo::class,ClassThree::class]);

// Or Router (is a syntax sugar for web api wrap a HttpPipeline Object)
$router = new \Moon\Moon\Router('/posts', [PreClass::class]);
$router->get('/[paginated]', [ClassOne::class,ClassTwo::class,ClassThree::class]);
$router->post('/{id}', [ClassOne::class,ClassTwo::class]);
// run

$httpPipelineCollection = new \Moon\Moon\Collection\PipelineArrayCollection([$httpUserPipeline, $httpContactPipeline]);
$httpPipelineCollection->add($httpProductPipeline);
$httpPipelineCollection->merge($router->pipelines());
$app->run($httpPipelineCollection);