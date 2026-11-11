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

    // Pre-flight check: PostgreSQL extension availability
    if (!function_exists('pg_connect')) {
        http_response_code(503);
        die("<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <title>CampusBallot - Database Driver Required</title>
            <link rel='stylesheet' href='/assets/css/bootstrap.min.css'>
            <style>
                body { background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
                .notice-card { max-width: 580px; width: 100%; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border-top: 4px solid #f59e0b; }
                .badge-warn { background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; display: inline-block; margin-bottom: 1rem; }
                code { background: #f1f5f9; color: #0f172a; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class='notice-card'>
                <span class='badge-warn'>Standalone Preview Mode</span>
                <h3 style='margin-top:0; color:#1e293b; font-weight:700;'>PostgreSQL Driver Not Detected</h3>
                <p style='color:#64748b; line-height:1.6;'>You are previewing <strong>CampusBallot Core</strong> on a local PHP runtime without the <code>pgsql</code> driver or active PostgreSQL database instance.</p>
                <div style='background:#f8fafc; border-left:3px solid #3b82f6; padding:12px 16px; margin:1.5rem 0;'>
                    <strong style='color:#1e40af;'>How to run full interactive ballots:</strong>
                    <p style='margin:6px 0 0; color:#334155; font-size:0.9rem;'>Run with Docker Compose (includes PHP + PostgreSQL + Seeds):<br><code>docker compose up -d</code></p>
                    <p style='margin:6px 0 0; color:#334155; font-size:0.9rem;'>Or uncomment <code>extension=pgsql</code> in <code>php.ini</code> if using local XAMPP.</p>
                </div>
                <a href='/' class='btn btn-primary' style='border-radius:6px; padding:8px 18px; text-decoration:none; display:inline-block; font-weight:600;'>&larr; Return to Portal Home</a>
            </div>
        </body>
        </html>");
    }

    $db_host   = getenv('DB_HOST') ?: '127.0.0.1';
    $db_port   = getenv('DB_PORT') ?: '5432';
    $db_name   = getenv('DB_NAME') ?: 'evoting_db';
    $db_user   = getenv('DB_USER') ?: 'postgres';
    $db_pass   = getenv('DB_PASS') ?: '';
    $db_schema = getenv('DB_SCHEMA') ?: 'evoting, public';

    $conn_str = "host={$db_host} port={$db_port} dbname={$db_name} user={$db_user} password={$db_pass}";
    $conn = @pg_connect($conn_str);

    if (!$conn) {
        http_response_code(503);
        die("<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <title>CampusBallot - Database Offline</title>
            <link rel='stylesheet' href='/assets/css/bootstrap.min.css'>
            <style>
                body { background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
                .notice-card { max-width: 580px; width: 100%; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); border-top: 4px solid #ef4444; }
                .badge-err { background: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; display: inline-block; margin-bottom: 1rem; }
                code { background: #f1f5f9; color: #0f172a; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class='notice-card'>
                <span class='badge-err'>Database Unreachable</span>
                <h3 style='margin-top:0; color:#1e293b; font-weight:700;'>Database Connection Offline</h3>
                <p style='color:#64748b; line-height:1.6;'>The portal is running, but cannot connect to PostgreSQL at <code>{$db_host}:{$db_port}</code>.</p>
                <div style='background:#f8fafc; border-left:3px solid #ef4444; padding:12px 16px; margin:1.5rem 0;'>
                    <strong style='color:#991b1b;'>To start the database:</strong>
                    <p style='margin:6px 0 0; color:#334155; font-size:0.9rem;'>Run: <code>docker compose up -d</code> in the project root.</p>
                </div>
                <a href='/' class='btn btn-primary' style='border-radius:6px; padding:8px 18px; text-decoration:none; display:inline-block; font-weight:600;'>&larr; Return to Portal Home</a>
            </div>
        </body>
        </html>");
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
