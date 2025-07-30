#!/usr/bin/env php
<?php

// Directory with blade files
$bladeDir = __DIR__ . '/resources/views/livewire';

// List of all blade files in directory
$bladeFiles = glob($bladeDir . '/*.blade.php');

foreach ($bladeFiles as $file) {
    $content = file_get_contents($file);

    // Replace all translation calls
    $content = preg_replace('/\{\{\s*__\([\'"]([^\'"]+)[\'"]\)\s*\}\}/', '{{ __(\'resources-components::messages.$1\') }}', $content);
    $content = preg_replace('/title="\{\{\s*__\([\'"]([^\'"]+)[\'"]\)\s*\}\}"/', 'title="{{ __(\'resources-components::messages.$1\') }}"', $content);

    // Save the updated file
    file_put_contents($file, $content);

    echo "Updated: " . basename($file) . PHP_EOL;
}

echo "Done!\n";
