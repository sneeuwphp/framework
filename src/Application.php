<?php

namespace Sneeuw;

use Sneeuw\Http\HttpMethod;
use Sneeuw\Http\Request;
use Sneeuw\Routing\Route;
use Sneeuw\Routing\RouteBag;
use Sneeuw\Routing\Router;
use Sneeuw\Routing\RouteType;

/**
 * Represents the entire application.
 */
final readonly class Application
{
    private Router $router;

    private string $root;

    public function __construct(string $root)
    {
        Environment::readFromFile($root.'/../.env');

        $this->root = $root;
        $this->router = new Router;
    }

    /**
     * Instructs the `Application` to use file-based routes.
     *
     * Accepts a string pointing to the directory containing the pages structure
     * and alternatively accepts an array mapping specific subdomains to
     * directories. Use placeholders ({id}) for dynamic subdomains and an empty
     * string to match url's without a subdomain.
     *
     * @param  string|array<string,string>  $pages
     */
    public function withFileBasedRoutes(string|array $pages): Application
    {
        $paths = is_array($pages) ? $pages : ['' => $pages];
        foreach ($paths as $subdomain => $path) {
            $filePaths = Discovery::discoverFiles($path);
            foreach ($filePaths as $filePath) {
                $targetFilePath = substr($filePath, strlen($path));
                $routePath = str_replace($targetFilePath === '/index' ? 'index' : '/index', '', $targetFilePath);

                $this->router->add(new Route(
                    HttpMethod::GET,
                    $subdomain,
                    $routePath,
                    function ($request, ...$routeParameters) {
                        // TODO: render the page at $targetFilePath
                    },
                    RouteType::FileBased,
                ));
            }
        }

        return $this;
    }

    /**
     * Instructs the `Application` to use traditional routes.
     *
     * Accepts a string pointing to a routes file, or an array of strings
     * pointing to multiple route files and alternatively accepts an associative
     * array mapping specific subdomains to routes files.
     *
     * @param  string|array<string,string>  $pages
     */
    public function withTraditionalRoutes(string|array $pages): Application
    {
        $paths = is_array($pages) ? $pages : ['' => $pages];
        $isList = array_is_list($paths);

        foreach ($paths as $subdomain => $path) {
            $bag = new RouteBag($isList ? null : $subdomain);

            /** @var callable */
            $fn = include $path;
            $fn($bag);
            $this->router->add($bag->routes);
        }

        return $this;
    }

    /**
     * Handles the given request and send a response back to the client.
     */
    public function handle(Request $request): void
    {
        $response = $this->router->execute($request);
        if ($response !== null) {
            $response->send();
        }
    }
}
