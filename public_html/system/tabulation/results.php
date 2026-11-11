<?php
ini_set("error_reporting", E_ALL & ~E_NOTICE);
ini_set("display_errors", 0);

require_once __DIR__ . '/../db_connect.php';

$kode_fak   = trim((string)($_GET['kode_fak'] ?? ''));
$nomor_soal = trim((string)($_GET['nomor_soal'] ?? ''));

if ($kode_fak === '' || $nomor_soal === '') {
    die("Parameter tidak lengkap. Gunakan rekap.php?kode_fak=...&nomor_soal=1..5");
}
if (!preg_match('/^\d{1,4}$/', $kode_fak)) die("kode_fak/id_soal tidak valid.");
if (!preg_match('/^[1-5]$/', $nomor_soal)) die("nomor_soal tidak valid (1-5).");

// -------------------- LOCKING GATE --------------------
function lock_id_fak_from_kode($kode_fak) {
    $kode_fak = trim((string)$kode_fak);

    // 1 digit: sudah faculty code
    if (preg_match('/^\d$/', $kode_fak)) {
        return $kode_fak;
    }

    // 4 digit: ambil 2 digit pertama sebagai kode fakultas (mis. 1001 -> 10)
    if (preg_match('/^\d{4}$/', $kode_fak)) {
        return substr($kode_fak, 0, 2);
    }

    // fallback (kalau tiba-tiba format lain masuk)
    return substr($kode_fak, 0, 1);
}

$id_fak_lock = lock_id_fak_from_kode($kode_fak);

$cek_status_sql = "SELECT status FROM status_rekap WHERE id_fak = $1";
$cek_status_res = pg_query_params($conn, $cek_status_sql, array($id_fak_lock));
$cek_status_row = $cek_status_res ? pg_fetch_assoc($cek_status_res) : null;

if (!$cek_status_row) {
    die("status_rekap tidak ditemukan untuk id_fak=" . htmlspecialchars($id_fak_lock));
}
if ($cek_status_row['status'] === '0') {
    header("Location: unlock.php?kode_fak=" . urlencode($kode_fak) . "&nomor_soal=" . urlencode($nomor_soal));
    exit;
}


// -------------------- LABELS --------------------
$posisi_label = [
    '1' => 'University Legislative Council (DPM-U)',
    '2' => 'University Student Executive Board (BEM-U)',
    '3' => 'Faculty Legislative Council (DPM-F)',
    '4' => 'Faculty Executive Board (BEM-F)',
    '5' => 'Department Student Association (HIMA)',
];

// -------------------- MAIN DATA --------------------
$is_pasangan = in_array($nomor_soal, ['2','4','5'], true); // BEM Univ, BEM Fak, HIMA

// NOTE: Only change is COUNT source -> v_rekap_new.count
$rekap_sql = "
SELECT
    o.nomor_soal,
    o.id_soal,
    o.opsi                                  AS jawaban,

    -- Ketua
    o.jawaban                               AS nama_ketua,
    COALESCE(m.nama_file,'')                AS foto_ketua,

    -- Wakil (optional)
    COALESCE(o2.jawaban,'')                 AS nama_wakil,
    COALESCE(m2.nama_file,'')               AS foto_wakil,

    p.pertanyaan                            AS tingkat,

    -- count from v_rekap_new (the proven logic)
    COALESCE(v.count, 0)                    AS count
FROM opsi_jawaban o
JOIN pertanyaan p
  ON p.id_soal=o.id_soal AND p.nomor_soal=o.nomor_soal
LEFT JOIN media m
  ON m.id_soal=o.id_soal AND m.nomor_soal=o.nomor_soal AND m.opsi=o.opsi

LEFT JOIN opsi_jawaban_2 o2
  ON o2.id_soal=o.id_soal AND o2.nomor_soal=o.nomor_soal AND o2.opsi=o.opsi
LEFT JOIN media_2 m2
  ON m2.id_soal=o.id_soal AND m2.nomor_soal=o.nomor_soal AND m2.opsi=o.opsi

LEFT JOIN v_rekap_new v
  ON v.id_soal=o.id_soal AND v.nomor_soal=o.nomor_soal AND v.opsi=o.opsi

