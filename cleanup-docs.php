<?php

/**
 * Remove undocumented "Endpoints" group from Scribe generated HTML
 */

$htmlFile = __DIR__ . '/resources/views/scribe/index.blade.php';

if (!file_exists($htmlFile)) {
    echo "HTML file not found\n";
    exit(1);
}

$content = file_get_contents($htmlFile);

if ($content === false) {
    echo "✗ Failed to read HTML file\n";
    exit(1);
}

// Remove sidebar navigation items for endpoints group (entire <li> blocks)
$newContent = preg_replace('/<li>\s*<a href="#endpoints[^"]*">[^<]*<\/a>\s*<\/li>/s', '', $content);
if ($newContent === null) {
    echo "✗ Regex error on navigation cleanup\n";
    exit(1);
}
$content = $newContent;

// Remove the entire endpoints h1 section heading
$newContent = preg_replace('/<h1 id="endpoints">Endpoints<\/h1>/', '', $content);
if ($newContent !== null) {
    $content = $newContent;
}

// Remove all h2 elements with id starting with "endpoints-" and their content
// We'll use a more conservative approach - just remove the h2 tags themselves
$newContent = preg_replace('/<h2 id="endpoints-[^"]*">/', '<h2 id="removed-endpoint">', $content);
if ($newContent !== null) {
    $content = $newContent;
}

// Now remove sections with the removed marker
$newContent = preg_replace('/<h2 id="removed-endpoint">.*?(?=<h2|<h1|$)/s', '', $content);
if ($newContent !== null) {
    $content = $newContent;
}

// Clean up any remaining orphaned endpoints references in navigation
$newContent = preg_replace('/<a href="#endpoints-[^"]*">[^<]*<\/a>/', '', $content);
if ($newContent !== null) {
    $content = $newContent;
}

// Remove tocify-item list items with data-unique containing "endpoints-"
$newContent = preg_replace('/<li class="tocify-item[^"]*" data-unique="endpoints-[^"]*">.*?<\/li>/s', '', $content);
if ($newContent !== null) {
    $content = $newContent;
}

if (file_put_contents($htmlFile, $content) === false) {
    echo "✗ Failed to write HTML file\n";
    exit(1);
}

echo "✓ Cleaned up undocumented endpoints from HTML\n";
