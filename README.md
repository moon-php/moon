Defining Moon\App api

```php
<?php

$app = new \Moon\Core\App(/*Container instance*/);

// Add generic pipelines to you app
$app->pipe([Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);
$app->pipe(Fqcn\ClassOne::class);



// If the app is a http application
////////////////////////////////////
////////////////// Web
////////////////////////////////////
$httpUserPipeline = new \Moon\Core\Pipeline\HttpPipeline('method', 'regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class]);
$httpContactPipeline = new \Moon\Core\Pipeline\HttpPipeline('method', 'regex', Fqcn\Class::class);
$httpProductPipeline = new \Moon\Core\Pipeline\HttpPipeline('method', 'regex');
$httpProductPipeline->pipe([Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);

// Or Router (is a syntax sugar for web api wrap a HttpPipeline Object)
$router = new \Moon\Core\Router();
$router->get('regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);
$router->post('regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class]);
// run

$httpPipelineCollection = new \Moon\Core\Collection\HttpPipelineArrayCollection([$httpUserPipeline, $httpContactPipeline]);
$httpPipelineCollection->add($httpProductPipeline);
$httpPipelineCollection->merge($router->pipelines());
$app->runWeb($httpPipelineCollection);


// If the app is a command application
////////////////////////////////////
////////////////// Cli
////////////////////////////////////
$cliUserPipeline = new \Moon\Core\Pipeline\CliPipeline('regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);
$cliProductPipeline = new \Moon\Core\Pipeline\CliPipeline('regex', Fqcn\ClassOne::class);
// Or Cli (is a syntax sugar for command wrap a CliPipeline Object)
$cliCommand = new \Moon\Core\Cli();
$cliCommand->command('regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);

// run
$cliPipelineCollection = new \Moon\Core\Collection\CliPipelineArrayCollection([$cliUserPipeline,$cliProductPipeline]);
$cliPipelineCollection->merge($cliCommand->pipelines());
$app->runCli($cliPipelineCollection);
```