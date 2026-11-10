<?php
ini_set("error_reporting",E_ALL & ~E_NOTICE);
ini_set("display_errors",0);



//=======================
require_once __DIR__ . '/../make_conn.php';

$nomor_peserta = $_POST[nim];
$passwd = $_POST[psw];

if ($nomor_peserta=='')
{
    $nomor_peserta = $_GET[nim];
}


$cek_pmb_sql = "SELECT username,nama,passwd,foto 
                FROM t_user 
                WHERE username = '$nomor_peserta' and (test <> '5' or test is NULL )";
$cek_pmb_sql_result = pg_exec($conn,$cek_pmb_sql);
$cek_pmb = pg_fetch_array($cek_pmb_sql_result);
$nama = str_replace("'","`",$cek_pmb[nama]);;



//echo "$cek_pmb_sql|Nomor peserta : $cek_pmb[nomor_ujian]|nama peserta : $cek_pmb[nama_peserta]";
/*
if ($cek_pmb['nama'] == '')
{
    header("Location: ../../index.php");
}
*/
//========================

$jalur = $cek_pmb['id_jalur_pendaftaran'];
$id_pendaftaran = $cek_pmb['id'];
$id_jurusan = $cek_pmb['foto'];
$tes = 'PMW';
$id_soal = $nama;

$ip_stat = $_SERVER['REMOTE_ADDR'];


$sid = md5($nomor_peserta.$tes);

$cek_sid_sql = "SELECT id_session from t_log WHERE id_session = '$sid'";
$cek_sid_result = pg_exec($conn,$cek_sid_sql);
$cek_sid = pg_fetch_array($cek_sid_result);

if ($cek_sik[id_session] == '')
{
    $isi_sid_sql = "INSERT INTO t_log(id_session,nomor_peserta,id_soal,foto,nama,ip_station,tes) 
                   VALUES ('$sid','$nomor_peserta','$id_soal','$id_jurusan','$nama','$ip_stat','$tes')";
    $isi_sid_result = pg_exec($conn,$isi_sid_sql);
}

/*

$_SESSION["nomor_peserta"] = $nomor_peserta;
$_SESSION["id_soal"] = $id_soal;
$_SESSION["nama_peserta"] = $cek_pmb[nama_peserta];
$_SESSION["foto"] = $foto;
$_SESSION["id_soal"] = $id_soal;
*/


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

    <title>CampusBallot Core - Voting Booth</title>

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
                    <ul class="nav navbar-nav">

                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <div class="container-full">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <h3>
                    <b>Bilik Pemilihan</b>
                </h3>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h4 class="box-title"><b>Cara Memilih</b></h4>
                            </div>
                            <div class="box-body text-dark">
                                <form id="cd" action="soal_surat.php" name="cd" method="post">
                                    <br>
                                    <ul>
                                        <li style="font-weight: bold; color: red; font-size: 2rem">BACA DAN PAHAMI!</li><br>
                                        <li style="font-weight: bold; color: red; font-size: 2rem">JANGAN MENINGGALKAN /
                                            REFRESH / PINDAH TAB / BUKA APLIKASI LAIN SELAMA PROSES PEMILIHAN
                                        </li><br>
                                        <li style="font-weight: bold; color: red; font-size: 2rem">Jika tidak ada calon yang
                                            ditampilkan, silakan langsung klik
                                            Submit untuk ke tahap berikutnya. JANGAN PENCET KEMBALI / BACK / REFRESH!
                                        </li><br>
                                        <li style="font-weight: bold; color: red">Pastikan anda sudah menggunakan Mode
                                            Penyamaran / Incognito Mode
                                        </li><br>
                                        <li>Untuk memilih, klik bulatan disebelah kiri calon, klik Submit untuk menyimpan hasil</li><br>
                                        <li>Pilihan akan otomatis berpindah ke pilihan berikutnya</li><br>
                                        <li style="font-weight: bold; color: red; font-size: 1rem">Setelah pilihan berpindah, anda tidak dapat kembali ke pilihan sebelumnya</li><br>
                                        <li style="font-weight: bold; color: red; font-size: 1rem">Jika tidak ada calon yang ditampilkan, silakan langsung klik Submit untuk ke Bilik Pemilihan selanjutnya</li><br>
                                        <li>Apabila tidak ingin memilih salah satu calon (golput), silakan langsung klik Submit untuk ke Bilik Pemilihan selanjutnya</li><br>
                                    </ul>
                                    Klik Tombol di Bawah Ini untuk Mulai Memilih <br><b>(Selesaikan dulu proses pemilihan, jangan sambil buka aplikasi lain!)<br><br>

                                    <input type="hidden" name="sid" value="<?php echo "$sid"; ?>">
                                    <input type="submit" name="Submit" class="btn btn-primary" value="Mulai">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->

        </div>
    </div>
    <!-- /.content-wrapper -->

</div>
<!-- ./wrapper -->

<script language="javascript">
    // Created by: Neill Broderick :: http://www.bespoke-software-solutions.co.uk/downloads/downjs.php

    var mins
    var secs;

    function cd() {
        mins = 1 * m("0"); // change minutes here
        secs = 0 + s(":10"); // change seconds here (always add an additional second to your total)
        redo();
    }

    function m(obj) {
        for(var i = 0; i < obj.length; i++) {
            if(obj.substring(i, i + 1) == ":")
                break;
        }
        return(obj.substring(0, i));
    }

    function s(obj) {
        for(var i = 0; i < obj.length; i++) {
            if(obj.substring(i, i + 1) == ":")
                break;
        }
        return(obj.substring(i + 1, obj.length));
    }

    function dis(mins,secs) {
        var disp;
        if(mins <= 9) {
            disp = " 0";
        } else {
            disp = " ";
        }
        disp += mins + ":";
        if(secs <= 9) {
            disp += "0" + secs;
        } else {
            disp += secs;
        }
        return(disp);
    }

    function redo() {
        secs--;
        if(secs == -1) {
            secs = 59;
            mins--;
        }
        document.cd.disp.value = dis(mins,secs); // setup additional displays here.

        if((mins == 0) && (secs == 0)) {
            document.getElementById('cd').submit();
        } else {
            cd = setTimeout("redo()",1000);
        }


    }

    function init() {
        cd();
    }
    window.onload = init;
</script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

<!-- js -->
<script src="../../js/vendors.min.js"></script>

<!-- Crypto Admin App -->
<script src="../../js/jquery.smartmenus.js"></script>
<script src="../../js/menus.js"></script>
<script src="../../js/template.js"></script>

<!-- Form validator JavaScript -->
<script src="../../js/pages/validation.js"></script>
<script src="../../js/pages/form-validation.js"></script>

</body>
</html>
