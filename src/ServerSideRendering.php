<?php

namespace Sneeuw;

class ServerSideRendering
{
    public static function render(string $path): string
    {
        return self::sendRenderRequest($path);
    }

    private static function sendRenderRequest(string $path): string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => json_encode(['path' => $path]),
            ],
        ]);

        return file_get_contents('http://localhost:2002', false, $context);
    }
}
