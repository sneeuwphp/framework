<?php

namespace Sneeuw\Routing;

use Closure;
use Sneeuw\Http\HttpMethod;
use Sneeuw\Http\Request;

// TODO: is this smart?
class Route
{
    public function __construct(
        public HttpMethod $method,
        public string $path,
        /** @var Closure(Request $request): string */
        public Closure $handler,
        public RouteType $type = RouteType::Traditional,
    ) {}
}
