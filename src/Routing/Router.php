<?php

namespace Sneeuw\Routing;

use Sneeuw\Http\Request;
use Sneeuw\Http\Response;

/**
 * Is used by the `Application` to route incoming requests to the correct
 * handler.
 */
final class Router
{
    /**
     * The routes to match on.
     *
     * @var Route[]
     */
    private array $routes;

    public function __construct()
    {
        $this->routes = [];
    }

    /**
     * Matches the incoming request to a handler/action, executes it and returns
     * the response.
     */
    public function execute(Request $request): ?Response
    {
        $path = parse_url($request->uri, PHP_URL_PATH);
        $host = parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST);

        $this->sort();

        foreach ($this->routes as $route) {
            if ($route->method->value !== $request->method) {
                continue;
            }

            $url = getenv('APP_URL');
            $subdomain = substr(str_replace($url, '', $host), 0, -1);
            if (! empty($subdomain) && $route->subdomain !== $subdomain) {
                continue;
            }

            $pattern = $route->path;

            // Convert path parameters to regex named capture groups.
            $pattern = preg_replace(
                '/\{([a-zA-Z0-9_]+)\}/',
                '(?P<$1>[a-zA-Z0-9\-._~!$&\'()*+,;=:@%]+)',
                $pattern);

            // Convert optional path parameters
            $pattern = preg_replace(
                '/\{([a-zA-Z0-9_]+)\?\}/',
                '(?:/(?P<$1>[a-zA-Z0-9\-._~!$&\'()*+,;=:@%]*))?',
                $pattern);

            $pattern = '#^'.$pattern.'$#';
            if (preg_match($pattern, $path, $matches)) {
                $namedGroups = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $fn = $route->handler;

                return new Response($fn($request, ...$namedGroups));
            }
        }

        // TODO: return 404
        return null;
    }

    /**
     * Adds routes or a single route.
     *
     * @param  Route|Route[]  $routes
     */
    public function add(Route|array $route): void
    {
        if (is_array($route)) {
            array_push($this->routes, ...$route);

            return;
        }

        $this->routes[] = $route;
    }

    /**
     * Sorts the routes based on priority.
     */
    private function sort(): void
    {
        //
    }
}
