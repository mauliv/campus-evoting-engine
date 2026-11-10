<?php
ini_set("error_reporting", E_ALL & ~E_NOTICE);
ini_set("display_errors", 0);

require_once __DIR__ . '/../make_conn.php';

$nomor_sql = "SELECT d.fakultas,
                     COALESCE(COUNT(m.nim), 0) AS count,
                     d.jml AS dpt
              FROM v_rekap_dpt d
              LEFT JOIN mahasiswa m 
                ON d.fakultas = m.fakultas AND m.status::text = '1'::text
              GROUP BY d.fakultas, d.jml
              ORDER BY count DESC";
$nomor_sql_result = pg_exec($conn, $nomor_sql);

$total = 0;
$total_dpt = 0;
$rows = [];

while ($data = pg_fetch_array($nomor_sql_result)) {
    $count = (int)$data['count'];
    $dpt   = (int)$data['dpt'];
    $pct   = ($dpt > 0) ? round(($count / $dpt) * 100, 2) : 0;

    $rows[] = [
        'fakultas' => $data['fakultas'],
        'count'    => $count,
        'dpt'      => $dpt,
        'pct'      => $pct
    ];

    $total += $count;
    $total_dpt += $dpt;
}

$persentase_total = ($total_dpt > 0) ? round(($total / $total_dpt) * 100, 2) : 0;
$updated_at = date('d M Y, H:i:s') . ' WIB';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="refresh" content="5" />
  <link rel="icon" href="../../images/favicon.ico">

  <title>Live Voter Turnout Telemetry - CampusBallot Core</title>

  <style>
    :root{
      --primary-blue:#1e3a8a;
      --primary-soft:#eff6ff;
      --border:#1d4ed8;
    }
    body{
      margin:0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
      background:#f8fafc;
      color:#000;
    }
    .wrap{
      max-width: 980px;
      margin: 0 auto;
      padding: 18px 14px 28px;
    }
    .card{
      background:#fff;
      border:2px solid var(--primary-blue);
      border-radius:18px;
      box-shadow:0 8px 24px rgba(0,0,0,.08);
      padding:16px 16px 14px;
    }
    h1{
      margin:0 0 6px;
      color:var(--primary-blue);
      font-size: 24px;
      font-weight: 900;
      letter-spacing:.2px;
      line-height:1.25;
    }
    .sub{
      margin:0;
      color:#000;
      font-size:14px;
      line-height:1.5;
    }
    .hint{
      margin-top:10px;
      background: var(--primary-soft);
      border: 1px solid rgba(0,49,83,.35);
      border-radius: 14px;
      padding: 10px 12px;
      font-size: 13px;
      line-height: 1.5;
    }
    .stats{
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      gap:12px;
      margin-top:14px;
    }
    .stat{
      border:2px solid var(--primary-blue);
      border-radius:16px;
      padding:12px 12px;
      background:#fff;
      box-shadow:0 6px 14px rgba(0,0,0,.06);
      min-height:78px;
    }
    .stat .t{
      font-size:12px;
      font-weight:900;
      color:var(--primary-blue);
      text-transform:uppercase;
      letter-spacing:.6px;
    }
    .stat .v{
      margin-top:6px;
      font-size:28px;
      font-weight:900;
      color:#000;
      line-height:1.1;
    }
    .table-wrap{
      margin-top:14px;
      overflow-x:auto;
      -webkit-overflow-scrolling:touch;
    }
    table{
      width:100%;
      border-collapse:collapse;
      min-width: 760px;
      background:#fff;
    }
    th, td{
      border:2px solid var(--border);
      padding:10px 10px;
      font-size:14px;
    }
    th{
      background: var(--primary-blue);
      color:#fff;
      font-weight:900;
      text-transform:uppercase;
      letter-spacing:.5px;
      font-size:12px;
    }
    tbody tr:nth-child(even){
      background: var(--primary-soft);
    }
    td.num{ text-align:center; white-space:nowrap; }
    td.fac{ font-weight:800; }
    tfoot td{
      font-weight:900;
      background:#fff;
    }
    .footnote{
      margin-top:10px;
      font-size:12px;
      color:#333;
    }

    @media (max-width: 720px){
      h1{ font-size:20px; }
      .stats{ grid-template-columns: 1fr; }
      .stat .v{ font-size:26px; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Live Voter Turnout Telemetry<br><span style="font-size: 0.6em; opacity: 0.85; font-weight: normal;">CampusBallot Multi-Faculty Telemetry</span></h1>

      <p class="sub">
        Halaman ini menampilkan <strong>jumlah pemilih yang sudah terekam saat ini</strong>.
        Data diperbarui otomatis setiap <strong>5 detik</strong>.
      </p>

      <div class="hint">
        <strong>Waktu pembaruan:</strong> <?php echo htmlspecialchars($updated_at); ?><br>
        Jika tampilan tabel terpotong di ponsel, geser ke samping (horizontal).
      </div>

      <div class="stats">
        <div class="stat">
          <div class="t">Total Pemilih Saat Ini</div>
          <div class="v"><?php echo number_format($total, 0, ',', '.'); ?></div>
        </div>
        <div class="stat">
          <div class="t">Total DPT</div>
          <div class="v"><?php echo number_format($total_dpt, 0, ',', '.'); ?></div>
        </div>
        <div class="stat">
          <div class="t">Persentase Partisipasi</div>
          <div class="v"><?php echo number_format($persentase_total, 2, ',', '.'); ?>%</div>
        </div>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:60px;">No</th>
              <th>Fakultas</th>
              <th style="width:160px;">Jumlah Pemilih</th>
              <th style="width:130px;">Total DPT</th>
              <th style="width:130px;">Persentase</th>
            </tr>
          </thead>
          <tbody>
          <?php $no=1; foreach($rows as $r): ?>
            <tr>
              <td class="num"><?php echo $no++; ?></td>
              <td class="fac"><?php echo htmlspecialchars($r['fakultas']); ?></td>
              <td class="num"><?php echo number_format($r['count'], 0, ',', '.'); ?></td>
              <td class="num"><?php echo number_format($r['dpt'], 0, ',', '.'); ?></td>
              <td class="num"><?php echo number_format($r['pct'], 2, ',', '.'); ?>%</td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2" class="num">Total</td>
              <td class="num"><?php echo number_format($total, 0, ',', '.'); ?></td>
              <td class="num"><?php echo number_format($total_dpt, 0, ',', '.'); ?></td>
              <td class="num"><?php echo number_format($persentase_total, 2, ',', '.'); ?>%</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="footnote">
        Catatan: angka partisipasi dihitung dari <strong>pemilih terekam saat ini</strong> dibandingkan total DPT.
      </div>
    </div>
  </div>
</body>
</html>
