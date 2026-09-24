<?php declare(strict_types=1);

require 'vendor/autoload.php';

use Skim\Core\App;
use Skim\Core\Request;
use Skim\Core\Response;

$app = App::instance();
$app->router->get('/demo/economy/stream', [App\Controllers\EconomyController::class, 'stream']);

$ds = json_encode(['tax' => 25, 'pop' => 200, 'income' => 60, 'spend' => 40]);
$req = Request::make('GET', '/demo/economy/stream', query: ['datastar' => $ds]);
$res = new Response();

ob_start();
try {
    $app->dispatch($req, $res);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
$output = ob_get_clean();

echo "--- SSE output (first 2KB) ---\n";
echo substr($output, 0, 2048);
echo "\n--- end ---\n";

if (str_contains($output, 'datastar-patch-signals')) {
    echo "PASS: datastar-patch-signals found\n";
    exit(0);
} else {
    echo "FAIL: no datastar-patch-signals in output\n";
    exit(1);
}
