<?php
/**
 * Created by PhpStorm.
 * User: Core Engineering Team
 * Date: 16/12/20
 * Time: 06.34
 */
ini_set("error_reporting",E_ALL & ~E_NOTICE);
ini_set("display_errors",0);

require_once __DIR__ . '/../make_conn.php';

$nim = $_POST[nim];
$email = $_POST[email];

if ($nim <> '')
{
    $cari_sql = "select * from  mahasiswa   WHERE nim = '$nim'";
    $cari_result = pg_exec($conn,$cari_sql);
    $hasil_cari = pg_fetch_array($cari_result);



    echo "NIM : $hasil_cari[nim]<br>
            Nama : $hasil_cari[nama]<br>
            Status : $hasil_cari[status]<br>
            IP : $hasil_cari[ip]<br>
            No. HP : $hasil_cari[no_hp]<br>
            Waktu : $hasil_cari[waktu]<br>

";
}

if ($email <> '')

{
    $cari_sql = "select * from  mahasiswa   WHERE email = '$email'";
    $cari_result = pg_exec($conn,$cari_sql);
    $hasil_cari = pg_fetch_array($cari_result);



    echo "NIM : $hasil_cari[nim]<br>
            Nama : $hasil_cari[nama]<br>
            Status : $hasil_cari[status]<br>
            IP : $hasil_cari[ip]<br>
            No. HP : $hasil_cari[no_hp]<br>
            Waktu : $hasil_cari[waktu]<br>
            ";
}