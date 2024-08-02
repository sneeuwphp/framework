<?php

namespace Sneeuw\Routing;

use Closure;
use Sneeuw\Http\HttpMethod;
use Sneeuw\Http\Request;
use Sneeuw\Http\Response;

class Router
{
    /**
     * The routes to match on.
     *
     * @var Route[]
     */
    private array $routes = [];

    /**
     * Finds the associated action for the given Request, executes it and
     * returns the reponse.
     *
     * Traditional routes come first, then come file-based routes. Static
     * routes also come before dynamic ones.
     */
    public function execute(Request $request): Response
    {
        /** @var string */
        $uri = $request->server['REQUEST_URI'];
        $path = parse_url($uri, PHP_URL_PATH);

        // Sort routes based on priority
        $this->sortRoutes();

        // Loop over the routes and find the first one that matches
        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $route->path);
            $pattern = '#^'.$pattern.'$#';

            if (preg_match($pattern, $path)) {
                $content = ($route->handler)($request);

                return new Response(200, $content);
            }
        }

        return new Response(404);
    }

    /**
     * Adds a route.
     *
     * @param  Closure(Request): string  $handler
     */
    public function addRoute(HttpMethod $method, string $path, Closure $handler, RouteType $type = RouteType::Traditional): void
    {
        $this->routes[] = new Route($method, $path, $handler, $type);
    }

    /**
     * Sorts the routes based on priority.
     */
    private function sortRoutes(): void
    {
        usort($this->routes, function (Route $a, Route $b) {
            if ($a->type === RouteType::Traditional && $b->type !== RouteType::Traditional) {
                return -1;
            }
            if ($a->type !== RouteType::Traditional && $b->type === RouteType::Traditional) {
                return 1;
            }

            $aHasBrace = strpos($a->path, '{') !== false;
            $bHasBrace = strpos($b->path, '{') !== false;

            if (! $aHasBrace && $bHasBrace) {
                return -1;
            }
            if ($aHasBrace && ! $bHasBrace) {
                return 1;
            }

            return 0;
        });
    }
}
