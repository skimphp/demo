<?php declare(strict_types=1);

/** @var Skim\Core\App $app */

// ── Public ────────────────────────────────────────────────────────────────────
$app->router->get('/',       [App\Controllers\HomeController::class, 'index']);
$app->router->get('/health', [App\Controllers\ApiController::class,  'health']);
$app->router->get('/error', [App\Controllers\HomeController::class, 'error']);
$app->router->get('/demo/economy', [App\Controllers\EconomyController::class, 'page']);
$app->router->get('/demo/economy/worker', [App\Controllers\EconomyController::class, 'worker']);
$app->router->get('/demo/economy/stream', [App\Controllers\EconomyController::class, 'stream']);

// ── Auth ──────────────────────────────────────────────────────────────────────
$app->router->get( '/login',  [App\Controllers\AuthController::class, 'loginForm']);
$app->router->post('/login',  [App\Controllers\AuthController::class, 'login']);
$app->router->get( '/logout', [App\Controllers\AuthController::class, 'logout']);

// ── Posts (public) ────────────────────────────────────────────────────────────
$app->router->get('/posts',          [App\Controllers\PostController::class, 'index']);
$app->router->get('/posts/@id:int',  [App\Controllers\PostController::class, 'show']);

// ── Admin (auth-protected) ────────────────────────────────────────────────────
$app->router->group('/admin', function (Skim\Core\Router $r): void {
    $r->get('',                         [App\Controllers\AdminController::class, 'dashboard']);
    $r->get('/users',                   [App\Controllers\AdminController::class, 'users']);
    $r->post('/users',                  [App\Controllers\AdminController::class, 'storeUser']);
    $r->post('/users/@id:int/delete',   [App\Controllers\AdminController::class, 'deleteUser']);
    $r->get('/posts',                   [App\Controllers\AdminController::class, 'posts']);
    $r->post('/posts',                  [App\Controllers\AdminController::class, 'storePost']);
    $r->get('/posts/@id:int',           [App\Controllers\AdminController::class, 'editPost']);
    $r->post('/posts/@id:int',          [App\Controllers\AdminController::class, 'updatePost']);
    $r->post('/posts/@id:int/delete',   [App\Controllers\AdminController::class, 'deletePost']);
}, middleware: [App\Middleware\AuthMiddleware::class]);

// ── API ───────────────────────────────────────────────────────────────────────
$app->router->group('/api', function (Skim\Core\Router $r): void {
    $r->get('/users',        [App\Controllers\ApiController::class, 'users']);
    $r->get('/posts',        [App\Controllers\ApiController::class, 'posts']);
    $r->get('/posts/@id:int',[App\Controllers\ApiController::class, 'postShow']);
});
