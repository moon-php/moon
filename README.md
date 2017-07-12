Defining Moon\App api

```php
<?php

$app = new \Moon\Core\App(/*Container instance*/);

// Add generic pipelines to you app
$app->pipe([ClassOne::class,ClassTwo::class,ClassThree::class]);
$app->pipe(ClassOne::class);



// If the app is a http application
////////////////////////////////////
////////////////// Web
////////////////////////////////////
$httpUserPipeline = new \Moon\Core\Pipeline\HttpPipeline(['POST', 'PUT'], '/', [ClassOne::class,ClassTwo::class]);
$httpContactPipeline = new \Moon\Core\Pipeline\HttpPipeline('GET', '/users/{id::\d+}', ClassThree::class);
$httpProductPipeline = new \Moon\Core\Pipeline\HttpPipeline('GET', '/users/{id}/edit', ClassTFour::class);
$httpPrizesPipeline = new \Moon\Core\Pipeline\HttpPipeline('PUT', '::/users/(?<attributeName>\d+)', ClassFive::class);
$httpProductPipeline->pipe([ClassOne::class,ClassTwo::class,ClassThree::class]);

// Or Router (is a syntax sugar for web api wrap a HttpPipeline Object)
$router = new \Moon\Core\Router('/posts', [PreClass::class]);
$router->get('/[paginated]', [ClassOne::class,ClassTwo::class,ClassThree::class]);
$router->post('/{id}', [ClassOne::class,ClassTwo::class]);
// run

$httpPipelineCollection = new \Moon\Core\Collection\HttpPipelineArrayCollection([$httpUserPipeline, $httpContactPipeline]);
$httpPipelineCollection->add($httpProductPipeline);
$httpPipelineCollection->merge($router->pipelines());
$app->runWeb($httpPipelineCollection);


// If the app is a command application
////////////////////////////////////
////////////////// Cli
////////////////////////////////////
$cliUserPipeline = new \Moon\Core\Pipeline\CliPipeline('migrate', [ClassOne::class,ClassTwo::class,ClassThree::class]);
$cliProductPipeline = new \Moon\Core\Pipeline\CliPipeline('execute-queue', ClassOne::class);
// Or Cli (is a syntax sugar for command wrap a CliPipeline Object)
$cliCommand = new \Moon\Core\Cli('cache:');
$cliCommand->command('clear', [ClassOne::class,ClassTwo::class,ClassThree::class]);
$cliCommand->command('dump', [ClassOne::class,ClassTwo::class,ClassThree::class]);

// run
$cliPipelineCollection = new \Moon\Core\Collection\CliPipelineArrayCollection([$cliUserPipeline,$cliProductPipeline]);
$cliPipelineCollection->merge($cliCommand->pipelines());
$app->runCli($cliPipelineCollection);
```