<?php
/**
 * URL Helper
 * Provides base_url() and asset_url() for portable links.
 */

function base_url(string $path = ''): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Example: /hmsignou/public/index.php -> /hmsignou/public
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base   = rtrim($protocol . '://' . $host . $script, '/');

    return $base . ($path ? '/' . ltrim($path, '/') : '');
}

function asset_url(string $path): string {
    return base_url(ltrim($path, '/'));
}

function current_url(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host . ($_SERVER['REQUEST_URI'] ?? '/');
}