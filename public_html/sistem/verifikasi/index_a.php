<?php
ini_set("error_reporting", E_ALL & ~E_NOTICE);
ini_set("display_errors", 0);

require_once('Cas.php');
require_once __DIR__ . '/../make_conn.php';

// =============================
// 1. SSO LOGIN
// =============================
$cas = new CAS(array());
$cas->force_auth();

$alamat_user = $cas->user()->userlogin; // e.g. student.name@campus.edu

// =============================
// 2. AMBIL DATA MAHASISWA BERDASARKAN EMAIL SSO
// =============================
$cek_user_sql = "SELECT nim FROM mahasiswa WHERE email = $1";
$cek_user_result = pg_query_params($conn, $cek_user_sql, array($alamat_user));
$cek_user = pg_fetch_assoc($cek_user_result);

if (!$cek_user || empty($cek_user['nim'])) {
    // Tidak ketemu di tabel mahasiswa
    echo "<p align='center'>
        Akun SSO Anda: <b>" . htmlspecialchars($alamat_user) . "</b><br>
        tidak ditemukan di sistem pemilih.<br><br>
        Jika Anda mahasiswa aktif, silakan coba lagi dengan menggunakan mode penyamaran / incognito,<br>
        dan ketik ulang email SSO Anda (jangan gunakan autofill).<br><br>
        Jika masih tidak bisa, silakan hubungi KPU.<br><br>
        <a href='../../index.php' class='btn btn-primary'>Kembali</a>
    </p>";
    exit;
}

$nim = $cek_user['nim'];

// Ambil data lengkap mahasiswa (TAMBAH: ip, waktu)
$cek_pmb_sql = "
    SELECT nim, nama, prodi, fakultas, status, id_prodi, id_jurusan, ip, waktu
    FROM mahasiswa
    WHERE nim = $1
";
$cek_pmb_sql_result = pg_query_params($conn, $cek_pmb_sql, array($nim));
$cek_pmb = pg_fetch_assoc($cek_pmb_sql_result);

if (!$cek_pmb || empty($cek_pmb['nama'])) {
    echo "<p align='center'>
        Data mahasiswa tidak lengkap di sistem.<br>
        NIM: <b>" . htmlspecialchars($nim) . "</b><br><br>
        Jika Anda mahasiswa aktif, silakan hubungi KPU.<br><br>
        <a href='../../index.php' class='btn btn-primary'>Kembali</a>
    </p>";
    exit;
}

// Normalisasi variabel
$nama_mhs   = $cek_pmb['nama'];
$prodi      = $cek_pmb['prodi'];
$fakultas   = $cek_pmb['fakultas'];
$status     = $cek_pmb['status'];      // NULL = belum memilih, non-NULL = sudah memilih
$id_prodi   = $cek_pmb['id_prodi'];
$id_jurusan = $cek_pmb['id_jurusan'];
$ip_tercatat = $cek_pmb['ip'];
$waktu_pilih = $cek_pmb['waktu'];
$angkatan   = substr($nim, 0, 2);

// Flag dan variabel untuk proses token & sid
$sudah_memilih = false;
$nomor_peserta = null;   // ini adalah "token" (username di t_user)
$sid           = null;   // id_session di t_log

// Untuk tampilan info waktu/IP jika sudah memilih
$info_waktu_tampil = '';
$info_ip_tampil    = '';
$info_isp_tampil   = '';

