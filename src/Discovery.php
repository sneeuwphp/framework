<?php

namespace Sneeuw;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Used to discover files and directories.
 */
final readonly class Discovery
{
    /**
     * Recursively discovers files in the given directory and its
     * subdirectories.
     *
     * @return string[]
     */
    public static function discoverFiles(string $path): array
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
}
