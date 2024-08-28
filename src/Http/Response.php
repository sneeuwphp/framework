<?php

namespace Sneeuw\Http;

/**
 * Represents an HTTP response.
 */
class Response
{
    public function __construct(
        public ?string $body = null,
        public StatusCode $code = StatusCode::OK,
    ) {}

    public function send(): void
    {
        http_response_code($this->code->value);
        if ($this->body !== null) {
            echo $this->body;
        }
    }
}
