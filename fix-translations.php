#!/usr/bin/env php
<?php

// Directory with blade files
$bladeDir = __DIR__ . '/resources/views/livewire';

// List of all blade files in directory
$bladeFiles = glob($bladeDir . '/*.blade.php');

foreach ($bladeFiles as $file) {
    $content = file_get_contents($file);

    // Fix duplicate namespaces
    $content = preg_replace('/\'resources-components::messages\.resources-components::messages\.([^\']+)\'/', '\'resources-components::messages.$1\'', $content);

    // Save the updated file
    file_put_contents($file, $content);

    echo "Fixed: " . basename($file) . PHP_EOL;
}

echo "Done!\n";
