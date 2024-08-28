<?php

namespace Sneeuw\Routing;

use Sneeuw\Http\Request;
use Sneeuw\Http\Response;
use Sneeuw\Http\StatusCode;

/**
 * Routes incoming requests to the correct handler.
 */
final class Router
{
    /** @var Route[] */
    private array $routes = [];

    /**
     * Matches the incoming request to a handler/action, executes it and returns the response.
     */
    public function execute(Request $request): ?Response
    {
        foreach ($this->routes as $route) {
            if ($this->matchRoute($route, $request)) {
                $path = parse_url($request->uri, PHP_URL_PATH);
                $params = $this->extractParams($route->path, $path);

                $handler = $route->handler;

                return new Response($handler($request, ...$params));
            }
        }

        return new Response(null, StatusCode::NOT_FOUND);
    }

    /**
     * Adds routes or a single route.
     *
     * @param  Route|Route[]  $route
     */
    public function add(Route|array $route): void
    {
        if (is_array($route)) {
            array_push($this->routes, ...$route);

            return;
        }

        $this->routes[] = $route;
    }

    private function matchRoute(Route $route, Request $request): bool
    {
        if ($route->method->value !== $request->method) {
            return false;
        }

        $subdomain = $request->subdomain();
        if ($subdomain !== null && $route->subdomain !== $subdomain) {
            return false;
        }

        if (strlen($request->uri) > 1 && substr($request->uri, -1) === '/') {
            return false;
        }

        $pattern = $this->convertPathToPattern($route->path);
        $path = parse_url($request->uri, PHP_URL_PATH);

        return preg_match($pattern, $path) === 1;
    }

    private function extractParams(string $routePath, string $requestPath): array
    {
        $pattern = $this->convertPathToPattern($routePath);
        preg_match($pattern, $requestPath, $matches);

        array_shift($matches);

        $matches = array_filter($matches, fn ($v) => $v !== '');

        return $matches;
    }

    /**
     * @return non-empty-string
     */
    private function convertPathToPattern(string $path): string
    {
        // Escape forward slashes
        $path = str_replace('/', '\/', $path);

        // Convert optional parameters
        $path = preg_replace('/\{(\w+)\?\}/', '([^\/]*)?', $path);

        // Convert required parameters
        $path = preg_replace('/\{(\w+)\}/', '([^\/]+)', $path);

        // Ensure optional routes can be accessed correctly when no parameter is given
        $path = str_replace("\/([^\/]*)?", "\/?([^\/]*)", $path);

        // Ensure the pattern matches the whole path
        return '/^'.$path.'$/';
    }
}
