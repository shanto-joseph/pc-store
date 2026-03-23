<?php
/**
 * Minimal .env loader — no Composer required.
 * Reads key=value pairs from .env and defines them as constants.
 */
function load_env(string $path): void {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));

        if (!empty($key) && !defined($key)) {
            define($key, $value);
        }
    }
}

load_env(__DIR__ . '/.env');
