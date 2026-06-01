<?php
/**
 * Application bootstrap — paths, session, and URL helpers.
 */
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('APP_TIMEZONE', 'Asia/Kathmandu');

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

/**
 * Convert UTC datetime string from DB to app local timezone.
 */
function format_utc_datetime(?string $utcDatetime, string $format = 'M d, Y - h:i A'): string
{
    if (empty($utcDatetime)) {
        return 'N/A';
    }

    $utc = new DateTimeZone('UTC');
    $local = new DateTimeZone(APP_TIMEZONE);

    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $utcDatetime, $utc);
    if (!$dt) {
        try {
            $dt = new DateTimeImmutable($utcDatetime, $utc);
        } catch (Exception $e) {
            return 'N/A';
        }
    }

    return $dt->setTimezone($local)->format($format);
}

function local_now(string $format = 'd M Y'): string
{
    return (new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE)))->format($format);
}

require_once ROOT_PATH . '/config/database.php';
