<?php

namespace Sneeuw\Http;

// TODO: actually implement a good `Request` class.
class Request
{
    public function __construct(
        /** @var mixed[] */
        public array $query,

        /** @var mixed[] */
        public array $body,

        /** @var mixed[] */
        public array $server,

        /** @var mixed[] */
        public array $files,

        /** @var mixed[] */
        public array $cookies
    ) {}

    public static function capture(): Request
    {
        return new Request($_GET, $_POST, $_SERVER, $_FILES, $_COOKIE);
    }
}
