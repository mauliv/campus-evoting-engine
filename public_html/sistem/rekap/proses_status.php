<?php
require_once __DIR__ . '/../make_conn.php';
ini_set("error_reporting", E_ALL & ~E_NOTICE);
ini_set("display_errors", 0);

$kode_fak   = trim((string)($_POST['fakultas'] ?? ''));
$pass01     = trim((string)($_POST['pass01'] ?? ''));
$pass02     = trim((string)($_POST['pass02'] ?? ''));

// carry return params
$return_kode_fak   = trim((string)($_POST['kode_fak'] ?? $kode_fak));
$return_nomor_soal = trim((string)($_POST['nomor_soal'] ?? '1'));

// validate basic
if ($kode_fak === '' || !preg_match('/^\d{1,2}$/', $kode_fak)) {
    die("Fakultas tidak valid.");
}

$cek_pass_sql = "
    SELECT status
    FROM status_rekap
    WHERE id_fak = $1 AND password1 = $2 AND password2 = $3
    LIMIT 1
";
$res = pg_query_params($conn, $cek_pass_sql, array($kode_fak, $pass01, $pass02));
$row = $res ? pg_fetch_assoc($res) : null;

if (!$row) {
    // wrong password; redirect back with hint (optional)
    header("Location: set_status.php?kode_fak=" . urlencode($return_kode_fak) . "&nomor_soal=" . urlencode($return_nomor_soal));
    exit;
}

if ($row['status'] === '0') {
    $set_sql = "
        UPDATE status_rekap
        SET status = '1'
        WHERE id_fak = $1 AND password1 = $2 AND password2 = $3
    ";
    pg_query_params($conn, $set_sql, array($kode_fak, $pass01, $pass02));
}

// go back to the rekap that user intended to open
header("Location: rekap.php?kode_fak=" . urlencode($return_kode_fak) . "&nomor_soal=" . urlencode($return_nomor_soal));
exit;
