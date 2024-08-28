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
    public array $routes = [];

    /**
     * Matches the incoming request to a handler/action, executes it and returns the response.
     */
    public function execute(Request $request): ?Response
    {
        $this->sort();

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
        $path = str_replace('/', '\/', $path);

        $path = preg_replace('/\{(\w+)\?\}/', '([^\/]*)?', $path);
        $path = preg_replace('/\{(\w+)\}/', '([^\/]+)', $path);

        $path = str_replace("\/([^\/]*)?", "\/?([^\/]*)", $path);

        return '/^'.$path.'$/';
    }

    /**
     * Sorts routes based on how many segments it has, whether it is static or
     * dynamic and if the route is dynamic, also sort on whether the parameters
     * are optional or required.
     */
    public function sort(): void
    {
        usort($this->routes, function (Route $a, Route $b) {
            $aSegments = explode('/', trim($a->path, '/'));
            $bSegments = explode('/', trim($b->path, '/'));

            // Prioritize routes with more segments
            $segmentCountDiff = count($aSegments) - count($bSegments);
            if ($segmentCountDiff !== 0) {
                return $segmentCountDiff < 0 ? 1 : -1;
            }

            foreach ($aSegments as $index => $segment) {
                $aIsDynamic = preg_match('/\{(\w+)\??\}/', $segment);
                $bIsDynamic = preg_match('/\{(\w+)\??\}/', $bSegments[$index]);

                // Prioritize static routes
                if ($aIsDynamic && ! $bIsDynamic) {
                    return 1;
                } elseif (! $aIsDynamic && $bIsDynamic) {
                    return -1;
                }

                if ($aIsDynamic && $bIsDynamic) {
                    $aIsOptional = strpos($segment, '?') !== false;
                    $bIsOptional = strpos($bSegments[$index], '?') !== false;

                    // Prioritize required parameters
                    if ($aIsOptional && ! $bIsOptional) {
                        return 1;
                    } elseif (! $aIsOptional && $bIsOptional) {
                        return -1;
                    }
                }
            }
        });
    }
}