WHERE o.id_soal = $1 AND o.nomor_soal = $2
ORDER BY o.opsi
";

$rekap_res = pg_query_params($conn, $rekap_sql, array($kode_fak, $nomor_soal));

$rows = [];
$total_votes = 0;

if ($rekap_res) {
    while ($r = pg_fetch_assoc($rekap_res)) {
        $r['count'] = (int)($r['count'] ?? 0);
        $total_votes += $r['count'];
        $rows[] = $r;
    }
}

$tingkat_full = null;
if (!empty($rows)) {
    $tingkat_full = $rows[0]['tingkat'];
}

// -------------------- FALLBACK DIAGNOSTIC (only if empty) --------------------
$diag = null;
if (empty($rows)) {
    $cand_sql = "SELECT COUNT(*) AS n FROM opsi_jawaban WHERE id_soal=$1 AND nomor_soal=$2";
    $cand_res = pg_query_params($conn, $cand_sql, array($kode_fak, $nomor_soal));
    $cand_row = $cand_res ? pg_fetch_assoc($cand_res) : null;

    $vote_sql = "SELECT COUNT(*) AS n FROM peserta_jawaban WHERE id_soal=$1 AND nomor_soal=$2";
    $vote_res = pg_query_params($conn, $vote_sql, array($kode_fak, $nomor_soal));
    $vote_row = $vote_res ? pg_fetch_assoc($vote_res) : null;

    $diag = [
        'candidates' => (int)($cand_row['n'] ?? 0),
        'votes'      => (int)($vote_row['n'] ?? 0),
    ];
}

// -------------------- VOTERS + DPT + GOLPUT --------------------
$total_voters_sql = "SELECT COUNT(DISTINCT id_peserta) AS voters
                     FROM peserta_jawaban
                     WHERE id_soal=$1 AND nomor_soal=$2";
$voters_res = pg_query_params($conn, $total_voters_sql, array($kode_fak, $nomor_soal));
$voters_row = $voters_res ? pg_fetch_assoc($voters_res) : null;
$total_voters = (int)($voters_row['voters'] ?? 0);

function dpt_for_idsoal($conn, $id_soal) {
    $id_soal = trim((string)$id_soal);

    if ($id_soal === '0') {
        $r = pg_query($conn, "SELECT COUNT(*) AS dpt FROM mahasiswa");
        return (int)(pg_fetch_assoc($r)['dpt'] ?? 0);
    }
    if (preg_match('/^\d{3}$/', $id_soal)) {
        $r = pg_query_params($conn, "SELECT COUNT(*) AS dpt FROM mahasiswa WHERE id_jurusan=$1", array($id_soal));
        return (int)(pg_fetch_assoc($r)['dpt'] ?? 0);
    }
    if (preg_match('/^\d{4}$/', $id_soal)) {
        if (in_array($id_soal, ['1001','1101','1201'], true)) {
            $prefix = substr($id_soal, 0, 2);
            $r = pg_query_params($conn, "SELECT COUNT(*) AS dpt FROM mahasiswa WHERE id_jurusan LIKE $1", array($prefix.'%'));
            return (int)(pg_fetch_assoc($r)['dpt'] ?? 0);
        }
        $r = pg_query_params($conn, "SELECT COUNT(*) AS dpt FROM mahasiswa WHERE id_jurusan=$1", array($id_soal));
        return (int)(pg_fetch_assoc($r)['dpt'] ?? 0);
    }
    if (preg_match('/^\d{1}$/', $id_soal)) {
        $r = pg_query_params($conn, "SELECT COUNT(*) AS dpt FROM mahasiswa WHERE substring(id_jurusan,1,1)=$1", array($id_soal));
        return (int)(pg_fetch_assoc($r)['dpt'] ?? 0);
    }
    return 0;
}

$total_dpt = dpt_for_idsoal($conn, $kode_fak);
$golput = max(0, $total_dpt - $total_voters);

