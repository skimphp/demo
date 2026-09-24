<?php declare(strict_types=1);

namespace App\Helpers;

/**
 * Pure computation engine for the City Economy Simulator demo.
 * Zero I/O, no mutable static state.
 */
class Economy {
    public static array $defaults = [
        'tax'    => 18,
        'pop'    => 120,
        'income' => 45,
        'spend'  => 32,
    ];

    public static array $wave_order = [
        1 => ['rev'],
        2 => ['cost'],
        3 => ['bal', 'debt'],
        4 => ['happy', 'energy'],
        5 => ['pollut', 'growth'],
    ];

    /**
     * Compute all 8 derived economy metrics from 4 inputs.
     *
     * @param array $inputs Keys: tax, pop, income, spend.
     * @return array Keys: rev, cost, bal, debt, happy, energy, pollut, growth.
     */
    public static function compute(array $inputs): array {
        $tax    = (float) ($inputs['tax']    ?? self::$defaults['tax']);
        $pop    = (float) ($inputs['pop']    ?? self::$defaults['pop']);
        $income = (float) ($inputs['income'] ?? self::$defaults['income']);
        $spend  = (float) ($inputs['spend']  ?? self::$defaults['spend']);

        $rev    = (int) round($tax * $pop * $income / 100);
        $cost   = (int) round($spend * $rev / 100 * 3.1);
        $bal    = $rev - $cost;
        $debt   = (int) max(0, round(-$bal / max($rev, 1) * 100));
        $happy  = (int) max(10, min(100, round(40 + $spend * 0.8 - $tax * 0.6 + $pop * 0.02)));
        $energy = (int) max(20, min(100, round(95 - $pop * 0.08 + $spend * 0.3)));
        $pollut = (int) max(0, min(100, round($pop * 0.15 - $spend * 0.5 + 10)));
        $growth = (float) round($bal / max($rev, 1) * 8 - $tax * 0.1 + $spend * 0.05, 1);

        return [
            'rev'    => $rev,
            'cost'   => $cost,
            'bal'    => $bal,
            'debt'   => $debt,
            'happy'  => $happy,
            'energy' => $energy,
            'pollut' => $pollut,
            'growth' => $growth,
        ];
    }

    /**
     * Status for a given metric: ok | warn | bad.
     */
    public static function status(string $id, float $v): string {
        if ($id === 'bal')    return $v > 0 ? 'ok' : ($v > -50 ? 'warn' : 'bad');
        if ($id === 'happy')  return $v > 65 ? 'ok' : ($v > 40 ? 'warn' : 'bad');
        if ($id === 'energy') return $v > 70 ? 'ok' : ($v > 45 ? 'warn' : 'bad');
        if ($id === 'pollut') return $v < 35 ? 'ok' : ($v < 65 ? 'warn' : 'bad');
        if ($id === 'debt')   return $v < 25 ? 'ok' : ($v < 55 ? 'warn' : 'bad');
        if ($id === 'growth') return $v > 1 ? 'ok' : ($v > 0 ? 'warn' : 'bad');
        return 'ok';
    }

    /**
     * Human-readable status label.
     */
    public static function statusLabel(string $s): string {
        if ($s === 'ok')   return '✓ healthy';
        if ($s === 'warn') return '⚠ watch';
        return '✗ critical';
    }

    /**
     * Trend arrow + label for a given metric.
     */
    public static function trend(string $id, float $v): string {
        if ($id === 'bal' || $id === 'growth') return $v > 0 ? '▲ up' : '▼ down';
        if (in_array($id, ['rev', 'happy', 'energy'], true)) return $v > 60 ? '▲ strong' : '▼ low';
        if (in_array($id, ['pollut', 'debt', 'cost'], true)) return $v < 30 ? '▼ low' : '▲ high';
        return '— stable';
    }

    /**
     * Display string for a cell value.
     */
    public static function format(string $id, float $v): string {
        if ($id === 'rev' || $id === 'cost') return '$' . (int) $v . 'M';
        if ($id === 'bal') return ($v >= 0 ? '+$' : '-$') . abs((int) $v) . 'M';
        if ($id === 'debt' || $id === 'growth') return ($v >= 0 ? '+' : '') . $v . '%';
        return (string) (int) $v;
    }
}
