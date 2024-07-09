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

    public function __construct()
    {
        $this->router = new Router;

        /*$this->router->addRoute(HttpMethod::POST, '/_internal', function (Request $request) {*/
        /*    $body = file_get_contents('php://input');*/
        /*    $decoded = json_decode($body, true);*/
        /*});*/
    }

    /**
     * Instructs the Application to use file-based routes.
     *
     * This will walk recursively over all files/directories in the given path
     * and register each page as a GET route.
     */
    public function withFileBasedRoutes(string $path): Application
    {
        $directoryIterator = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directoryIterator);

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (in_array($file->getBasename(), ['.', '..'])) {
                continue;
            }

            $filename = $file->getBasename('.'.$file->getExtension());
            $pathWithoutExtension = $file->getPath().'/'.$filename;
            $pathFromPagesRoot = substr($pathWithoutExtension, strlen($path));

            $this->router->addRoute(
                HttpMethod::GET,
                $pathFromPagesRoot,
                function (Request $request) use ($pathFromPagesRoot) {
                    $pathFromPagesRoot = ltrim($pathFromPagesRoot, '/');

                    return Render::sendRenderRequest($pathFromPagesRoot);
                }
            );
        }

        return $this;
    }

    /**
     * Handles the given request.
     */
    public function handle(Request $request): void
    {
        $this->router->execute($request)->send();
    }
}