// -------------------- CHART DATA --------------------
$labels = [];
$counts = [];
foreach ($rows as $r) {
    $nama = $r['nama_ketua'];
    if ($is_pasangan && trim($r['nama_wakil']) !== '') {
        $nama .= ' / ' . $r['nama_wakil'];
    }
    $labels[] = $nama;
    $counts[] = (int)$r['count'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../../images/favicon.ico">
    <title>Official Election Tabulation - CampusBallot Core</title>

    <link rel="stylesheet" href="../../assets/vendor_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/horizontal-menu.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/skin_color.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.js"
            integrity="sha512-zO8oeHCxetPn1Hd9PdDleg5Tw1bAaP0YmNvPY8CwcRyUk7d7/+nyElmFrB6f7vg4f7Fv4sui1mcep8RIEShczg=="
            crossorigin="anonymous"></script>

<style>
  :root{
    --brand-primary:#003153;
    --brand-primary-light:#004a80;
    --brand-primary-soft:#e8f1f8;
  }

  body{ background:#f5f9fc; color:#000; }

  .content-header h3{ font-weight:900; color:var(--brand-primary); letter-spacing:.4px; }
  .content-header p{ color:#000; font-size:16px; margin-top:4px; }

  .box{
    border-radius:18px;
    border:2px solid var(--brand-primary);
    box-shadow:0 8px 24px rgba(0,0,0,.08);
    background:#fff;
  }
  .box-body{ padding:18px 18px 20px; }
  .box-title{ font-weight:900; color:var(--brand-primary); font-size:22px; }

  .stat{
    background:#fff;
    border:2px solid var(--brand-primary);
    border-radius:18px;
    padding:14px 16px;
    box-shadow:0 6px 14px rgba(0,0,0,.08);
    min-height:90px;
  }
  .stat .t{
    font-size:14px;
    font-weight:800;
    color:var(--brand-primary);
    text-transform:uppercase;
    letter-spacing:.6px;
  }
  .stat .v{ font-size:32px; font-weight:900; color:#000; line-height:1.1; }
  .stat.golput{ border-width:3px; background:var(--brand-primary-soft); }

  .chart-wrap{
    height:420px;
    background:#fff;
    border-radius:16px;
    padding:10px;
    border:2px solid var(--brand-primary);
  }

.chart-scroll{
  overflow-x:auto;
  overflow-y:hidden;
  -webkit-overflow-scrolling:touch;
}

.chart-scroll canvas{
  display:block;          /* penting: cegah layout aneh */
  height:380px !important;/* tinggi stabil */
}


  .table-responsive{ overflow-x:auto; -webkit-overflow-scrolling:touch; }
  table.rekap{
    width:100%;
    border-collapse:collapse;
    background:#fff;
    min-width:720px;
  }
  table.rekap th, table.rekap td{
    border:2px solid var(--brand-primary);
    padding:10px 12px;
    font-size:16px;
    color:#000;
  }
  table.rekap th{
    background:var(--brand-primary);
    color:#fff;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.6px;
    font-size:14px;
  }
  table.rekap tbody tr:nth-child(even){ background:var(--brand-primary-soft); }
  .dim-0{ opacity:.65; font-style:italic; }

  .btn-warning{ font-weight:800; }

  @media (min-width:1200px){
    .content-header h3{ font-size:34px; }
    .stat .v{ font-size:38px; }
    .box-title{ font-size:26px; }
    table.rekap th, table.rekap td{ font-size:18px; }
  }

  html, body{ height:auto !important; overflow-x:hidden; overflow-y:auto !important; }
  .wrapper, .content-wrapper, .container-full, section.content{
    min-height:0 !important; height:auto !important; overflow:visible !important;
  }

  .back-mobile{ display:none; }
  .back-desktop{ display:block; }

  @media (max-width:767.98px){
    .content-wrapper{ padding-bottom:110px; }
    .back-desktop{ display:none; }
    .back-mobile{
      display:block;
      position:fixed;
      left:12px; right:12px; bottom:12px;
      z-index:9999;
    }
    .back-mobile .btn{
      border-radius:18px;
      font-weight:900;
      box-shadow:0 8px 20px rgba(0,0,0,.25);
    }
    table.rekap th, table.rekap td{
      font-size:14px;
      padding:8px 10px;
      white-space:nowrap;
    }
  }

.calon-img{
  width:72px;
  height:96px;              /* rasio konsisten (portrait) */
  max-width:72px;
  object-fit:cover;         /* crop rapi tanpa distorsi */
  object-position:center;
  border-radius:10px;
  border:2px solid var(--brand-primary);
  background:#fff;
}

  /* Turn A/B/C into 1/2/3 visually (table only) */
  table.rekap tbody{ counter-reset: calon; }
  table.rekap td.kode{
    position:relative;
    color:transparent;
  }
  table.rekap td.kode::before{
    counter-increment: calon;
    content: counter(calon);
    color:#000;
    font-weight:900;
    position:absolute;
    left:12px;
    top:50%;
    transform:translateY(-50%);
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
        </div>
    </header>

    <div class="content-wrapper">
        <div class="container-full">
            <div class="content-header">
                <h3 class="mb-0">
                    <?php echo htmlspecialchars($tingkat_full ?? ($posisi_label[$nomor_soal] ?? 'Rekapitulasi')); ?>
                </h3>
                <p class="text-muted mb-0">
                    Kode Fakultas: <strong><?php echo htmlspecialchars($kode_fak); ?></strong> |
                    Nomor Surat: <strong><?php echo htmlspecialchars($nomor_soal); ?></strong>
                </p>
            </div>

            <section class="content">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
<div class="col-md-3 col-6"><div class="stat"><div class="t">DPT</div><div class="v"><?php echo number_format($total_dpt,0,',','.'); ?></div></div></div> 
<div class="col-md-3 col-6"><div class="stat"><div class="t">Memilih</div><div class="v"><?php echo number_format($total_voters,0,',','.'); ?></div></div></div> 
<div class="col-md-3 col-6"><div class="stat golput"><div class="t">Tidak Memilih</div><div class="v"><?php echo number_format($golput,0,',','.'); ?></div></div></div> 
<div class="col-md-3 col-6"><div class="stat"><div class="t">Total Suara Kandidat</div><div class="v"><?php echo number_format($total_votes,0,',','.'); ?></div></div></div>
                        </div>
                    </div><br>

                    <div class="col-xl-6 col-12 mt-3">
                        <div class="box">
                            <div class="box-body text-dark">
                                <h4 class="box-title mb-2">Diagram (Bar)</h4>
                                <p class="text-muted">
  Pada layar mobile, diagram batang dapat terlihat kurang optimal.
  Disarankan membuka halaman ini menggunakan mode desktop.
</p>

                                <?php if (empty($rows)): ?>
                                    <div class="alert alert-warning mb-0">
                                        Data rekap kosong.
                                        <?php if ($diag): ?>
                                            <div class="mt-2">
                                                Kandidat terdaftar (opsi_jawaban): <strong><?php echo $diag['candidates']; ?></strong><br>
                                                Suara masuk (peserta_jawaban): <strong><?php echo $diag['votes']; ?></strong><br>
                                                <small class="text-muted">
                                                    Jika kandidat &gt; 0 tapi view kosong, cek definisi JOIN.
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
<div class="chart-wrap chart-scroll">
    <canvas id="chartBar"></canvas>
</div>

<script>
(function () {
  var labels = <?php echo json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;
  var counts = <?php echo json_encode($counts); ?>;

  var canvas = document.getElementById('chartBar');
  if (!canvas) return;

  var container = canvas.parentElement; // .chart-wrap.chart-scroll
  var barWidth = 90; // px per kandidat (silakan tweak 70-110)
  var minWidth = labels.length * barWidth;

  // Lebar final = minimal lebar untuk semua bar, atau minimal selebar container
  var finalWidth = Math.max(minWidth, container.clientWidth);
  var finalHeight = 380;

  // Pastikan canvas benar-benar melebar (untuk scroll) dan ukurannya fixed
  canvas.style.width = finalWidth + 'px';
  canvas.style.height = finalHeight + 'px';
  canvas.width = finalWidth;
  canvas.height = finalHeight;

  var ctx = canvas.getContext('2d');

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Suara',
        data: counts,
        backgroundColor: '#003153',
        hoverBackgroundColor: '#004a80',
        borderColor: '#00223a',
        borderWidth: 1,
        barThickness: 28,
        maxBarThickness: 36
      }]
    },
    options: {
      responsive: false,            // ✅ penting: biar tidak “balik” jadi sempit
      maintainAspectRatio: false,
      legend: { display: false },
      tooltips: { enabled: true },
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true,
            precision: 0,
            fontSize: 14,
            fontColor: '#000'
          },
          gridLines: { color: 'rgba(0,0,0,.15)' }
        }],
        xAxes: [{
          ticks: {
            autoSkip: false,
            fontSize: 12,
            fontColor: '#000',
            maxRotation: 45,
            minRotation: 0
          },
          gridLines: { display: false }
        }]
      }
    }
  });
})();
</script>

                                <?php endif; ?>

                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-12 mt-3">
                        <div class="box">
                            <div class="box-body text-dark">
                                <h4 class="box-title mb-2">Tabel</h4>

                                <?php if (empty($rows)): ?>
                                    <div class="alert alert-warning mb-0">
                                        Tidak ada baris untuk ditampilkan.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="rekap">
                                            <thead>
                                            <tr>
                                                <th width="45%">Tingkat</th>
                                                <th width="10%">Nomor Urut</th>
                                                <th width="35%">Nama</th>
                                                <th width="10%">Suara</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($rows as $r): ?>
                                                <?php $dim = ((int)$r['count'] === 0) ? "class='dim-0'" : ""; ?>
                                                <tr <?php echo $dim; ?>>
                                                    <td><?php echo htmlspecialchars($r['tingkat']); ?></td>
                                                    <td class="kode"><?php echo htmlspecialchars($r['jawaban']); ?></td>
                                                    <td>
                                                        <?php if ($is_pasangan): ?>
                                                            <div><strong>Ketua:</strong> <?php echo htmlspecialchars($r['nama_ketua']); ?></div>
                                                            <?php if (trim($r['nama_wakil']) !== ''): ?>
                                                                <div class="mt-1"><strong>Wakil:</strong> <?php echo htmlspecialchars($r['nama_wakil']); ?></div>
                                                            <?php endif; ?>
                                                            <div class="mt-2 d-flex gap-2 flex-wrap">
                                                                <?php if (trim($r['foto_ketua']) !== ''): ?>
                                                                    <img class="calon-img" src="<?php echo htmlspecialchars($r['foto_ketua']); ?>" onerror="this.src='../booth/candidates/candidate-placeholder.svg'" alt="Leader">
                                                                <?php else: ?>
                                                                    <img class="calon-img" src="../booth/candidates/candidate-placeholder.svg" alt="Leader">
                                                                <?php endif; ?>
                                                                <?php if (trim($r['foto_wakil']) !== ''): ?>
                                                                    <img class="calon-img" src="<?php echo htmlspecialchars($r['foto_wakil']); ?>" onerror="this.src='../booth/candidates/candidate-placeholder.svg'" alt="Running Mate">
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <?php echo htmlspecialchars($r['nama_ketua']); ?>
                                                            <div class="mt-2">
                                                                <?php if (trim($r['foto_ketua']) !== ''): ?>
                                                                    <img class="calon-img" src="<?php echo htmlspecialchars($r['foto_ketua']); ?>" onerror="this.src='../booth/candidates/candidate-placeholder.svg'" alt="Candidate">
                                                                <?php else: ?>
                                                                    <img class="calon-img" src="../booth/candidates/candidate-placeholder.svg" alt="Candidate">
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><strong><?php echo (int)$r['count']; ?></strong></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4 back-desktop">
                    <div class="col-12">
                        <a href="index.php" role="button" class="btn btn-warning btn-lg">
                            <i class="fa fa-home"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="back-mobile">
                    <a href="index.php" role="button" class="btn btn-warning btn-lg btn-block">
                        <i class="fa fa-home"></i> Kembali
                    </a>
                </div>

            </section>
        </div>
    </div>
</div>

<script src="../../js/vendors.min.js"></script>
<script src="../../js/jquery.smartmenus.js"></script>
<script src="../../js/menus.js"></script>
<script src="../../js/template.js"></script>
</body>
</html>
