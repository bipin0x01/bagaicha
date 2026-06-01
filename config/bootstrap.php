<?php
/**
 * Application bootstrap — paths, session, and URL helpers.
 */
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('STORAGE_PATH', ROOT_PATH . '/storage');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Public asset URL (css, js, images). */
function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

/** Application page URL. */
function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

require_once ROOT_PATH . '/config/database.php';
