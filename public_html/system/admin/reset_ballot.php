<?php
/**
 * CampusBallot Core - Supervised Emergency Ballot Reset
 * Resets a stuck or spoiled voter session under supervisory oversight.
 */
ini_set("error_reporting", E_ALL & ~E_NOTICE);
ini_set("display_errors", 0);

require_once __DIR__ . '/../db_connect.php';

$nim = isset($_POST['nim']) ? trim($_POST['nim']) : '';

if ($nim !== '') {
    // 1. Fetch voter token safely
    $cari_mhs = "SELECT nim, nama, token FROM mahasiswa WHERE nim = $1";
    $cari_mhs_result = pg_query_params($conn, $cari_mhs, array($nim));
    $data_mhs = $cari_mhs_result ? pg_fetch_assoc($cari_mhs_result) : null;

    if ($data_mhs) {
        $token = $data_mhs['token'];

        if (!empty($token)) {
            // Delete previously staged answers for this token
            $hapus_sql = "DELETE FROM peserta_jawaban WHERE id_peserta = $1";
            pg_query_params($conn, $hapus_sql, array($token));
        }

        // Reset voter status to eligible
        $reset_sql = "UPDATE mahasiswa  
                      SET status = NULL, 
                          ip = NULL,
                          token = NULL,
                          waktu = NULL 
                      WHERE nim = $1";
        pg_query_params($conn, $reset_sql, array($nim));

        echo "<h3>Voter Record Reset Successfully</h3>";
        echo "<p>Student ID <strong>" . htmlspecialchars($nim) . "</strong> (" . htmlspecialchars($data_mhs['nama']) . ") has been reset to eligible.</p>";
        echo "<a href='reset_form.html' class='btn btn-primary'>Return to Reset Form</a>";
    } else {
        echo "<p>No voter found with Student ID: " . htmlspecialchars($nim) . "</p>";
        echo "<a href='reset_form.html'>Return</a>";
    }
} else {
    header("Location: reset_form.html");
    exit;
}
?>


