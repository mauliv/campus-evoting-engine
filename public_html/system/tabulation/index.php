<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../images/favicon.ico">

    <title>Official Election Tabulation - CampusBallot Core</title>

    <!-- Bootstrap 4.5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

    <!-- theme style -->
    <link rel="stylesheet" href="../../css/horizontal-menu.css">
    <link rel="stylesheet" href="../../css/style.css">

    <!-- Admin skins -->
    <link rel="stylesheet" href="../../css/skin_color.css">
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
                <h3>Official Plenary Tabulation</h3>
            </div>

            <section class="content">
                <div class="row">
                    <div class="col-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h2 class="text-center">Official Candidate Results & Ballot Tabulation</h2>
                            </div>

                            <div class="box-body text-dark text-center">

                                <!-- ===================== UNIVERSITAS (nomor_soal 1 & 2) ===================== -->
                                <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#collapseUniv" aria-expanded="false" aria-controls="collapseUniv">
                                    Universitas
                                </button>
                                <div class="collapse multi-collapse" id="collapseUniv">
                                    <div class="card card-body">
                                        <a href="results.php?kode_fak=0&nomor_soal=2" class="btn btn-info" role="button">BEM KM UNY</a>
                                        <a href="results.php?kode_fak=6&nomor_soal=1" class="btn btn-info" role="button">DPM UNIVERSITAS DAPIL FIKK</a>
                                    </div>
                                </div><br>

                                <!-- ===================== FIP (1xxx / 1xx) ===================== -->
                                <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#collapseFac1" aria-expanded="false" aria-controls="collapseFac1">
                                    Fakultas Ilmu Pendidikan
                                </button>
                                <div class="collapse multi-collapse" id="collapseFac1">
                                    <div class="card card-body">
                                        <a href="results.php?kode_fak=1&nomor_soal=4" class="btn btn-info" role="button">BEM FIP</a>
                                        <a href="results.php?kode_fak=1&nomor_soal=3" class="btn btn-info" role="button">DPM FIP</a>

                                        <a href="results.php?kode_fak=101&nomor_soal=5" class="btn btn-info" role="button">HIMA Administrasi Pendidikan/MP (KOSONG)</a>
                                        <a href="results.php?kode_fak=102&nomor_soal=5" class="btn btn-info" role="button">HIMA Pendidikan Non Formal/Luar Sekolah</a>
                                        <a href="results.php?kode_fak=103&nomor_soal=5" class="btn btn-info" role="button">HIMA Pendidikan Luar Biasa</a>
                                        <a href="results.php?kode_fak=105&nomor_soal=5" class="btn btn-info" role="button">HIMA Teknologi Pendidikan</a>
                                        <a href="results.php?kode_fak=110&nomor_soal=5" class="btn btn-info" role="button">HIMA Kebijakan Pendidikan</a>
                                        <a href="results.php?kode_fak=111&nomor_soal=5" class="btn btn-info" role="button">HIMA PG PAUD</a>
                                        <a href="results.php?kode_fak=113&nomor_soal=5" class="btn btn-info" role="button">HIMA Psikologi Pendidikan dan Bimbingan/BK</a>
                                        <a href="results.php?kode_fak=115&nomor_soal=5" class="btn btn-info" role="button">HIMA Pendidikan Guru Sekolah Dasar</a>

                                    </div>
                                </div><br>

                                <!-- ===================== FBSB (2xxx / 2xx) ===================== -->
                                <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#collapseFac2" aria-expanded="false" aria-controls="collapseFac2">
                                    Fakultas Bahasa, Seni, dan Budaya
                                </button>
                                <div class="collapse multi-collapse" id="collapseFac2">
                                    <div class="card card-body">
                                        <a href="results.php?kode_fak=2&nomor_soal=4" class="btn btn-info" role="button">BEM FBSB</a>
                                        <a href="results.php?kode_fak=202&nomor_soal=3" class="btn btn-info" role="button">DPM FBSB Dapil PBInggris</a>
                                        <a href="results.php?kode_fak=204&nomor_soal=3" class="btn btn-info" role="button">DPM FBSB Dapil PBPerancis</a>
                                        <a href="results.php?kode_fak=210&nomor_soal=3" class="btn btn-info" role="button">DPM FBSB Dapil Sasindo</a>

                                        <a href="results.php?kode_fak=201&nomor_soal=5" class="btn btn-info" role="button">HIMA Pendidikan Bahasa dan Sastra Indonesia</a>
                                        <a href="results.php?kode_fak=202&nomor_soal=5" class="btn btn-info" role="button">HIMA EDSA</a>
                                        <a href="results.php?kode_fak=203&nomor_soal=5" class="btn btn-info" role="button">HIMA Jerman BDS</a>
                                        <a href="results.php?kode_fak=204&nomor_soal=5" class="btn btn-info" role="button">HIMA Perancis</a>
                                        <a href="results.php?kode_fak=205&nomor_soal=5" class="btn btn-info" role="button">HIMA Jawa/Pend. Bahasa Daerah</a>

                                    </div>
                                </div><br>

                                <!-- ===================== FMIPA (3xxx / 3xx) ===================== -->
                                <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#collapseFac3" aria-expanded="false" aria-controls="collapseFac3">
                                    Fakultas Matematika dan IPA
                                </button>
                                <div class="collapse multi-collapse" id="collapseFac3">
                                    <div class="card card-body">
                                        <a href="results.php?kode_fak=3&nomor_soal=4" class="btn btn-info" role="button">BEM FMIPA</a>

                                        <a href="results.php?kode_fak=302&nomor_soal=5" class="btn btn-info" role="button">HIMA Fisika</a>
                                        <a href="results.php?kode_fak=303&nomor_soal=5" class="btn btn-info" role="button">HIMA Kimia</a>
                                        <a href="results.php?kode_fak=305&nomor_soal=5" class="btn btn-info" role="button">HIMA Matematika</a>
                                        <a href="results.php?kode_fak=308&nomor_soal=5" class="btn btn-info" role="button">HIMA Biologi</a>
                                        <a href="results.php?kode_fak=312&nomor_soal=5" class="btn btn-info" role="button">HIMA IPA</a>

                                    </div>
                                </div><br>

                                <!-- ===================== FISIP (4xxx / 4xx) ===================== -->
                                <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#collapseFac4" aria-expanded="false" aria-controls="collapseFac4">
                                    Fakultas Ilmu Sosial dan Ilmu Politik
                                </button>
                                <div class="collapse multi-collapse" id="collapseFac4">
                                    <div class="card card-body">
                                        <a href="results.php?kode_fak=4&nomor_soal=4" class="btn btn-info" role="button">BEM FISIP</a>
                                        <a href="results.php?kode_fak=401&nomor_soal=3" class="btn btn-info" role="button">DPM FISIP Dapil PPKN</a>
                                        <a href="results.php?kode_fak=405&nomor_soal=3" class="btn btn-info" role="button">DPM FISIP Dapil Pend Geografi</a>
                                        <a href="results.php?kode_fak=419&nomor_soal=3" class="btn btn-info" role="button">DPM FISIP Dapil Ilmu Komunikasi</a>


                                        <a href="results.php?kode_fak=401&nomor_soal=5" class="btn btn-info" role="button">HIMA Pendidikan Kewarganegaraan dan Hukum</a>
                                        <a href="results.php?kode_fak=405&nomor_soal=5" class="btn btn-info" role="button">HIMA Pendidikan Geografi</a>
                                        <a href="results.php?kode_fak=406&nomor_soal=5" class="btn btn-info" role="button">HIMA Pendidikan Sejarah</a>
                                        <a href="results.php?kode_fak=407&nomor_soal=5" class="btn btn-info" role="button">HIMA Pariwisata</a>
                                        <a href="results.php?kode_fak=413&nomor_soal=5" class="btn btn-info" role="button">HIMA Pendidikan Sosiologi</a>
                                        <a href="results.php?kode_fak=416&nomor_soal=5" class="btn btn-info" role="button">HIMA Pendidikan IPS</a>
                                        <a href="results.php?kode_fak=417&nomor_soal=5" class="btn btn-info" role="button">HIMA Administrasi Publik</a>
                                        <a href="results.php?kode_fak=419&nomor_soal=5" class="btn btn-info" role="button">HIMA Ilmu Komunikasi</a>
                                        <a href="results.php?kode_fak=420&nomor_soal=5" class="btn btn-info" role="button">HIMA Ilmu Sejarah</a>
                                        <a href="results.php?kode_fak=421&nomor_soal=5" class="btn btn-info" role="button">HIMA Ilmu Politik</a>

                                    </div>
                                </div><br>

                                <!-- ===================== FT (5xxx / 5xx) ===================== -->
                                <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#collapseFac5" aria-expanded="false" aria-controls="collapseFac5">
                                    Fakultas Teknik
                                </button>
                                <div class="collapse multi-collapse" id="collapseFac5">
                                    <div class="card card-body">
                                        <a href="results.php?kode_fak=5&nomor_soal=4" class="btn btn-info" role="button">BEM FT</a>

                                        <a href="results.php?kode_fak=501&nomor_soal=5" class="btn btn-info" role="button">HME</a>
                                        <a href="results.php?kode_fak=502&nomor_soal=5" class="btn btn-info" role="button">HIMANIKA</a>
                                        <a href="results.php?kode_fak=503&nomor_soal=5" class="btn btn-info" role="button">HIMA MESIN</a>
                                        <a href="results.php?kode_fak=504&nomor_soal=5" class="btn btn-info" role="button">HIMA OTOMOTIF</a>
                                        <a href="results.php?kode_fak=505&nomor_soal=5" class="btn btn-info" role="button">HMTSP</a>
                                        <a href="results.php?kode_fak=506&nomor_soal=5" class="btn btn-info" role="button">HIMAGANA</a>

                                    </div>
                                </div><br>

                                <!-- ===================== FIKK (6xxx / 6xx) ===================== -->
                                <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#collapseFac6" aria-expanded="false" aria-controls="collapseFac6">
                                    Fakultas Ilmu Keolahragaan dan Kesehatan
                                </button>
                                <div class="collapse multi-collapse" id="collapseFac6">
                                    <div class="card card-body">
                                        <a href="results.php?kode_fak=6&nomor_soal=4" class="btn btn-info" role="button">BEM FIKK</a>
                                        <a href="results.php?kode_fak=6&nomor_soal=3" class="btn btn-info" role="button">DPM FIKK</a>

                                        <a href="results.php?kode_fak=601&nomor_soal=5" class="btn btn-info" role="button">HIMA PJKR</a>
                                        <a href="results.php?kode_fak=602&nomor_soal=5" class="btn btn-info" role="button">HIMA PKO</a>
                                        <a href="results.php?kode_fak=603&nomor_soal=5" class="btn btn-info" role="button">HIMA IKOR</a>
                                        <a href="results.php?kode_fak=604&nomor_soal=5" class="btn btn-info" role="button">HIMA PJSD</a>

                                    </div>
                                </div><br>

                                <!-- ===================== FEB (8xxx / 8xx) ===================== -->
                                <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#collapseFac8" aria-expanded="false" aria-controls="collapseFac8">
                                    Fakultas Ekonomi dan Bisnis
                                </button>
                                <div class="collapse multi-collapse" id="collapseFac8">
                                    <div class="card card-body">
                                        <a href="results.php?kode_fak=8&nomor_soal=4" class="btn btn-info" role="button">BEM FEB</a>
                                        <a href="results.php?kode_fak=8&nomor_soal=3" class="btn btn-info" role="button">DPM FEB</a>

                                        <a href="results.php?kode_fak=802&nomor_soal=5" class="btn btn-info" role="button">HIMA Pendidikan Administrasi Perkantoran</a>
                                        <a href="results.php?kode_fak=803&nomor_soal=5" class="btn btn-info" role="button">HIMA Akuntansi</a>
                                        <a href="results.php?kode_fak=804&nomor_soal=5" class="btn btn-info" role="button">HIMA Pendidikan Ekonomi</a>
                                        <a href="results.php?kode_fak=808&nomor_soal=5" class="btn btn-info" role="button">HIMA MANAJEMEN</a>
                                        <a href="results.php?kode_fak=810&nomor_soal=5" class="btn btn-info" role="button">HIMA Pendidikan Akuntansi</a>

                                    </div>
                                </div><br>

                                <!-- ===================== FV (9xxx / 9xx) ===================== -->
                                <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#collapseFac9" aria-expanded="false" aria-controls="collapseFac9">
                                    Fakultas Vokasi
                                </button>
                                <div class="collapse multi-collapse" id="collapseFac9">
                                    <div class="card card-body">
                                        <a href="results.php?kode_fak=9&nomor_soal=4" class="btn btn-info" role="button">BEM FV</a>
                                        <a href="results.php?kode_fak=9&nomor_soal=3" class="btn btn-info" role="button">DPM FV</a>

                                        <a href="results.php?kode_fak=901&nomor_soal=5" class="btn btn-info" role="button">HIMANAGARI WATES</a>
                                        <a href="results.php?kode_fak=902&nomor_soal=5" class="btn btn-info" role="button">HIMA TEKNIK SIPIL VOKASI</a>
                                        <a href="results.php?kode_fak=903&nomor_soal=5" class="btn btn-info" role="button">HIMA ELEKTRO ELEKTRONIKA VOKASI</a>
                                        <a href="results.php?kode_fak=904&nomor_soal=5" class="btn btn-info" role="button">HIMA MESIN VOKASI</a>
                                        <a href="results.php?kode_fak=905&nomor_soal=5" class="btn btn-info" role="button">HIMA OLAHRAGAAN DAN KESEHATAN VOKASI</a>
                                        <a href="results.php?kode_fak=906&nomor_soal=5" class="btn btn-info" role="button">HIMA BISNIS KEUANGAN VOKASI</a>
                                        <a href="results.php?kode_fak=907&nomor_soal=5" class="btn btn-info" role="button">HIMATABONA GUNUNGKIDUL</a>

                                    </div>
                                </div><br>

                                <!-- ===================== FK / FP / FH (4-digit) ===================== -->
                                <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#collapseFac10" aria-expanded="false" aria-controls="collapseFac10">
                                    Fakultas Kedokteran
                                </button>
                                <div class="collapse multi-collapse" id="collapseFac10">
                                    <div class="card card-body">
