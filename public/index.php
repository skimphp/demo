<?php declare(strict_types=1);

$ts = microtime(true);

// Single entry point — all requests funnel here.
// Apache/Nginx must rewrite all requests to this file.
define('SKIM_ROOT', dirname(__DIR__));

require SKIM_ROOT . '/vendor/autoload.php';

$app = Skim\Core\App::instance();

// --- global middleware ---
// cors must run before auth — OPTIONS preflight must not hit auth checks.
$app->use(Skim\Middleware\Cors::class);

// toolbar runs last (after Controller) so it can read fully-populated profiler data.
// Only active when APP_DEBUG=true AND response is text/html AND not JSON/AJAX.
if (\Skim\Core\Config::get('app.debug', false)) {
    $app->use(Skim\Middleware\ToolbarMiddleware::class);
}

// --- routes ---
require SKIM_ROOT . '/routes.php';

// --- run ---
$app->run();

// Dev timing — disabled; it corrupts JSON/SSE responses.
// $ms = (microtime(true) - $ts) * 1000;
// echo 'time:' . $ms . ' ms';