<?php

declare(strict_types=1);

namespace Moon\Core;


use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Moon\Core\Collection\CliPipelineCollectionInterface;
use Moon\Core\Collection\HttpPipelineCollectionInterface;
use Moon\Core\Exception\Exception;
use Moon\Core\Exception\InvalidArgumentException;
use Moon\Core\Matchable\RequestMatchable;
use Moon\Core\Matchable\StringMatchable;
use Moon\Core\Pipeline\AbstractPipeline;
use Moon\Core\Pipeline\HttpPipeline;
use Moon\Core\Pipeline\MatchablePipelineInterface;
use Moon\Core\Pipeline\PipelineInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class App extends AbstractPipeline implements PipelineInterface
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * App constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    ####################################################################################################################
    ####################################################################################################################
    # WEB
    ####################################################################################################################
    ####################################################################################################################

    /**
     * Run the web application, and return a Response
     *
     * @param HttpPipelineCollectionInterface $pipelines
     *
     * @return ResponseInterface
     *
     * @throws InvalidArgumentException
     */
    public function runWeb(HttpPipelineCollectionInterface $pipelines): ResponseInterface
    {
        $request = $this->container->get('request');
        $response = $this->container->get('response');

        if (!$request instanceof RequestInterface) {
            throw new InvalidArgumentException('Request must be a valid ' . RequestInterface::class . ' instance');
        }
        if (!$response instanceof ResponseInterface) {
            throw new InvalidArgumentException('Response must be a valid ' . ResponseInterface::class . ' instance');
        }

        $matchableRequest = new RequestMatchable($request);
        /** @var HttpPipeline $pipeline */
        foreach ($pipelines as $pipeline) {
            if ($pipeline->matchBy($matchableRequest)) {
                $pipelineResponse = $this->processWebStages(array_merge($this->stages(), $pipeline->stages()));

                if ($pipelineResponse instanceof ResponseInterface) {

                    return $pipelineResponse;
                }

                return $response->withBody($pipelineResponse);
            }
        }

        if ($methodNotAllowedResponse = $this->handleMethodNotAllowedResponse($response, $matchableRequest)) {

            return $methodNotAllowedResponse;
        }

        if ($routeNotFoundResponse = $this->handleNotFoundResponse($response)) {

            return $routeNotFoundResponse;
        }

        /** @var ResponseInterface $response */
        return $response->withStatus(500);
    }

    /**
     * Handle all the passed stages
     *
     * @param array $stages
     * @param mixed $args
     *
     * @return ResponseInterface|string
     *
     * @throws \Moon\Core\Exception\Exception
     */
    protected function processWebStages(array $stages, $args = null)
    {
        // Take the stage to handle and the next one (if exists)
        $currentStage = array_shift($stages);
        $nextStage = current($stages);

        // If is a string get the instance in the container
        if (is_string($currentStage)) {
            $currentStage = $this->container->get($currentStage);
        }

        if ($currentStage instanceof DelegateInterface) {

            // If $args is a instance of the request, use it for process the middleware
            // Otherwise use the one into the container
            if ($args instanceof RequestInterface) {
                $request = $args;
            }

            // TODO Think if may be possible to handle a response without force the middleware stack and pass it to a callable
            // Return the response if there's no more stage to execute, otherwise continue
            $response = $currentStage->process($request ?? $this->container->get('request'));

            if ($nextStage === false) {

                return $response;
            }

            return $this->processWebStages($stages, $response);
        }

        if ($currentStage instanceof MiddlewareInterface) {

            // Set the next middleware to execute as null or to a DelegateInterface as next stage to execute
            $next = null;
            if ($nextStage !== false && in_array(DelegateInterface::class, class_implements($nextStage), true)) {
                $next = $this->container->get($nextStage);
            }

            // If $args is a instance of the request, use it for process the middleware
            // Otherwise use the one into the container
            if ($args instanceof RequestInterface) {
                $request = $args;
            }

            // TODO Think if may be possible to handle a response without force the middleware stack and pass it to a callable
            // Return the response if there's no more stage to execute, otherwise continue
            $response = $currentStage->process($request ?? $this->container->get('request'), $next);

            if ($nextStage === false) {

                return $response;
            }

            return $this->processWebStages($stages, $response);
        }

        // If there's not next stage in the stack, return the result for this one
        if ($nextStage === false && is_callable($currentStage)) {

            return $currentStage($args ?: $this->container->get('request'));
        }

        // Process the current stage, and proceed to the stack
        if (is_callable($currentStage)) {

            return $this->processWebStages($stages, $currentStage($args ?: $this->container->get('request')));
        }

        throw new Exception("The stage '$currentStage' can't be handled");
    }

    /**
     * Return a Response for "not found" status
     *
     * @param ResponseInterface $response
     *
     * @return null|ResponseInterface
     */
    private function handleNotFoundResponse(ResponseInterface $response):?ResponseInterface
    {
        if (!$this->container->has('notFoundHandler')) {

            return $response->withStatus(404);
        }

        $notFoundHandler = $this->container->get('notFoundHandler');

        if ($notFoundHandler instanceof \Closure) {

            return $notFoundHandler();
        }

        return null;
    }

    /**
     * Return a Response for "method not allowed" status
     *
     * @param ResponseInterface $response
     * @param RequestMatchable $requestMatchable
     *
     * @return null|ResponseInterface
     */
    private function handleMethodNotAllowedResponse(ResponseInterface $response, RequestMatchable $requestMatchable):?ResponseInterface
    {
        if (!$requestMatchable->isPatternMatched()) {

            return null;
        }

        if (!$this->container->has('methodNotAllowedHandler')) {

            return $response->withStatus(405);
        }

        $notFoundHandler = $this->container->get('methodNotAllowedHandler');

        if ($notFoundHandler instanceof \Closure) {

            return $notFoundHandler();
        }

        return null;
    }

####################################################################################################################
####################################################################################################################
# CLI
####################################################################################################################
####################################################################################################################

    /**
     * Run the cli application
     *
     * @param CliPipelineCollectionInterface $pipelines
     *
     * @return void
     */
    public function runCli(CliPipelineCollectionInterface $pipelines): void
    {
        $input = $this->container->get('input');

        if (!$input instanceof InputInterface) {
            throw new InvalidArgumentException('input must be a valid ' . InputInterface::class . ' instance');
        }

        $matchableString = new StringMatchable($input->toString());

        /** @var MatchablePipelineInterface $pipeline */
        foreach ($pipelines as $pipeline) {
            if ($pipeline->matchBy($matchableString)) {
                $this->processCliStages(array_merge($this->stages(), $pipeline->stages()));
            }
        }
    }

    protected function processCliStages(array $stages): void
    {
        $payload = $this->container->get('input');

        foreach ($stages as $stage) {

            if (is_string($stage)) {
                $stage = $this->container->get($stage);
            }

            if (is_callable($stages)) {
                $payload = $stage($payload);
                continue;
            }

            throw new Exception("The stage '$stage' can't be handled");
        }
    }
}