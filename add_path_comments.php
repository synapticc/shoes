<?php

// add_path_comments.php

//  Add comment for PHP files only

// $root = __DIR__ . '/src';
// $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
//
// foreach ($files as $file) {
//     if (!$file->isFile() || $file->getExtension() !== 'php') continue;
//
//     $realPath = $file->getRealPath();
//     $relativePath = '//' . str_replace(__DIR__ . '/', '', $realPath);
//
//     $content = file_get_contents($realPath);
//     if (preg_match("/^<\?php\s*\/\/.*{$file->getFilename()}/m", $content)) continue;
//
//     $newContent = "<?php\n\n$relativePath\n" . preg_replace('/^<\?php\s*/', '', $content, 1);
//     file_put_contents($realPath, $newContent);
//
//     echo "Updated: $relativePath\n";
// }


//  Add comment for YAML files only

// $root = __DIR__;
// $configDir = $root . '/config';
//
// if (!is_dir($configDir)) {
//     echo "Config directory not found.\n";
//     exit(1);
// }
//
// $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configDir));
//
// foreach ($files as $file) {
//     if (!$file->isFile()) continue;
//
//     $ext = $file->getExtension();
//     if (!in_array($ext, ['yaml', 'yml'])) continue;
//
//     $realPath = $file->getRealPath();
//     $relativePath = str_replace($root . '/', '', $realPath);
//     $commentLine = "# $relativePath\n";
//
//     $content = file_get_contents($realPath);
//
//     // Skip if comment already exists
//     if (strpos($content, $commentLine) === 0) {
//         continue;
//     }
//
//     // Prepend comment
//     $newContent = $commentLine . ltrim($content, "\xEF\xBB\xBF");
//
//     file_put_contents($realPath, $newContent);
//     echo "Updated: $relativePath\n";
// }

$root = __DIR__;
$assetsDir = $root.'/assets/js';

if (!is_dir($assetsDir)) {
    echo "Assets directory not found.\n";
    exit(1);
}

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($assetsDir));

foreach ($files as $file) {
    if (!$file->isFile() || 'js' !== $file->getExtension()) {
        continue;
    }

    $realPath = $file->getRealPath();
    $relativePath = str_replace($root.'/', '', $realPath);
    $commentLine = "// $relativePath\n";

    $content = file_get_contents($realPath);

    // Skip if comment already exists
    if (0 === strpos($content, $commentLine)) {
        continue;
    }

    // Prepend comment
    $newContent = $commentLine.ltrim($content, "\xEF\xBB\xBF");
    file_put_contents($realPath, $newContent);

    echo "Updated: $relativePath\n";
}
