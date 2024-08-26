<?php

namespace Sneeuw\Http;

/**
 * Represents an HTTP response.
 */
class Response
{
    public function __construct(
        public string $body,
        public StatusCode $code = StatusCode::OK,
    ) {}

    public function send(): void
    {
        http_response_code($this->code->value);
        echo $this->body;
    }
}
