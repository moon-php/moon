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
$httpUserPipeline = new \Moon\Core\Pipeline\HttpPipeline('method', 'regex');
$httpUserPipeline->pipe([Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);
$httpUserPipeline->pipe(Fqcn\Class::class);
$httpContactPipeline = new \Moon\Core\Pipeline\HttpPipeline('method', 'regex');
$httpContactPipeline->pipe([Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);
$httpContactPipeline->pipe(Fqcn\Class::class);

// Or Router (is a syntax sugar for web api wrap a HttpPipeline Object)
$router = new \Moon\Core\Router();
$router->get('regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);
$router->post('regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class]);
// run
$app->runWeb([$httpUserPipeline, $httpContactPipeline, $router->pipelines()]);


// If the app is a command application
////////////////////////////////////
////////////////// Cli
////////////////////////////////////
$cliUserPipeline = new \Moon\Core\Pipeline\CliPipeline('regex');
$cliUserPipeline->pipe([Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);
$cliUserPipeline->pipe(Fqcn\Class::class);
// Or Cli (is a syntax sugar for command wrap a CliPipeline Object)
$cliCommand = new \Moon\Core\Cli();
$cliCommand->command('regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);

// run
$app->runCli([$cliUserPipeline, $cliCommand->pipelines()]);
```