<?php

namespace Sneeuw\Http;

/**
 * Represents an HTTP request and contains useful information about a request
 * and gives you handles to read possible incoming data.
 */
class Request
{
    public function __construct(
        /**
         * Name and revision of the information protocol via which the page was
         * requested; e.g. 'HTTP/1.0'
         */
        public string $serverProtocol,

        /**
         *  Which request method was used to access the page.
         */
        public string $method,

        /**
         * The timestamp of the start of the request.
         */
        public int $time,

        /**
         * The timestamp of the start of the request, with microsecond
         * precision.
         */
        public float $timeFloat,

        /**
         * The query string, if any, via which the page was accessed.
         */
        public ?string $queryString,

        /**
         * Whether or not the request was queried through the HTTPS protocol.
         */
        public bool $https,

        /**
         * The IP address from which the user is viewing the current page.
         */
        public string $remoteAddress,

        /**
         * The Host name from which the user is viewing the current page. The
         * reverse dns lookup is based on the REMOTE_ADDR of the user.
         */
        public ?string $remoteHost,

        /**
         * The port being used on the user's machine to communicate with the web
         * server.
         */
        public string $remotePort,

        /**
         * The URI which was given in order to access this page; for instance,
         * '/index.html'.
         */
        public string $uri,

        /**
         * When doing Digest HTTP authentication this variable is set to the
         * 'Authorization' header sent by the client (which you should then use
         * to make the appropriate validation).
         */
        public ?string $authDigest,

        /**
         * When doing HTTP authentication this variable is set to the username
         * provided by the user.
         */
        public ?string $authUser,

        /**
         * When doing HTTP authentication this variable is set to the password
         * provided by the user.
         */
        public ?string $authPassword,

        /**
         * When doing HTTP authentication this variable is set to the
         * authentication type.
         */
        public ?string $authType,

        /**
         * Contains any client-provided pathname information trailing the actual
         * script filename but preceding the query string, if available. For
         * instance, if the current script was accessed via the URI
         * http://www.example.com/php/path_info.php/some/stuff?foo=bar, then
         * $_SERVER['PATH_INFO'] would contain /some/stuff.
         */
        public ?string $pathInfo,

        /**
         * Original version of 'PATH_INFO' before processed by PHP.
         */
        public ?string $originalPathInfo,

        /**
         * Contains an associative array of URL parameters (query string).
         *
         * @var array<string, string>
         */
        public array $query,

        /**
         * Contains an associative array of values submitted via the HTTP POST
         * method when using `application/x-www-form-urlencoded` or
         * `multipart/form-data` as the HTTP Content-Type.
         *
         * @var array<mixed, mixed>
         */
        public array $form,

        /**
         * Contains an associative array of HTTP cookies.
         *
         * @var array<string, string>
         */
        public array $cookies,

        /**
         * Contains an associative array of uploaded files.
         *
         * @var array<mixed, mixed>
         */
        public array $files,
    ) {}

    /**
     * Captures an HTTP request from the current environment using superglobals.
     */
    public static function capture(): Request
    {
        return new Request(
            $_SERVER['SERVER_PROTOCOL'],
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_TIME'],
            $_SERVER['REQUEST_TIME_FLOAT'],
            $_SERVER['QUERY_STRING'] ?? null,
            (bool) ($_SERVER['HTTPS'] ?? false),
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['REMOTE_HOST'] ?? null,
            $_SERVER['REMOTE_PORT'],
            $_SERVER['REQUEST_URI'],
            $_SERVER['PHP_AUTH_DIGEST'] ?? null,
            $_SERVER['PHP_AUTH_USER'] ?? null,
            $_SERVER['PHP_AUTH_PW'] ?? null,
            $_SERVER['AUTH_TYPE'] ?? null,
            $_SERVER['PATH_INFO'] ?? null,
            $_SERVER['ORIG_PATH_INFO'] ?? null,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES,
        );
    }

    /**
     * Reads the entire body into a string and decodes the JSON body into an
     * associative array, null or scalar type.
     */
    public function json(): null|bool|int|float|string|array
    {
        return json_decode($this->body(), true);
    }

    /**
     * Reads the entire body into a string and returns the data or false on
     * failure.
     */
    public function body(): string|false
    {
        return file_get_contents('php://input');
    }

    /**
     * Returns a stream to the body.
     *
     * @return resource|null;
     */
    public function getBodyStream(): mixed
    {
        return fopen('php://input', 'rb');
    }

    /**
     * Returns the subdomain part of the URL.
     */
    public function subdomain(): ?string
    {
        $appUrl = getenv('APP_URL');
        $host = parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST);

        $onlySubdomain = substr(str_replace($appUrl, '', $host), 0, -1);

        return empty($onlySubdomain) ? null : $onlySubdomain;
    }
}
