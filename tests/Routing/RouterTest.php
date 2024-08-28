<?php

use PHPUnit\Framework\TestCase;
use Sneeuw\Http\HttpMethod;
use Sneeuw\Routing\Route;
use Sneeuw\Routing\Router;
use Sneeuw\Routing\RouteType;

class RouterTest extends TestCase
{
    /**
     * Tests whether the `Router` correctly matches routes and calls the
     * correct handler. Routes should be in order of specificity and whether or
     * not the route is static or dynamic, it's parameters are required or
     * optional and whether the route is traditional or file-based.
     */
    public function testCorrectHandlerCalledForRoute(): void
    {
        $createRouteFromPath = function (string $path): Route {
            return new Route(HttpMethod::GET, null, $path, '', RouteType::Traditional);
        };

        $routes = array_map($createRouteFromPath, [
            '/posts/{id}',
            '/posts/{id}/{slug?}',
            '/posts/edit',
            '/{id}',
            '/{id}/{sec?}',
            '/static',
        ]);

        $expectedOrder = [
            '/posts/{id}/{slug?}',
            '/posts/edit',
            '/posts/{id}',
            '/{id}/{sec?}',
            '/static',
            '/{id}',
        ];

        $router = new Router;
        $router->add($routes);
        $router->sort();

        $actualOrder = array_map(fn ($r) => $r->path, $router->routes);

        $this->assertSame($expectedOrder, $actualOrder);
    }
}
