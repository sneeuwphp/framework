<?php

namespace Sneeuw;

final readonly class Environment
{
    // TODO: currently does not support trailing comments in .env file
    // TODO: actually create usable class with get/set methods
    public static function readFromFile(string $path): void
    {
        $contents = file_get_contents($path);
        $lines = explode("\n", $contents);

        foreach ($lines as $line) {
            $line = trim($line);

            if (! empty($line) && $line[0] !== '#') {
                putenv($line);
            }
        }
    }
}
