<?php declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Economy;
use Skim\Core\Request;
use Skim\Core\Response;
use Skim\Realtime\Contract\SignalPatcher;

class EconomyController {
    public function page(Request $req, Response $res): mixed {
        return $res->view('demo/economy', ['title' => 'City Economy — SKIM SSE Demo']);
    }

    public function worker(Request $req, Response $res): mixed {
        $mem_before = memory_get_usage(true);
        $start = microtime(true);

        \Skim\Worker\WorkerReset::apply();

        $mem_after = memory_get_usage(true);
        $reset_ms = round((microtime(true) - $start) * 1000, 3);

        return $res->view('demo/economy', [
            'title' => 'City Economy — Worker Mode',
            'worker_mode' => true,
            'metrics' => [
                'mem_before' => $mem_before,
                'mem_after' => $mem_after,
                'mem_delta' => $mem_after - $mem_before,
                'reset_ms' => $reset_ms,
                'leak_active' => \Skim\Worker\LeakDetector::isActive(),
                'resettable_count' => count(\Skim\Worker\WorkerReset::discovered()),
            ]
        ]);
    }

    public function stream(Request $req, Response $res): mixed {
        $ds = $req->input('datastar');
        $signals = $ds !== null ? json_decode((string) $ds, true) ?? [] : [];

        $inputs = [
            'tax'    => max(5,  min(40,  (float) ($signals['tax']    ?? Economy::$defaults['tax']))),
            'pop'    => max(50, min(500, (float) ($signals['pop']    ?? Economy::$defaults['pop']))),
            'income' => max(20, min(120, (float) ($signals['income'] ?? Economy::$defaults['income']))),
            'spend'  => max(10, min(60,  (float) ($signals['spend']  ?? Economy::$defaults['spend']))),
        ];

        \Skim\Dev\Profiler::disable();

        return $res->stream(function (signal_patcher $ui) use ($inputs) {
            $start_ns = hrtime(true);
            $results = Economy::compute($inputs);

            foreach (Economy::$wave_order as $wave => $ids) {
                foreach ($ids as $id) {
                    $val    = $results[$id];
                    $status = Economy::status($id, (float) $val);
                    $trend  = Economy::trend($id, (float) $val);
                    $fmt    = Economy::format($id, (float) $val);

                    $ui->signals([
                        'cells'  => [$id => $fmt],
                        'status' => [$id => $status],
                        'trend'  => [$id => $trend],
                    ]);
                }
            }

            $latency_ns = hrtime(true) - $start_ns;
            $ui->signals([
                'server_latency_ns' => $latency_ns,
                'server_latency_ms' => round($latency_ns / 1_000_000, 3),
            ]);
        });
    }
}