<p>Verified voter registry: <a href="../telemetry/turnout.php">Live Turnout Telemetry</a></p>
                                        <a href="results.php?kode_fak=1001&nomor_soal=4" class="btn btn-info" role="button">BEM FK</a>
                                    </div>
                                </div><br>

                                <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#collapseFac12" aria-expanded="false" aria-controls="collapseFac12">
                                    Fakultas Hukum
                                </button>
                                <div class="collapse multi-collapse" id="collapseFac12">
                                    <div class="card card-body">
                                        <a href="results.php?kode_fak=1201&nomor_soal=4" class="btn btn-info" role="button">BEM FH</a>
                                        <a href="results.php?kode_fak=1201&nomor_soal=3" class="btn btn-info" role="button">DPM FH</a>
                                    </div>
                                </div><br>

                                <button class="btn btn-primary btn-block" type="button" data-toggle="collapse" data-target="#collapseFac11" aria-expanded="false" aria-controls="collapseFac11">
                                    Fakultas Psikologi
                                </button>
                                <div class="collapse multi-collapse" id="collapseFac11">
                                    <div class="card card-body">
<p>Verified voter registry: <a href="../telemetry/turnout.php">Live Turnout Telemetry</a></p>
                                        <a href="results.php?kode_fak=1101&nomor_soal=5" class="btn btn-info" role="button">HIMA PSIKOLOGI</a>
                                    </div>
                                </div><br>

                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <footer class="main-footer">
        &copy; CampusBallot Core · Open-Source Collegiate Election Engine
    </footer>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
        crossorigin="anonymous"></script>

<script src="../../js/template.js"></script>
</body>
</html>
