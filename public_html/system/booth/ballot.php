<?php


ini_set("error_reporting", E_ALL & ~E_NOTICE);
ini_set("display_errors", 0);

require_once __DIR__ . '/../db_connect.php';

function faculty_code_from_prodi($prodi_code) {
    $prodi_code = trim((string)$prodi_code);

    // kalau memang faculty code (4 digit) langsung tangkap
    if ($prodi_code === '1001') return '10'; // FK
    if ($prodi_code === '1101') return '11'; // FP
    if ($prodi_code === '1201') return '12'; // FH

    // kalau ada kode prodi 4+ digit yang memang dimulai 10/11/12 (bukan 3 digit seperti 110)
    if (strlen($prodi_code) >= 4) {
        if (strpos($prodi_code, '10') === 0) return '10';
        if (strpos($prodi_code, '11') === 0) return '11';
        if (strpos($prodi_code, '12') === 0) return '12';
    }

    // sisanya: fakultas = digit pertama (FIP=1, FBSB=2, dst)
    return substr($prodi_code, 0, 1);
}

function faculty_idsoal_normalized($faculty_code) {
    // In DB, FK/FP/FH are stored as 1001/1101/1201 (NOT 10/11/12)
    if ($faculty_code === '10') return '1001'; // FK
    if ($faculty_code === '11') return '1101'; // FP
    if ($faculty_code === '12') return '1201'; // FH
    return $faculty_code; // 1,2,3,4,5,6,8,9 stay same
}


function pertanyaan_exists($conn, $id_soal, $nomor_soal) {
    $sql = "SELECT 1 FROM pertanyaan WHERE id_soal = $1 AND nomor_soal = $2 LIMIT 1";
    $res = pg_query_params($conn, $sql, array($id_soal, $nomor_soal));
    return ($res && pg_num_rows($res) > 0);
}

function has_candidates($conn, $id_soal, $nomor_soal) {
    // candidates can exist in opsi_jawaban OR media
    $sql1 = "SELECT 1 FROM opsi_jawaban WHERE id_soal = $1 AND nomor_soal = $2 LIMIT 1";
    $r1 = pg_query_params($conn, $sql1, array($id_soal, (string)$nomor_soal));
    if ($r1 && pg_num_rows($r1) > 0) return true;

    $sql2 = "SELECT 1 FROM media WHERE id_soal = $1 AND nomor_soal = $2 LIMIT 1";
    $r2 = pg_query_params($conn, $sql2, array($id_soal, (string)$nomor_soal));
    return ($r2 && pg_num_rows($r2) > 0);
}

$sid = isset($_POST['sid']) ? $_POST['sid'] : $_GET['sid'];
$sid = trim($sid);

if ($sid == '') {
    die("Akses tidak valid. SID tidak ditemukan.");
}

$log_sql = "SELECT nomor_peserta, foto, nama FROM t_log WHERE id_session = $1";
$log_res = pg_query_params($conn, $log_sql, array($sid));
if (!$log_res || pg_num_rows($log_res) == 0) {
    die("Sesi tidak ditemukan. Silakan hubungi panitia pemilihan.");
}

$log_row       = pg_fetch_assoc($log_res);
$nomor_peserta = $log_row['nomor_peserta'];
$id_jurusan    = $log_row['foto'];
$nama_peserta  = $log_row['nama'];

$nim_mhs       = null;
$status_mhs    = null;

$mahasiswa_sql = "SELECT nim, status, id_jurusan
                  FROM mahasiswa
                  WHERE token = $1
                  LIMIT 1";
$mahasiswa_res = pg_query_params($conn, $mahasiswa_sql, array($nomor_peserta));


if ($mahasiswa_res && pg_num_rows($mahasiswa_res) > 0) {
    $mrow        = pg_fetch_assoc($mahasiswa_res);
    $nim_mhs     = $mrow['nim'];
    $status_mhs  = $mrow['status'];

    if (!empty($mrow['id_jurusan'])) {
        $id_jurusan = trim((string)$mrow['id_jurusan']);
    }
}

