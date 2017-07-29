<?php

declare(strict_types=1);

namespace Moon\Moon\Matchable;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use ReflectionProperty;

class RequestMatchableTest extends TestCase
{
    public function testConstruct()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $matchable = new RequestMatchable($request);
        $reflection = new ReflectionProperty($matchable, 'request');
        $reflection->setAccessible(true);
        $this->assertSame($request, $reflection->getValue($matchable));
    }

    /**
     * @dataProvider matchDataProvider
     */
    public function testMatch($pattern, $verbs, $verbPathToMatch, $urlPathToMatch, $expectedMatch, $expectedIsPatternMatched, array $expectedAttributes)
    {
        $uri = $this->prophesize(UriInterface::class);
        $uri->getPath()->shouldBeCalled(1)->willReturn($urlPathToMatch);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getUri()->willReturn($uri->reveal());
        $request->getMethod()->willReturn($verbPathToMatch);
        foreach ($expectedAttributes as $key => $value) {
            $request->withAttribute($key, $value)->shouldBeCalled()->willReturn($request);
        }
        $request = $request->reveal();
        $matchable = new RequestMatchable($request);
        $match = $matchable->match(['pattern' => $pattern, 'verbs' => $verbs]);

        $this->assertSame($match, $expectedMatch);
        $this->assertSame($matchable->isPatternMatched(), $expectedIsPatternMatched);
        $this->assertSame($matchable->requestWithAddedAttributes(), $request);
    }

    public function matchDataProvider()
    {
        return [
            ['/users', ['GET'], 'GET', '/users', true, true, []],
            ['/users', ['GET'], 'POST', '/users', false, true, []],
            ['/users/{id}', ['POST', 'GET'], 'POST', '/users/any', true, true, ['id' => 'any']],
            ['/users/{id::\d+}', ['POST', 'GET'], 'DELETE', '/users/string', false, false, []],
            ['/users/{id::\d+}', ['DELETE'], 'DELETE', '/users/1', true, true, ['id' => 1]],
            ['/string[/{id::\d+}]', ['PUT'], 'PUT', '/string/11111111111', true, true, ['id' => '11111111111']],
            ['/string[/[{id::\d+}]]', ['PUT'], 'PUT', '/string/', true, true, ['id' => '']],
            ['/string[/{id::\d+}]', ['PUT'], 'PUT', '/string/q', false, false, []],
            ['::/users/(?<attribute>\d+)', ['PUT'], 'PUT', '/users/1221', true, true, ['attribute' => '1221']],
            ['/sub/[{a}/[{b}/[{c}]]]', ['GET'], 'GET', '/sub/1/', true, true, ['a' => 1, 'b' => '', 'c' => '']],
            ['/sub/[{a}/[{b}/[{c}]]]', ['GET'], 'GET', '/sub/1/2/', true, true, ['a' => 1, 'b' => 2, 'c' => '']],
            ['/sub/[{a}/[{b}/[{c}]]]', ['GET'], 'GET', '/sub/1/2/3', true, true, ['a' => 1, 'b' => 2, 'c' => 3]],
        ];
    }
}