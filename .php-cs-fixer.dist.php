<?php

//.php-cs-fixer.dist.php
$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->in(__DIR__.'/config')
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => false,
        'declare_strict_types' => false,
        '@PSR12' => true,
        // Ensures consistent indentation (spaces/tabs)
        'indentation_type' => true,
        'array_indentation' => true,
        'binary_operator_spaces' => ['default' => 'single_space'],
        'ordered_imports' => [
             // Sorts alphabetically
            'sort_algorithm' => 'alpha',
            // Order of types
            'imports_order' => ['class', 'function', 'const']
        ],
        'no_unused_imports' => true
    ])
    ->setFinder($finder)
    ->setIndent('    ') // 4 spaces
    ->setLineEnding("\n")
;
