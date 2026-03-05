<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('perf:sql-summary {--file=} {--limit=1000} {--top=15}', function () {
    $defaultFile = collect(glob(storage_path('logs/sql-profile*.log')) ?: [])
        ->sortDesc()
        ->first();
    $filePath = $this->option('file') ?: ($defaultFile ?: storage_path('logs/sql-profile.log'));
    $limit = max(1, (int) $this->option('limit'));
    $top = max(1, (int) $this->option('top'));

    if (! is_file($filePath)) {
        $this->error("File log tidak ditemukan: {$filePath}");
        return self::FAILURE;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (! is_array($lines) || empty($lines)) {
        $this->warn('Log profiling masih kosong.');
        return self::SUCCESS;
    }

    $lines = array_slice($lines, -1 * $limit);
    $stats = [];
    $parsedRows = 0;

    foreach ($lines as $line) {
        if (! str_contains($line, 'sql_profile')) {
            continue;
        }

        $jsonStart = strpos($line, '{');
        $jsonEnd = strrpos($line, '}');
        if ($jsonStart === false || $jsonEnd === false || $jsonEnd <= $jsonStart) {
            continue;
        }

        $payload = json_decode(substr($line, $jsonStart, ($jsonEnd - $jsonStart + 1)), true);
        if (! is_array($payload)) {
            continue;
        }

        $method = (string) ($payload['method'] ?? 'GET');
        $routeName = (string) ($payload['route_name'] ?? '');
        $path = (string) ($payload['path'] ?? '/');
        $endpointKey = $method.' '.($routeName !== '' ? $routeName : $path);

        if (! array_key_exists($endpointKey, $stats)) {
            $stats[$endpointKey] = [
                'endpoint' => $endpointKey,
                'samples' => 0,
                'avg_req_ms' => 0.0,
                'avg_sql_ms' => 0.0,
                'max_req_ms' => 0.0,
                'max_sql_ms' => 0.0,
                'avg_queries' => 0.0,
                'avg_slow_queries' => 0.0,
            ];
        }

        $entry = &$stats[$endpointKey];
        $entry['samples']++;
        $entry['avg_req_ms'] += (float) ($payload['request_ms'] ?? 0);
        $entry['avg_sql_ms'] += (float) ($payload['sql_total_ms'] ?? 0);
        $entry['avg_queries'] += (float) ($payload['query_count'] ?? 0);
        $entry['avg_slow_queries'] += (float) ($payload['slow_query_count'] ?? 0);
        $entry['max_req_ms'] = max($entry['max_req_ms'], (float) ($payload['request_ms'] ?? 0));
        $entry['max_sql_ms'] = max($entry['max_sql_ms'], (float) ($payload['sql_total_ms'] ?? 0));

        $parsedRows++;
    }

    if ($parsedRows === 0) {
        $this->warn('Tidak ada baris profiling yang bisa diparse.');
        return self::SUCCESS;
    }

    foreach ($stats as &$entry) {
        $samples = max(1, (int) $entry['samples']);
        $entry['avg_req_ms'] = round($entry['avg_req_ms'] / $samples, 2);
        $entry['avg_sql_ms'] = round($entry['avg_sql_ms'] / $samples, 2);
        $entry['avg_queries'] = round($entry['avg_queries'] / $samples, 2);
        $entry['avg_slow_queries'] = round($entry['avg_slow_queries'] / $samples, 2);
        $entry['max_req_ms'] = round($entry['max_req_ms'], 2);
        $entry['max_sql_ms'] = round($entry['max_sql_ms'], 2);
    }
    unset($entry);

    usort($stats, static fn (array $a, array $b): int => $b['avg_sql_ms'] <=> $a['avg_sql_ms']);

    $tableRows = array_slice(array_map(static function (array $entry): array {
        return [
            $entry['endpoint'],
            (string) $entry['samples'],
            (string) $entry['avg_req_ms'],
            (string) $entry['avg_sql_ms'],
            (string) $entry['max_req_ms'],
            (string) $entry['max_sql_ms'],
            (string) $entry['avg_queries'],
            (string) $entry['avg_slow_queries'],
        ];
    }, $stats), 0, $top);

    $this->table(
        ['Endpoint', 'Samples', 'AvgReq(ms)', 'AvgSQL(ms)', 'MaxReq(ms)', 'MaxSQL(ms)', 'AvgQ', 'AvgSlowQ'],
        $tableRows
    );

    $this->info("Parsed {$parsedRows} samples from {$filePath}");

    return self::SUCCESS;
})->purpose('Ringkas profil SQL per endpoint dari log sampling');
