<?php

namespace Sneeuw\Routing;

use Sneeuw\Http\HttpMethod;
use Sneeuw\Http\Request;
use Sneeuw\Http\Response;

final readonly class Route
{
    public function __construct(
        public HttpMethod $method,
        public ?string $subdomain,
        public string $path,
        /**
         * The handler callable which will be called when executing this route.
         *
         * @var callable(Request, mixed...): Response
         */
        public mixed $handler,
        public RouteType $type,
    ) {}
}
