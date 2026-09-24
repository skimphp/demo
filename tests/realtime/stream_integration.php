<?php declare(strict_types=1);

require 'vendor/autoload.php';

use Skim\Core\Response;
use Skim\Realtime\Datastar;
use Skim\Realtime\Contract\SignalPatcher;

$pass = 0;
$fail = 0;

function check(bool $cond, string $msg): void {
    global $pass, $fail;
    if ($cond) { $pass++; echo "  PASS  {$msg}\n"; }
    else { $fail++; echo "  FAIL  {$msg}\n"; }
}

$received = null;
$res = new Response();
@$res->stream(function (signal_patcher $ui) use (&$received) {
    $received = $ui;
});

echo "stream() integration\n";
check($received instanceof datastar, 'received datastar driver');
check($received instanceof signal_patcher, 'received signal_patcher');

echo "\n{$pass} passed, {$fail} failed\n";
exit($fail > 0 ? 1 : 0);
