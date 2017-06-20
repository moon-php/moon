Defining Moon\App api

```php
<?php
use Moon\Container;
use Moon\App;
$container = new Container([]);
$app = new App($container);

// Add generic pipelines to you app
$app->pipe([Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);
$app->pipe([Fqcn\ClassOne::class]);



////////////////////////////////////
////////////////// Web
////////////////////////////////////
$httpUserPipeline = new HttpPipeline();
$httpUserPipeline->pipe('method', 'regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);
$httpUserPipeline->pipe('method', 'regex', [Fqcn\Class::class]);
$httpContactPipeline = new HttpPipeline();
$httpContactPipeline->pipe('method', 'regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);
$httpContactPipeline->pipe('method', 'regex', [Fqcn\Class::class]);

// Or Router (is a syntax sugar for web api wrap a HttpPipeline Object)
$router = new Router();
$router->get('regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);
$router->post('regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class]);
// run
$app->runWeb([$httpUserPipeline, $httpContactPipeline, $router]);



////////////////////////////////////
////////////////// Cli
////////////////////////////////////
$cliUserPipeline = new CliPipeline();
$cliUserPipeline->pipe('regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);
$cliUserPipeline->pipe('regex', [Fqcn\Class::class]);
// Or Cli (is a syntax sugar for command wrap a CliPipeline Object)
$cliCommand = new Cli();
$cliCommand->command('regex', [Fqcn\ClassOne::class,Fqcn\ClassTwo::class,Fqcn\ClassThree::class]);
// run
$app->runCli([$cliUserPipeline, $cliCommand]);
```