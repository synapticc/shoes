<?php
$directory = __DIR__ . '/src/Controller';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $txtFile = $file->getPathname() . '.txt';
        if (copy($file->getPathname(), $txtFile)) {
            echo "Copied: " . $file->getFilename() . " → " . basename($txtFile) . "\n";
        } else {
            echo "Failed: " . $file->getFilename() . "\n";
        }
    }
}
?>   