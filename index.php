<?php
/**
 * Alfa Egypt ERP System - Shared Hosting Front Controller Bridge
 */
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)
);

// If request points directly to a public file, let it serve or rewrite
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

require_once __DIR__ . '/public/index.php';
