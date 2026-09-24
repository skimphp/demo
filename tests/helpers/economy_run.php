<?php declare(strict_types=1);

require 'vendor/autoload.php';

use App\Helpers\Economy;

$pass = 0;
$fail = 0;

function check(bool $cond, string $msg): void {
    global $pass, $fail;
    if ($cond) { $pass++; echo "  PASS  {$msg}\n"; }
    else { $fail++; echo "  FAIL  {$msg}\n"; }
}

$defaults = Economy::compute(Economy::$defaults);

echo "Economy::compute() defaults\n";
check($defaults['rev'] === 972, 'rev = 972');
check($defaults['cost'] === 964, 'cost = 964');
check($defaults['bal'] === 8, 'bal = 8');
check($defaults['debt'] === 0, 'debt = 0');
check($defaults['happy'] === 57, 'happy = 57');
check($defaults['energy'] === 95, 'energy = 95');
check($defaults['pollut'] === 12, 'pollut = 12');
check($defaults['growth'] === -0.1, 'growth = -0.1');

echo "Economy::status()\n";
check(Economy::status('bal', 10) === 'ok', 'bal ok');
check(Economy::status('bal', -10) === 'warn', 'bal warn');
check(Economy::status('bal', -60) === 'bad', 'bal bad');
check(Economy::status('happy', 70) === 'ok', 'happy ok');
check(Economy::status('happy', 50) === 'warn', 'happy warn');
check(Economy::status('happy', 30) === 'bad', 'happy bad');

echo "Economy::trend()\n";
check(Economy::trend('bal', 5) === '▲ up', 'bal up');
check(Economy::trend('bal', -5) === '▼ down', 'bal down');
check(Economy::trend('rev', 80) === '▲ strong', 'rev strong');

echo "Economy::format()\n";
check(Economy::format('rev', 972) === '$972M', 'rev format');
check(Economy::format('bal', 12) === '+$12M', 'bal positive');
check(Economy::format('bal', -5) === '-$5M', 'bal negative');
check(Economy::format('debt', 15) === '+15%', 'debt format');
check(Economy::format('growth', -2) === '-2%', 'growth format');

echo "\n{$pass} passed, {$fail} failed\n";
exit($fail > 0 ? 1 : 0);
