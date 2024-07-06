<?php

namespace Sneeuw\Http;

// TODO: actually implement a good `Response` class.
class Response
{
    public function __construct(
        private int $code,
        private ?string $data = null
    ) {}

    public function send(): void
    {
        http_response_code($this->code);

        if ($this->data !== null) {
            echo $this->data;
        }
    }
}
