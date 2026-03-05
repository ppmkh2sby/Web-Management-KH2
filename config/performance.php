<?php

return [
    'sql_profile' => [
        'enabled' => (bool) env('SQL_PROFILE_ENABLED', false),
        'sample_rate' => (float) env('SQL_PROFILE_SAMPLE_RATE', 0.25),
        'slow_query_ms' => (float) env('SQL_PROFILE_SLOW_QUERY_MS', 100),
        'top_queries' => (int) env('SQL_PROFILE_TOP_QUERIES', 5),
        'response_headers' => (bool) env('SQL_PROFILE_RESPONSE_HEADERS', false),
        'channel' => env('SQL_PROFILE_LOG_CHANNEL', 'sql_profile'),
    ],
];
