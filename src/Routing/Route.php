<?php

namespace Sneeuw\Routing;

use Closure;
use Sneeuw\Http\HttpMethod;

// TODO: is this smart?
class Route
{
    public function __construct(
        public HttpMethod $method,
        public string $path,
        /** @var Closure(string): string */
        public Closure $handler,
        public RouteType $type = RouteType::Traditional,
    ) {}
}
