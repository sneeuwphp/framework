<?php

namespace Sneeuw;

class Render
{
    public static function sendRenderRequest(string $path): string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => json_encode(['path' => $path]),
            ],
        ]);

        $response = file_get_contents('http://localhost:2002', false, $context);

        return $response;
    }
}
