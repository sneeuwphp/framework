<?php

namespace Sneeuw\Routing;

use Sneeuw\Http\HttpMethod;

final class RouteBag
{
    /**
     * @var Route[]
     */
    public array $routes = [];

    public ?string $subdomain = null;

    public function __construct(?string $subdomain = null)
    {
        $this->subdomain = $subdomain;
    }

    /**
     * Adds a traditional GET route.
     */
    public function get(string $path, mixed $handler): void
    {
        $this->routes[] = new Route(
            HttpMethod::GET,
            $this->subdomain,
            $path,
            $handler,
            RouteType::Traditional
        );
    }
}
