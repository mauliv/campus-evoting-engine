<?php
/**
 * System Healthcheck & Database Telemetry Endpoint
 * CampusBallot Core - Collegiate E-Voting Platform
 * 
 * Provides structured JSON readiness and liveness telemetry for load balancers.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$start_time = microtime(true);
$response = array(
    'status'    => 'healthy',
    'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
    'runtime'   => array(
        'php_version' => PHP_VERSION,
        'sapi'        => PHP_SAPI,
    ),
    'services'  => array(
        'database' => array(
            'connected'  => false,
            'latency_ms' => null,
            'version'    => null,
        )
    )
);

try {
    require_once __DIR__ . '/make_conn.php';

    if (isset($conn) && $conn) {
        $query_start = microtime(true);
        $res = @pg_query($conn, "SELECT version()");
        $query_end = microtime(true);

        if ($res) {
            $row = pg_fetch_row($res);
            $response['services']['database']['connected']  = true;
            $response['services']['database']['latency_ms'] = round(($query_end - $query_start) * 1000, 2);
            $response['services']['database']['version']    = $row[0] ?? 'PostgreSQL';
        } else {
            $response['status'] = 'degraded';
            $response['services']['database']['error'] = 'Query test failed';
            http_response_code(503);
        }
    } else {
        $response['status'] = 'unhealthy';
        $response['services']['database']['error'] = 'Database connection not initialized';
        http_response_code(503);
    }
} catch (\Throwable $e) {
    $response['status'] = 'unhealthy';
    $response['services']['database']['error'] = $e->getMessage();
    http_response_code(500);
}

$response['total_duration_ms'] = round((microtime(true) - $start_time) * 1000, 2);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
