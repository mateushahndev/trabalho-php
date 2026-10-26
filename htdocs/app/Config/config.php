<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'Opções B3',
        'debug' => true,
    ],

    'database' => [
        'host'     => 'localhost',
        'port'     => 5432,
        'dbname'   => 'opcoes_b3',
        'user'     => 'opcoes_user',
        'password' => 'quie/b7Aevi0UoXioKoo',
    ],

    'session' => [
        'name' => 'opcoes_b3_session',
    ],

    'pagination' => [
        'markets_per_page'    => 2,
        'contracts_per_page'  => 2,
        'positions_per_page'  => 2,
        'transactions_per_page' => 2,
    ],

    'finance' => [
        'cache_dir'           => dirname(__DIR__, 2) . '/storage/cache',
        'risk_free_fallback'  => 0.1375,
        'vol_fallback'        => 0.35,
        'history_days'        => 365,
        'chart_days'          => 365,
        'probe_timeout_ms'    => 30000,
        'probe_hosts'         => ['https://query1.finance.yahoo.com/', 'http://mba.tuck.dartmouth.edu/'],
        'fred_api_key'        => null,
        'benchmark_b3'        => 'IBOV',
    ],
];
