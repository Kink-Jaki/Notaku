<?php

// Search for any modifications to forElseCounter
$dir = 'vendor/laravel/framework/src/Illuminate/View/';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (strpos($content, 'forElseCounter') !== false) {
            echo $file->getPathname()."\n";
            // Find lines with forElseCounter
            $lines = explode("\n", $content);
            foreach ($lines as $i => $line) {
                if (strpos($line, 'forElseCounter') !== false) {
                    echo '  Line '.($i + 1).': '.trim($line)."\n";
                }
            }
        }
    }
}

// Also check app directory for any overrides
echo "\n--- Checking app directory ---\n";
$dir2 = 'app/';
if (is_dir($dir2)) {
    $files2 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir2));
    foreach ($files2 as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            if (strpos($content, 'CompilesLoops') !== false || strpos($content, 'forElseCounter') !== false) {
                echo $file->getPathname()."\n";
                echo "  FOUND OVERRIDE!\n";
            }
        }
    }
}

// Check if any service provider or macro modifies the blade compiler
echo "\n--- Checking for blade compiler macros or overrides ---\n";
$files3 = glob('app/Providers/*.php');
foreach ($files3 as $f) {
    $content = file_get_contents($f);
    if (strpos($content, 'Blade') !== false) {
        echo $f."\n";
        echo substr($content, 0, 200)."\n";
    }
}
