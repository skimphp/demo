<?php declare(strict_types=1);

use App\Helpers\Economy;

describe('Economy::compute()', function(): void {

    test('returns expected defaults', function(): void {
        $r = Economy::compute(Economy::$defaults);
        expect($r['rev'])->toBe(972);
        expect($r['cost'])->toBe(964);
        expect($r['bal'])->toBe(8);
        expect($r['debt'])->toBe(0);
        expect($r['happy'])->toBe(57);
        expect($r['energy'])->toBe(95);
        expect($r['pollut'])->toBe(12);
        expect($r['growth'])->toBe(-0.1);
    });

    test('clamps happy and energy at boundaries', function(): void {
        $r = Economy::compute(['tax' => 5, 'pop' => 500, 'income' => 120, 'spend' => 10]);
        expect($r['happy'])->toBeGreaterThanOrEqual(10);
        expect($r['happy'])->toBeLessThanOrEqual(100);
        expect($r['energy'])->toBeGreaterThanOrEqual(20);
        expect($r['energy'])->toBeLessThanOrEqual(100);
        expect($r['pollut'])->toBeGreaterThanOrEqual(0);
        expect($r['pollut'])->toBeLessThanOrEqual(100);
    });

    test('handles max sliders', function(): void {
        $r = Economy::compute(['tax' => 40, 'pop' => 500, 'income' => 120, 'spend' => 60]);
        expect($r['rev'])->toBe(24000);
        expect($r['cost'])->toBe(44640);
        expect($r['bal'])->toBe(-20640);
        expect($r['debt'])->toBeGreaterThan(0);
    });

});

describe('Economy::status()', function(): void {

    test('bal ok when positive', function(): void {
        expect(Economy::status('bal', 10))->toBe('ok');
    });

    test('bal warn when slightly negative', function(): void {
        expect(Economy::status('bal', -10))->toBe('warn');
    });

    test('bal bad when very negative', function(): void {
        expect(Economy::status('bal', -60))->toBe('bad');
    });

    test('happy thresholds', function(): void {
        expect(Economy::status('happy', 70))->toBe('ok');
        expect(Economy::status('happy', 50))->toBe('warn');
        expect(Economy::status('happy', 30))->toBe('bad');
    });

});

describe('Economy::trend()', function(): void {

    test('bal trends up when positive', function(): void {
        expect(Economy::trend('bal', 5))->toBe('▲ up');
    });

    test('bal trends down when negative', function(): void {
        expect(Economy::trend('bal', -5))->toBe('▼ down');
    });

    test('rev strong above 60', function(): void {
        expect(Economy::trend('rev', 80))->toBe('▲ strong');
    });

});

describe('Economy::format()', function(): void {

    test('formats money values', function(): void {
        expect(Economy::format('rev', 972))->toBe('$972M');
        expect(Economy::format('cost', 960))->toBe('$960M');
    });

    test('formats balance with sign', function(): void {
        expect(Economy::format('bal', 12))->toBe('+$12M');
        expect(Economy::format('bal', -5))->toBe('-$5M');
    });

    test('formats percentages', function(): void {
        expect(Economy::format('debt', 15))->toBe('+15%');
        expect(Economy::format('growth', -2))->toBe('-2%');
    });

});
