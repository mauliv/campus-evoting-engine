<?php
/**
 * Database Connection & Environment Configuration
 * CampusBallot Core - High-Concurrency E-Voting Engine
 * 
 * Safely reads credentials and schema names from Environment Variables.
 * Zero hardcoded credentials in source control.
 */

if (!defined('CAMPUSBALLOT_DB_INITIALIZED')) {
    define('CAMPUSBALLOT_DB_INITIALIZED', true);

    ini_set("error_reporting", E_ALL & ~E_NOTICE);
    ini_set("display_errors", 0);

    $db_host   = getenv('DB_HOST') ?: '127.0.0.1';
    $db_port   = getenv('DB_PORT') ?: '5432';
    $db_name   = getenv('DB_NAME') ?: 'evoting_db';
    $db_user   = getenv('DB_USER') ?: 'postgres';
    $db_pass   = getenv('DB_PASS') ?: '';
    $db_schema = getenv('DB_SCHEMA') ?: 'evoting, public';

    $conn_str = "host={$db_host} port={$db_port} dbname={$db_name} user={$db_user} password={$db_pass}";
    $conn = @pg_connect($conn_str);

    if (!$conn) {
        error_log("Database connection failure on host: " . $db_host);
        die("System is under heavy concurrent load. Please refresh your browser in a few moments.");
    }

    // Ensure active PostgreSQL search path is set to the application schema
    $set_schema = "SET search_path TO " . $db_schema;
    @pg_query($conn, $set_schema);

    // Network station identification & physical kiosk whitelist verification
    $ip_stat = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $cek_ip_sql = "SELECT lokasi FROM whitelist WHERE ip = $1 AND keterangan = 'BILIK'";
    $cek_ip_result = @pg_query_params($conn, $cek_ip_sql, array($ip_stat));
    $cek_ip = $cek_ip_result ? @pg_fetch_assoc($cek_ip_result) : null;
}