// --- sanitize/validate id_jurusan (must be numeric 1–4 digits) ---
$id_jurusan = trim((string)$id_jurusan);

if (!preg_match('/^\d{1,4}$/', $id_jurusan)) {
    // invalid format → block to avoid wrong mapping
    die("Akses tidak valid. id_jurusan tidak valid.");
}

function get_id_soal_for($nomor_urut, $prodi_code, $conn)
{
    $prodi_code   = trim((string)$prodi_code);

    $faculty_code_raw = faculty_code_from_prodi($prodi_code);          // "10" / "11" / "12" or "6" etc
    $faculty_idsoal    = faculty_idsoal_normalized($faculty_code_raw); // "1001/1101/1201" or "6" etc

    // 1) DPM Universitas (faculty-wide)
    if ($nomor_urut == 1) return $faculty_idsoal;

    // 2) BEM Universitas (everyone votes)
    if ($nomor_urut == 2) return '0';

if ($nomor_urut == 3) {
    // DPM Fakultas:
    // if dapil has candidates → use dapil
    if (has_candidates($conn, $prodi_code, 3)) {
        return $prodi_code;
    }
    // else fallback faculty
    return $faculty_idsoal;
}

    // 4) BEM Fakultas (faculty-wide) — must use normalized
    if ($nomor_urut == 4) return $faculty_idsoal;

    // 5) HIMA (prodi-wide)
    if ($nomor_urut == 5) return $prodi_code;

    return null;
}

$cek_user_sql = "SELECT test FROM t_user WHERE username = $1";
$cek_user_res = pg_query_params($conn, $cek_user_sql, array($nomor_peserta));
if ($cek_user_res && pg_num_rows($cek_user_res) > 0) {
    $u = pg_fetch_assoc($cek_user_res);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_vote'])) {

    $jawaban = $_POST['jawaban'] ?? [];

    $ada_pilihan = false;
    foreach ($jawaban as $v) {
        if ($v !== '' && $v !== null) {
            $ada_pilihan = true;
            break;
        }
    }

    $del_sql = "DELETE FROM peserta_jawaban WHERE id_peserta = $1";
    pg_query_params($conn, $del_sql, array($nomor_peserta));

    $now     = date('Y-m-d H:i:s');
    $ip_stat = $_SERVER['REMOTE_ADDR'];

    if ($ada_pilihan) {
        for ($nomor_urut = 1; $nomor_urut <= 5; $nomor_urut++) {

            if (!isset($jawaban[$nomor_urut]) || $jawaban[$nomor_urut] === '') {
                continue;
            }

            $opsi    = $jawaban[$nomor_urut];
            $id_soal = get_id_soal_for($nomor_urut, $id_jurusan, $conn);
            if ($id_soal === null || $id_soal === '') continue;

            $jawaban_db = $opsi;

            $map_sql = "SELECT jawaban FROM media
                        WHERE id_soal = $1 AND nomor_soal = $2 AND opsi = $3
                        LIMIT 1";
            $map_res = pg_query_params($conn, $map_sql, array($id_soal, (string)$nomor_urut, $opsi));
            if ($map_res && pg_num_rows($map_res) > 0) {
                $map_row = pg_fetch_assoc($map_res);
                if (trim($map_row['jawaban']) !== '') {
                    $jawaban_db = $map_row['jawaban'];
                }
            }

            $insert_sql = "INSERT INTO peserta_jawaban
                           (id_peserta, nomor_soal, waktu, id_soal, jawaban, nomor_urutan)
                           VALUES ($1, $2, $3, $4, $5, $6)";
            pg_query_params($conn, $insert_sql, array(
                $nomor_peserta,
                (string)$nomor_urut,
                $now,
                $id_soal,
                $jawaban_db,
                $nomor_urut
            ));
        }
    }

    if (!empty($nim_mhs)) {
        $update_mhs_sql = "
            UPDATE mahasiswa
            SET status = '1',
                ip     = $1,
                token  = $2,
                waktu  = $3
            WHERE nim = $4
        ";
        pg_query_params($conn, $update_mhs_sql, array(
            $ip_stat,
            $nomor_peserta,
            $now,
            $nim_mhs
        ));
    }

    $update_user_sql = "UPDATE t_user SET test = '5' WHERE username = $1";
    pg_query_params($conn, $update_user_sql, array($nomor_peserta));

    header("Location: completed.php?sid=" . urlencode($sid));
    exit;
}

