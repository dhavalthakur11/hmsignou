<?php
/**
 * Entry Point
 * All requests are routed through here via .htaccess
 */
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH',  BASE_PATH . '/app');
define('CORE_PATH', BASE_PATH . '/core');
$envPath = dirname(__DIR__) . '/.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;

        [$name, $value] = explode('=', $line, 2);
        putenv("$name=$value");
    }
}

// Autoload core classes
spl_autoload_register(function ($class) {
    $paths = [CORE_PATH, APP_PATH . '/models', APP_PATH . '/controllers/auth',
              APP_PATH . '/controllers/admin', APP_PATH . '/controllers/modules',
              APP_PATH . '/middleware'];
    foreach ($paths as $path) {
        $file = $path . '/' . $class . '.php';
        if (file_exists($file)) { require_once $file; return; }
    }
});

// Load helpers
require_once APP_PATH . '/helpers/session_helper.php';
require_once APP_PATH . '/helpers/url_helper.php';

// Bootstrap the app
require_once CORE_PATH . '/App.php';
$app = new App();