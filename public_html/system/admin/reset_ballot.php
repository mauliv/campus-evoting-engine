<?php
/**
 * Created by PhpStorm.
 * User: Core Engineering Team
 * Date: 16/12/20
 * Time: 06.34
 */
ini_set("error_reporting",E_ALL & ~E_NOTICE);
ini_set("display_errors",0);

require_once __DIR__ . '/../db_connect.php';

$nim = $_POST[nim];


if ($nim <> '')
{

    $cari_mhs = "SELECT nim,nama,token from mahasiswa where nim = '$nim'";
    $cari_mhs_result = pg_exec($conn,$cari_mhs);
    $data_mhs = pg_fetch_array($cari_mhs_result);

    echo "$cari_mhs<br>";

    $token = $data_mhs[token];

    $hapus_jawaban_token = "delete from peserta_jawaban where id_peserta = '$token'";
    $hapus_jawaban_result = pg_exec($conn,$hapus_jawaban_token);


    echo "$hapus_jawaban_token<br>";

    $reset_sql = "update mahasiswa  
                  set status = NULL, 
                  ip = NULL,
                  no_hp = NULL ,
                  token = NULL ,
                  waktu = NULL 
                  WHERE nim = '$nim'";
    $reset_result = pg_exec($conn,$reset_sql);

    echo "$reset_sql<br>";

    echo "Status mahasiswa di reset<br> <br><a href='form_resetXX.html'>Kembali</a> ";

}