$label_posisi = array(
    1 => 'University Legislative Council (DPM-U)',
    2 => 'University Student Executive Board (BEM-U)',
    3 => 'Faculty Legislative Council (DPM-F)',
    4 => 'Faculty Executive Board (BEM-F)',
    5 => 'Department Student Association (HIMA)',
);

$semua_soal = array();

for ($nomor_urut = 1; $nomor_urut <= 5; $nomor_urut++) {

    $id_soal = get_id_soal_for($nomor_urut, $id_jurusan, $conn);
    if ($id_soal === null || $id_soal === '') {
        continue;
    }

    // Pertanyaan
    $pertanyaan_sql = "SELECT pertanyaan FROM pertanyaan WHERE id_soal = $1 AND nomor_soal = $2";
$id_soal_str   = trim((string)$id_soal);
$nomor_soal_str = (string)$nomor_urut;

$pertanyaan_sql = "SELECT pertanyaan FROM pertanyaan WHERE id_soal = $1 AND nomor_soal = $2";
$pert_res = pg_query_params($conn, $pertanyaan_sql, array($id_soal_str, $nomor_soal_str));

if (!$pert_res || pg_num_rows($pert_res) == 0) {
    continue;
}
$row_pert = pg_fetch_assoc($pert_res);

    // Opsi A–E
    $opsi_list = array();

    // Section pasangan: BEM Univ (2), BEM Fakultas (4), HIMA (5)
    $is_pasangan_section = in_array($nomor_urut, array(2, 4, 5));

// get all option code A-Z
$opsi_codes = array();

// dari opsi_jawaban
$sql_opsi_codes_1 = "SELECT DISTINCT opsi FROM opsi_jawaban
                     WHERE id_soal = $1 AND nomor_soal = $2
                     ORDER BY opsi ASC";
$res_codes_1 = pg_query_params($conn, $sql_opsi_codes_1, array($id_soal, (string)$nomor_urut));
if ($res_codes_1) {
    while ($r = pg_fetch_assoc($res_codes_1)) {
        $opsi_codes[] = $r['opsi'];
    }
}

// opsi_jawaban_2 pasangan
if ($is_pasangan_section) {
    $sql_opsi_codes_2 = "SELECT DISTINCT opsi FROM opsi_jawaban_2
                         WHERE id_soal = $1 AND nomor_soal = $2
                         ORDER BY opsi ASC";
    $res_codes_2 = pg_query_params($conn, $sql_opsi_codes_2, array($id_soal, (string)$nomor_urut));
    if ($res_codes_2) {
        while ($r = pg_fetch_assoc($res_codes_2)) {
            if (!in_array($r['opsi'], $opsi_codes, true)) {
                $opsi_codes[] = $r['opsi'];
            }
        }
    }
}

// if empty, fallback minimal A-E (so not blank total)
if (empty($opsi_codes)) {
    $opsi_codes = array('A','B','C','D','E');
}

// === opsi_list build all code ===
foreach ($opsi_codes as $opsi_kode) {

    $teks_main   = '';
    $teks_second = '';

    // 1) teks utama dari opsi_jawaban
    $sql_opsi_main = "SELECT jawaban FROM opsi_jawaban
                      WHERE id_soal = $1 AND nomor_soal = $2 AND opsi = $3";
    $res_opsi_main = pg_query_params($conn, $sql_opsi_main, array($id_soal, (string)$nomor_urut, $opsi_kode));
    if ($res_opsi_main && pg_num_rows($res_opsi_main) > 0) {
        $row_opsi_main = pg_fetch_assoc($res_opsi_main);
        if (trim($row_opsi_main['jawaban']) !== '') {
            $teks_main = $row_opsi_main['jawaban'];
        }
    }

    // 2) teks kedua (khusus pasangan) dari opsi_jawaban_2
    if ($is_pasangan_section) {
        $sql_opsi_2 = "SELECT jawaban FROM opsi_jawaban_2
                       WHERE id_soal = $1 AND nomor_soal = $2 AND opsi = $3";
        $res_opsi_2 = pg_query_params($conn, $sql_opsi_2, array($id_soal, (string)$nomor_urut, $opsi_kode));
        if ($res_opsi_2 && pg_num_rows($res_opsi_2) > 0) {
            $row_opsi_2 = pg_fetch_assoc($res_opsi_2);
            if (trim($row_opsi_2['jawaban']) !== '') {
                $teks_second = $row_opsi_2['jawaban'];
            }
        }
    }

    // 3) media utama
    $media_files = array();
    $sql_media_main = "SELECT nama_file FROM media
                       WHERE id_soal = $1 AND nomor_soal = $2 AND opsi = $3";
    $res_media_main = pg_query_params($conn, $sql_media_main, array($id_soal, (string)$nomor_urut, $opsi_kode));
    if ($res_media_main && pg_num_rows($res_media_main) > 0) {
        while ($row_media_main = pg_fetch_assoc($res_media_main)) {
            if (trim($row_media_main['nama_file']) !== '') {
                $media_files[] = $row_media_main['nama_file'];
            }
        }
    }

    // 4) media_2 (khusus pasangan) + fallback teks_second dari media_2.jawaban
    if ($is_pasangan_section) {
        $sql_media_2 = "SELECT nama_file, jawaban FROM media_2
                        WHERE id_soal = $1 AND nomor_soal = $2 AND opsi = $3";
        $res_media_2 = pg_query_params($conn, $sql_media_2, array($id_soal, (string)$nomor_urut, $opsi_kode));
        if ($res_media_2 && pg_num_rows($res_media_2) > 0) {
            while ($row_media_2 = pg_fetch_assoc($res_media_2)) {
                if (trim($row_media_2['nama_file']) !== '') {
                    $media_files[] = $row_media_2['nama_file'];
                }
                if ($teks_second === '' && trim((string)$row_media_2['jawaban']) !== '') {
                    $teks_second = $row_media_2['jawaban'];
                }
            }
        }
    }

    // kalau benar-benar kosong, skip opsi ini
    if (trim($teks_main) === '' && trim($teks_second) === '' && empty($media_files)) {
        continue;
    }

    $opsi_list[$opsi_kode] = array(
        'teks_main'   => $teks_main,
        'teks_second' => $teks_second,
        'media'       => $media_files,
    );
}

// === TAMBAHKAN 1x surat suara (HARUS DI LUAR LOOP OPSI) ===
$semua_soal[] = array(
    'nomor_urut' => $nomor_urut,
    'label'      => $label_posisi[$nomor_urut],
    'id_soal'    => $id_soal,
    'pertanyaan' => $row_pert['pertanyaan'],
    'opsi'       => $opsi_list,
    'is_kosong'  => empty($opsi_list),
);

}


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="CampusBallot Voting Booth">
    <meta name="author" content="CampusBallot Core">
    <link rel="icon" href="../../images/favicon.ico">

    <title>CampusBallot Core - Voting Booth</title>

    <!-- Bootstrap 4.0-->
    <link rel="stylesheet" href="../../assets/vendor_components/bootstrap/dist/css/bootstrap.min.css">

    <!-- theme style -->
    <link rel="stylesheet" href="../../css/horizontal-menu.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/skin_color.css">

    <style>
        .surat-card {
            border-radius: 0.75rem;
            margin-bottom: 1rem;
        }
        .surat-card .card-header {
            font-weight: bold;
        }

        .opsi-kartu {
            display: block;
            width: 100%;
            cursor: pointer;
            margin-bottom: 0.5rem;
        }

        .opsi-radio {
            margin-right: 0.75rem;
            margin-top: 0.3rem;
            flex-shrink: 0;
        }

        .opsi-content {
            border-radius: 0.75rem;
            border: 1px solid #e0e0e0;
            padding: 0.6rem 0.8rem;
            display: flex;
            align-items: flex-start;
            width: 100%;
            background-color: #ffffff;
            transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .opsi-kartu:hover .opsi-content {
            background-color: #f5f5f5;
        }

        .opsi-kartu input[type="radio"]:checked ~ .opsi-content {
            border-color: #0d6efd;
            background-color: #e8f2ff;
            box-shadow: 0 0 0 2px rgba(13,110,253,.25);
        }

        .opsi-images {
            display: flex;
            gap: 0.5rem;
            margin-right: 0.75rem;
        }

        .opsi-images img {
            max-width: 90px;
            height: auto;
            border-radius: 0.5rem;
            object-fit: cover;
        }

        .opsi-text-block {
            display: flex;
            flex-direction: column;
        }

        .opsi-text-label {
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        @media (max-width: 575.98px) {
            .opsi-content {
                flex-direction: row;
                align-items: flex-start;
            }

            .opsi-images {
                gap: 0.4rem;
            }
            .opsi-images img {
                max-width: 72px;
            }
        }

.surat-card .card-body{
  counter-reset: opsi;
}

.opsi-text-label{
  font-size: 0;
  line-height: 0;
  margin: 0;
}

.opsi-text-label::before{
  counter-increment: opsi;
  content: counter(opsi) ".";
  font-size: 1rem;
  line-height: 1.2;
  font-weight: bold;
}


    </style>

</head>
<body class="layout-top-nav light-skin theme-classic">
<div class="wrapper">

    <header class="main-header">
        <div class="inside-header">
            <div class="d-flex align-items-center logo-box justify-content-between">
                <a href="../../index.php" class="logo">
                    <div class="logo-mini">
                        <span class="light-logo"><img src="../../images/logo.svg" style="height: 32px;" alt="logo"></span>
                        <span class="dark-logo"><img src="../../images/logo.svg" style="height: 32px;" alt="logo"></span>
                    </div>
                    <div class="logo-lg">
                        <span class="light-logo"><img src="../../images/logo-text.svg" style="height: 24px;" alt="CampusBallot"></span>
                        <span class="dark-logo"><img src="../../images/logo-text.svg" style="height: 24px;" alt="CampusBallot"></span>
                    </div>
                </a>
            </div>
            <nav class="navbar navbar-static-top">
                <div class="navbar-custom-menu r-side">
                    <ul class="nav navbar-nav"></ul>
                </div>
            </nav>
        </div>
    </header>

    <div class="content-wrapper">
        <div class="container-full">
            <div class="content-header">
                <h3><b>Official Voting Booth</b></h3>
                <p class="text-primary mb-1">
                    This screen displays your eligible ballot papers:
                    University Legislative, University Executive Ticket, Faculty Legislative, Faculty Executive, and Department Association.
                </p>
                <p class="text-muted mb-3">
                    Do not close the browser, refresh the page, or leave this window before clicking <strong>Submit Official Ballot</strong>.
                </p>
            </div>

            <section class="content">
                <div class="col-12">
                    <div class="box text-dark p-2 p-sm-3">

                        <form id="formSurat" action="ballot.php" method="post">
                            <input type="hidden" name="sid" value="<?php echo htmlspecialchars($sid); ?>">

                            <div class="row">
                                <?php if (empty($semua_soal)): ?>
                                    <div class="col-12">
                                        <div class="alert alert-warning">
                                            No ballot papers are currently available for your account.
                                            Please contact the election committee for support.
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($semua_soal as $soal): ?>
                                        <?php
                                            $is_pasangan_section = in_array($soal['nomor_urut'], array(2,4,5));
                                        ?>
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <div class="card surat-card">
                                                <div class="card-header bg-primary text-white">
                                                    <?php echo htmlspecialchars($soal['label']); ?>
                                                </div>
                                                <div class="card-body">
                                                    <p><strong><?php echo htmlspecialchars($soal['pertanyaan']); ?></strong></p>

                                                    <?php if (!empty($soal['opsi'])): ?>
                                                        <?php foreach ($soal['opsi'] as $kode => $opsi): ?>
                                                            <label class="opsi-kartu">
                                                                <!-- Radio -->
                                                                <input
                                                                    class="form-check-input opsi-radio"
                                                                    type="radio"
                                                                    name="jawaban[<?php echo (int)$soal['nomor_urut']; ?>]"
                                                                    value="<?php echo htmlspecialchars($kode); ?>"
                                                                >
                                                                <div class="opsi-content">
                                                                    <?php if (!empty($opsi['media'])): ?>
                                                                        <div class="opsi-images">
                                                                            <?php foreach ($opsi['media'] as $file): ?>
                                                                                <?php 
                                                                                $img_src = (!empty($file) && file_exists(__DIR__ . '/' . $file)) 
                                                                                    ? htmlspecialchars($file) 
                                                                                    : 'candidates/candidate-placeholder.svg';
                                                                                ?>
                                                                                <img src="<?php echo $img_src; ?>"
                                                                                     onerror="this.src='candidates/candidate-placeholder.svg'"
                                                                                     alt="Candidate <?php echo htmlspecialchars($kode); ?>">
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <div class="opsi-images">
                                                                            <img src="candidates/candidate-placeholder.svg"
                                                                                 alt="Candidate <?php echo htmlspecialchars($kode); ?>">
                                                                        </div>
                                                                    <?php endif; ?>

                                                                    <div class="opsi-text-block">
                                                                   <div class="opsi-text-label">
    <?php echo htmlspecialchars($kode); ?>.
</div>



                                                                        <div class="opsi-text-body">
                                                                            <?php if ($is_pasangan_section): ?>
                                                                                <?php if (!empty($opsi['teks_main'])): ?>
                                                                                    <div>
                                                                                        <strong>President / Chair:</strong>
                                                                                        <?php echo nl2br(htmlspecialchars($opsi['teks_main'])); ?>
                                                                                    </div>
                                                                                <?php endif; ?>

                                                                                <?php if (!empty($opsi['teks_second'])): ?>
                                                                                    <div class="mt-1">
                                                                                        <strong>Vice President / Co-Chair:</strong>
                                                                                        <?php echo nl2br(htmlspecialchars($opsi['teks_second'])); ?>
                                                                                    </div>
                                                                                <?php endif; ?>
                                                                            <?php else: ?>
                                                                                <?php if (!empty($opsi['teks_main'])): ?>
                                                                                    <?php echo nl2br(htmlspecialchars($opsi['teks_main'])); ?>
                                                                                <?php endif; ?>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        <?php endforeach; ?>

                                                        <small class="text-muted">
                                                            To abstain from this ballot, leave all options unselected.
                                                        </small>
                                                    <?php else: ?>
                                                        <p class="text-muted mb-1">
                                                            No active candidate options on this ballot (Uncontested / Empty).
                                                        </p>
                                                        <small class="text-muted">
                                                            Refer to election guidelines for uncontested ballot seats.
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                             </div>
                                         </div>
                                     <?php endforeach; ?>
                                 <?php endif; ?>
                             </div>

                             <?php if (!empty($semua_soal)): ?>
                                 <div class="text-center mt-3 mb-3">
                                     <button type="submit" name="submit_vote" value="1" class="btn btn-lg btn-success">
                                         Submit Official Ballot
                                     </button>
                                 </div>
                             <?php endif; ?>
                        </form>

                    </div>
                </div>
            </section>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous"></script>

<script src="../../js/vendors.min.js"></script>
<script src="../../js/jquery.smartmenus.js"></script>
<script src="../../js/menus.js"></script>
<script src="../../js/template.js"></script>

<script>
    let formSubmitted = false;
    document.getElementById('formSurat').addEventListener('submit', function () {
        formSubmitted = true;
    });

    window.addEventListener('beforeunload', function (e) {
        if (!formSubmitted) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>

</body>
</html>