// =============================
// 3. CEK APAKAH SUDAH PERNAH MEMILIH (berdasar mahasiswa.status)
// =============================
if (!is_null($status)) {
    // Sudah pernah memilih -> tidak buat token lagi
    $sudah_memilih = true;

    // --- Format waktu pilih (jika ada) ---
    if (!empty($waktu_pilih)) {
        $ts = strtotime($waktu_pilih);
        if ($ts !== false) {
            // Format lokal: 24-12-2025 13:45:10
            $info_waktu_tampil = date('d-m-Y H:i:s', $ts) . " WIB";
        } else {
            // Kalau parsing gagal, tampilkan raw value
            $info_waktu_tampil = $waktu_pilih;
        }
    }

    // --- IP tercatat (jika ada) ---
    if (!empty($ip_tercatat)) {
        $info_ip_tampil = $ip_tercatat;
    }

    // Pesan utama (tetap sama, tapi nanti info waktu/IP/ISP kita tampilkan di box bawah)
    $pesan = "<b>NIM sudah digunakan untuk memilih.</b><br>
              Jika Anda merasa belum memilih, silakan hubungi KPU.<br><br>
              NIM Anda: <b>" . htmlspecialchars($nim) . "</b><br>
              Email SSO: <b>" . htmlspecialchars($alamat_user) . "</b>";
} else {
    // =============================
    // 4. BELUM MEMILIH → GENERATE TOKEN BARU (username di t_user)
    // =============================
    $unik     = false;
    $username = null;

    while ($unik == false) {
        // 12 digit angka random
        $username = mt_rand(100000000000, 999999999999);

        $kode_kartu_sql = "SELECT username FROM t_user WHERE username = $1";
        $kode_kartu_result = pg_query_params($conn, $kode_kartu_sql, array($username));
        $kode_kartu_data = pg_fetch_assoc($kode_kartu_result);

        if (!$kode_kartu_data || $kode_kartu_data['username'] == '') {
            $unik = true;

            // t_user.nama diisi id_prodi, t_user.foto diisi id_jurusan (pola lama)
            $isi_t_user_sql = "
                INSERT INTO t_user (username, nama, foto)
                VALUES ($1, $2, $3)
            ";
            pg_query_params($conn, $isi_t_user_sql, array($username, $id_prodi, $id_jurusan));
        } else {
            $unik = false;
        }
    }

    // username inilah yang selama ini menjadi "token" / nomor_peserta
    $nomor_peserta = $username;

    // =============================
    // 5. UPDATE TOKEN DI TABEL MAHASISWA
    //    (status, ip, waktu TIDAK diubah di sini — hanya di soal_surat.php setelah submit)
    // =============================
    $set_token_sql = "
        UPDATE mahasiswa
        SET token = $1
        WHERE nim = $2
    ";
    pg_query_params($conn, $set_token_sql, array($nomor_peserta, $nim));

    // =============================
    // 6. BUAT SID + TULIS KE T_LOG
    //    Agar soal_surat.php bisa membaca nomor_peserta & id_jurusan dari t_log
    // =============================
    $tes = 'PMW';
    $sid = md5($nomor_peserta . $tes);

    $ip_stat = $_SERVER['REMOTE_ADDR'];

    // Di sistem lama (bilik/index_a.php):
    //   - t_log.nomor_peserta = username (token / id_peserta)
    //   - t_log.foto          = id_jurusan
    //   - t_log.nama          = $nama (di sana berisi id_prodi)
    //   - t_log.id_soal       = id_prodi
    // Kita ikuti pola itu supaya soal_surat.php kompatibel.
    $id_soal_log = $id_prodi;
    $nama_log    = $id_prodi;

    $cek_sid_sql = "SELECT id_session FROM t_log WHERE id_session = $1";
    $cek_sid_result = pg_query_params($conn, $cek_sid_sql, array($sid));
    $cek_sid = pg_fetch_assoc($cek_sid_result);

    if (empty($cek_sid['id_session'])) {
        $isi_sid_sql = "
            INSERT INTO t_log (id_session, nomor_peserta, id_soal, foto, nama, ip_station, tes)
            VALUES ($1, $2, $3, $4, $5, $6, $7)
        ";
        pg_query_params($conn, $isi_sid_sql, array(
            $sid,
            $nomor_peserta,
            $id_soal_log,
            $id_jurusan,
            $nama_log,
            $ip_stat,
            $tes
        ));
    }

    // Tidak perlu kirim WA / SMS token lagi (fitur lama sudah tidak dipakai)
    $pesan = ""; // tampilan dihandle lewat HTML di bawah
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../images/favicon.ico">

    <title>CampusBallot Core - Voter Verification</title>

    <!-- Bootstrap 4.0-->
    <link rel="stylesheet" href="../../assets/vendor_components/bootstrap/dist/css/bootstrap.min.css">

    <!-- theme style -->
    <link rel="stylesheet" href="../../css/horizontal-menu.css">
    <link rel="stylesheet" href="../../css/style.css">

    <!-- Admin skins -->
    <link rel="stylesheet" href="../../css/skin_color.css">

</head>
<body class="layout-top-nav light-skin theme-classic">
<!-- Site wrapper -->
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
            <!-- Header Navbar -->
            <nav class="navbar navbar-static-top">
                <div class="navbar-custom-menu r-side">
                    <ul class="nav navbar-nav"></ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <div class="container-full">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <h3>Verifikasi & Bilik Pemilihan</h3>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-12">

                        <div class="box">
                            <div class="box-header with-border">
                                <h4 class="box-title"><b>Data Pemilih</b></h4>
                            </div>
                            <div class="box-body text-dark">
                                <table class="table table-bordered">
                                    <tr>
                                        <th scope="row">NIM</th>
                                        <td><?php echo htmlspecialchars($nim); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Nama</th>
                                        <td><?php echo htmlspecialchars($nama_mhs); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Prodi</th>
                                        <td><?php echo htmlspecialchars($prodi); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Fakultas</th>
                                        <td><?php echo htmlspecialchars($fakultas); ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Email SSO</th>
                                        <td><?php echo htmlspecialchars($alamat_user); ?></td>
                                    </tr>
                                </table>

                                <?php if ($sudah_memilih): ?>
                                    <div class="alert alert-danger mt-3">
                                        <?php echo $pesan; ?>
                                    </div>

                                    <div class="mt-3">
                                        <h5><b>Rincian Waktu & IP Pemilihan yang Telah Anda Lakukan</b></h5>
                                        <table class="table table-sm table-striped" style="max-width: 480px;">
                                            <tr>
                                                <th scope="row" style="width: 40%;">Waktu memilih</th>
                                                <td>
                                                    <?php echo $info_waktu_tampil
                                                        ? htmlspecialchars($info_waktu_tampil)
                                                        : '<span class="text-muted">Tidak tercatat</span>'; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">IP tercatat</th>
                                                <td>
                                                    <?php echo $info_ip_tampil
                                                        ? htmlspecialchars($info_ip_tampil)
                                                        : '<span class="text-muted">Tidak tercatat</span>'; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                <?php else: ?>
                                    <p class="text-danger mt-2">
                                        Pastikan Anda adalah pemilik dari akun SSO sesuai dengan data di atas!
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!$sudah_memilih): ?>
                            <div class="box">
                                <div class="box-header with-border">
                                    <h4 class="box-title"><b>PETUNJUK PEMILIHAN</b></h4>
                                </div>
                                <div class="box-body text-dark">
                                    <ul>
                                        <li style="font-weight: bold; color: red; font-size: 1.3rem">
                                            BACA DAN PAHAMI SEBELUM MEMILIH!
                                        </li><br>
                                        <li style="font-weight: bold; color: red; font-size: 1.1rem">
                                            JANGAN MENINGGALKAN / REFRESH / PINDAH TAB / BUKA APLIKASI LAIN
                                            SELAMA PROSES PEMILIHAN.
                                        </li><br>
                                        <li>
                                            Halaman berikutnya adalah <b>Bilik Pemilihan</b> yang memuat
                                            5 surat suara:
                                            <ul>
                                                <li>DPM Universitas</li>
                                                <li>BEM Universitas</li>
                                                <li>DPM Fakultas</li>
                                                <li>BEM Fakultas</li>
                                                <li>Himpunan Mahasiswa (HIMA)</li>
                                            </ul>
                                        </li>
                                        <li>
                                            Untuk memilih, klik pada kartu calon/pasangan yang tersedia
                                            di setiap surat suara.
                                        </li>
                                        <li>
                                            Jika tidak ada calon pada suatu surat suara, atau Anda memilih
                                            untuk golput pada surat tersebut, cukup biarkan semua opsi
                                            tidak terpilih.
                                        </li>
                                        <li>
                                            Setelah menekan tombol <b>Kirim Pilihan</b> di halaman bilik,
                                            Anda <b>tidak dapat</b> mengubah pilihan.
                                        </li>
                                    </ul>

                                    <p>
                                        Klik tombol di bawah ini untuk masuk ke Bilik Pemilihan.
                                        <br><b>(Selesaikan dulu proses pemilihan, jangan sambil buka aplikasi lain!)</b>
                                    </p>

                                    <form action="../bilik/soal_surat.php" method="post" class="mt-3">
                                        <input type="hidden" name="sid" value="<?php echo htmlspecialchars($sid); ?>">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            Mulai Memilih
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </section>
            <!-- /.content -->

        </div>
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
        &copy; CampusBallot Core · Open-Source Collegiate Election Engine
    </footer>

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

<!-- js -->
<script src="../../js/vendors.min.js"></script>
<script src="../../js/jquery.smartmenus.js"></script>
<script src="../../js/menus.js"></script>
<script src="../../js/template.js"></script>

<!-- Form validator JavaScript -->
<script src="../../js/pages/validation.js"></script>
<script src="../../js/pages/form-validation.js"></script>

</body>
</html>
