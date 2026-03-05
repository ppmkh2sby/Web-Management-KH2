<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProfileSqlPerEndpoint
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isEnabled() || ! $this->passesSampling()) {
            return $next($request);
        }

        $requestStart = microtime(true);
        $slowThresholdMs = $this->slowThresholdMs();
        $topLimit = $this->topQueriesLimit();

        $queryCount = 0;
        $slowQueryCount = 0;
        $sqlTotalMs = 0.0;
        $topQueries = [];

        DB::listen(function (QueryExecuted $query) use (&$queryCount, &$slowQueryCount, &$sqlTotalMs, &$topQueries, $slowThresholdMs, $topLimit): void {
            $queryCount++;

            $timeMs = (float) $query->time;
            $sqlTotalMs += $timeMs;

            if ($timeMs >= $slowThresholdMs) {
                $slowQueryCount++;
            }

            $this->trackTopQuery($topQueries, [
                'time_ms' => round($timeMs, 2),
                'sql' => $this->normalizeSql($query->sql),
                'connection' => $query->connectionName,
            ], $topLimit);
        });

        /** @var Response $response */
        $response = $next($request);
        $requestMs = (microtime(true) - $requestStart) * 1000;

        $route = $request->route();
        $channel = (string) config('performance.sql_profile.channel', 'sql_profile');

        Log::channel($channel)->info('sql_profile', [
            'method' => $request->getMethod(),
            'path' => '/'.ltrim($request->path(), '/'),
            'route_name' => $route?->getName(),
            'route_action' => $route?->getActionName(),
            'status_code' => $response->getStatusCode(),
            'request_ms' => round($requestMs, 2),
            'sql_total_ms' => round($sqlTotalMs, 2),
            'sql_time_ratio_pct' => $requestMs > 0 ? round(($sqlTotalMs / $requestMs) * 100, 2) : 0.0,
            'query_count' => $queryCount,
            'slow_query_count' => $slowQueryCount,
            'top_queries' => $topQueries,
        ]);

        if ((bool) config('performance.sql_profile.response_headers', false)) {
            $response->headers->set('X-Profile-Request-Ms', (string) round($requestMs, 2));
            $response->headers->set('X-Profile-Sql-Ms', (string) round($sqlTotalMs, 2));
            $response->headers->set('X-Profile-Query-Count', (string) $queryCount);
        }

        return $response;
    }

    private function isEnabled(): bool
    {
        return (bool) config('performance.sql_profile.enabled', false);
    }

    private function passesSampling(): bool
    {
        $sampleRate = (float) config('performance.sql_profile.sample_rate', 1.0);
        $sampleRate = max(0.0, min(1.0, $sampleRate));

        if ($sampleRate >= 1.0) {
            return true;
        }

        if ($sampleRate <= 0.0) {
            return false;
        }

        $threshold = (int) round($sampleRate * 10000);

        return random_int(1, 10000) <= $threshold;
    }

    private function slowThresholdMs(): float
    {
        return (float) config('performance.sql_profile.slow_query_ms', 100.0);
    }

    private function topQueriesLimit(): int
    {
        return max(1, (int) config('performance.sql_profile.top_queries', 5));
    }

    /**
     * @param  array<int, array{time_ms: float, sql: string, connection: string}>  $topQueries
     * @param  array{time_ms: float, sql: string, connection: string}  $entry
     */
    private function trackTopQuery(array &$topQueries, array $entry, int $limit): void
    {
        $topQueries[] = $entry;

        usort($topQueries, static fn (array $a, array $b): int => $b['time_ms'] <=> $a['time_ms']);

        if (count($topQueries) > $limit) {
            array_pop($topQueries);
        }
    }

    private function normalizeSql(string $sql): string
    {
        return preg_replace('/\s+/', ' ', trim($sql)) ?? trim($sql);
    }
}
