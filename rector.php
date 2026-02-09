<?php

// rector.php
use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withSets([
        SymfonySetList::SYMFONY_80, // Adjust version as needed
    ])
    ->withIndent(indentChar: ' ', indentSize: 4) // 4 spaces
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/config',
    ]);
