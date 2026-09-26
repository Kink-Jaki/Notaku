<?php

// Manually compile the blade template to see what output it generates
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$compiler = app('view')->getEngineResolver()->resolve('blade');
echo 'Compiler class: '.get_class($compiler)."\n";

// Get the blade compiler
$reflection = new ReflectionClass($compiler);
$prop = $reflection->getProperty('compiler');
$prop->setAccessible(true);
$innerCompiler = $prop->getValue($compiler);

// Get the forElseCounter
$prop2 = new ReflectionProperty($innerCompiler, 'forElseCounter');
$prop2->setAccessible(true);
echo 'forElseCounter before: '.$prop2->getValue($innerCompiler)."\n";

// Read the blade template
$bladePath = 'vendor/laravel/framework/src/Illuminate/Foundation/resources/exceptions/renderer/markdown.blade.php';
$bladeContent = file_get_contents($bladePath);

// Compile it
$compiled = $innerCompiler->compileString($bladeContent);
echo "\nCompiled output (first 2000 chars):\n";
echo substr($compiled, 0, 2000)."\n";

// Check for _-1 in the output
if (strpos($compiled, '$-1') !== false || preg_match_all('/\$__empty_\s*-\d+/', $compiled, $m)) {
    echo "\nWARNING: Found \$__empty_-1 in compiled output!\n";
    preg_match_all('/\$__empty_-\d+/', $compiled, $matches);
    echo 'Matches: '.implode(', ', $matches[0])."\n";
}
echo "\nforElseCounter after: ".$prop2->getValue($innerCompiler)."\n";
