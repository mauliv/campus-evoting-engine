<?php
/**
 * CampusBallot Core - Administrative Voter Roll Search
 * Supervised voter registry lookup utility.
 */
ini_set("error_reporting", E_ALL & ~E_NOTICE);
ini_set("display_errors", 0);

require_once __DIR__ . '/../db_connect.php';

$nim   = isset($_POST['nim']) ? trim($_POST['nim']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if ($nim !== '') {
    $cari_sql = "SELECT nim, nama, status, ip, waktu FROM mahasiswa WHERE nim = $1";
    $cari_result = pg_query_params($conn, $cari_sql, array($nim));
    $hasil_cari = $cari_result ? pg_fetch_assoc($cari_result) : null;

    if ($hasil_cari) {
        echo "<strong>Student ID (NIM):</strong> " . htmlspecialchars($hasil_cari['nim']) . "<br>";
        echo "<strong>Name:</strong> " . htmlspecialchars($hasil_cari['nama']) . "<br>";
        echo "<strong>Ballot Status:</strong> " . ($hasil_cari['status'] !== null ? "Voted (Locked)" : "Eligible (Not Voted)") . "<br>";
        echo "<strong>Station IP:</strong> " . htmlspecialchars($hasil_cari['ip'] ?? 'N/A') . "<br>";
        echo "<strong>Timestamp:</strong> " . htmlspecialchars($hasil_cari['waktu'] ?? 'N/A') . "<br>";
    } else {
        echo "<p>No voter found matching Student ID: " . htmlspecialchars($nim) . "</p>";
    }
}

if ($email !== '') {
    $cari_sql = "SELECT nim, nama, status, ip, waktu FROM mahasiswa WHERE email = $1";
    $cari_result = pg_query_params($conn, $cari_sql, array($email));
    $hasil_cari = $cari_result ? pg_fetch_assoc($cari_result) : null;

    if ($hasil_cari) {
        echo "<strong>Student ID (NIM):</strong> " . htmlspecialchars($hasil_cari['nim']) . "<br>";
        echo "<strong>Name:</strong> " . htmlspecialchars($hasil_cari['nama']) . "<br>";
        echo "<strong>Ballot Status:</strong> " . ($hasil_cari['status'] !== null ? "Voted (Locked)" : "Eligible (Not Voted)") . "<br>";
        echo "<strong>Station IP:</strong> " . htmlspecialchars($hasil_cari['ip'] ?? 'N/A') . "<br>";
        echo "<strong>Timestamp:</strong> " . htmlspecialchars($hasil_cari['waktu'] ?? 'N/A') . "<br>";
    } else {
        echo "<p>No voter found matching SSO Email: " . htmlspecialchars($email) . "</p>";
    }
}
?>