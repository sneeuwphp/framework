<?php

namespace Sneeuw;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Sneeuw\Http\HttpMethod;
use Sneeuw\Http\Request;
use Sneeuw\Routing\Router;
use SplFileInfo;

class Application
{
    private Router $router;

    /**
     * @var array<string, string>
     */
    private array $componentHashes = [];

    public function __construct()
    {
        $this->router = new Router;
    }

    /**
     * Instructs the Application to use file-based routes.
     *
     * This will walk recursively over all files/directories in the given path
     * and register each page as a GET route.
     */
    public function withFileBasedRoutes(string $pages, string $components): Application
    {
        // Discover pages and add GET routes
        $paths = $this->discoverFiles($pages);
        foreach ($paths as &$path) {
            $relativePath = substr($path, strlen(realpath('.').'/src'));
            $this->componentHashes[md5($relativePath)] = $relativePath;

            $originalRoutePath = substr($path, strlen($pages));
            $routePath = str_replace($originalRoutePath === '/index' ? 'index' : '/index', '', $originalRoutePath);

            $this->router->addRoute(HttpMethod::GET, $routePath, function () use ($originalRoutePath) {
                // TODO: think about how ssr can access current context?
                // probably: dry run first, see what components are executed with what
                // params and execute handlers accordingly, and render with given data
                return ServerSideRendering::render($originalRoutePath);
            });
        }

        // Discover components and generate hashes
        $paths = $this->discoverFiles($components);
        foreach ($paths as &$path) {
            $relativePath = substr($path, strlen(realpath('.').'/src'));
            $this->componentHashes[md5($relativePath)] = $relativePath;
        }

        // Add _internal route for handlers/actions
        $this->router->addRoute(HttpMethod::POST, '/_internal', function () {
            /** @var array{ hash: string, action: string, args: string[] } */
            $body = json_decode(file_get_contents('php://input'), true);

            $component = $this->componentHashes[$body['hash']];

            $contents = file_get_contents(realpath('.').'/src'.$component.'.ski');
            $handler = str_contains($contents, '---') ? explode('---', $contents)[0] : $contents;

            eval($handler);

            $data = json_encode(call_user_func_array($body['action'], $body['args'] ?? []));

            return $data === false ? '' : $data;
        });

        return $this;
    }

    /**
     * Recursively discovers files in the given directory and it's
     * subdirectories.
     *
     * @return string[]
     */
    private function discoverFiles(string $path): array
    {
        $directoryIterator = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directoryIterator);

        $files = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (in_array($file->getBasename(), ['.', '..'])) {
                continue;
            }

            $filename = $file->getBasename('.'.$file->getExtension());
            $pathWithoutExtension = $file->getPath().'/'.$filename;

            $files[] = $pathWithoutExtension;
        }

        return $files;
    }

    /**
     * Handles the given request.
     */
    public function handle(Request $request): void
    {
        $this->router->execute($request)->send();
    }
}
