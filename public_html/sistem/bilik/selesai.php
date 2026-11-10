
<?php
ini_set("error_reporting",E_ALL & ~E_NOTICE);
ini_set("display_errors",0);

require_once __DIR__ . '/../make_conn.php';
//session_start();

//$nomor_peserta = $_SESSION["nomor_peserta"];
//$id_soal = $_SESSION["id_soal"];

//$no_peserta = $_POST['no_peserta'];

$isi_jawaban_sql = "update t_user set test='5' where username = '$nomor_peserta'";
$isi_jawaban_result=pg_exec($conn,$isi_jawaban_sql);


//echo "|$nomor_peserta|";
/*
if ($no_peserta == $nomor_peserta)
{

    $jawaban_peserta = $_POST['radiobutton'];
    $id_soal = $_POST['id_soal'];
    $nomor_soal = $_POST['nomor_soal'];
    //echo "$jawaban_peserta|$id_soal|$nomor_soal";

    $isi_jawaban_sql = "INSERT INTO peserta_jawaban (id_peserta,nomor_soal,id_soal,jawaban,nomor_urutan)
                        VALUES ('$nomor_peserta','$nomor_soal','$id_soal','$jawaban_peserta','$nomor_soal')";
    $isi_jawaban_result=pg_exec($conn,$isi_jawaban_sql);


}



$nomor_sql = "SELECT max(nomor_urutan) as nomor from peserta_jawaban WHERE  id_peserta = '$nomor_peserta'";
$nomor_sql_result=pg_exec($conn,$nomor_sql);


$jum_cek = pg_num_rows($nomor_sql_result);
if ($jum_cek == 0)
{
    $nomor_urut = 1;
}
else
{
    $data_soal=pg_fetch_array($nomor_sql_result);
    $nomor_urut = $data_soal[nomor] + 1;

    $jenis_soal_sql = "SELECT jenis_soal from pertanyaan WHERE urutan = '$nomor_urut'";
    $jenis_soal_result = pg_exec($conn,$jenis_soal_sql);
    $data_jenis_soal = pg_fetch_array($jenis_soal_result);

    if ($data_jenis_soal[jenis_soal]=='1B')
    {
        header('Location:end_of_a.php');
    }
}

*/
?>

<style type="text/css">
    #txt {
        border:none;
        font-family:verdana;
        font-size:16pt;
        font-weight:bold;
        border-right-color:#FFFFFF
    }

    /* Add Zoom Animation */
    .animate {
        -webkit-animation: animatezoom 0.6s;
        animation: animatezoom 0.6s
    }

    @-webkit-keyframes animatezoom {
        from {-webkit-transform: scale(0)}
        to {-webkit-transform: scale(1)}
    }

    @keyframes animatezoom {
        from {transform: scale(0)}
        to {transform: scale(1)}
    }

    @keyframes leftslide {
        from {transform: translate(0px,0px)}
        to {transform: translate(200px,200px)}
    }


    .box {
        border-radius: 5px;
        border: solid thin #5A8DEE;
        width: 50%;
        position: absolute;
        margin-top: -200px;
        margin-left: -400px;
        left: 50%;
        top: 50%;
        animation: animatezoom 0.8s;
        background: #ffffff;

    }

    h1 {
        font-family: Arial,Helvetica,sans-serif;
        color: #5A8DEE;
        text-shadow: bisque 3px 3px 3px;
    }

</style>
<!--
This script downloaded from www.JavaScriptBank.com
    Come to view and download over 2000+ free javascript at www.JavaScriptBank.com
-->


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
<body>
<p align="center">
<form id="cd" action="../../index.php" name="cd" method="post">

    <table width="70%" border="0" cellpadding="5" cellspacing="1">
        <tr>
            <td width="58%"><strong>
                    <!-----
                            <audio autoplay>
                                <source src="audio_nomor/End of Listening.mp3" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                            ---------->
                </strong>
            </td>
            <td width="42%" align="right"><input name="disp" type="hidden" id="txt" value="10:00" size="5" readonly="true" border="0">
                <br>
            </td>
        </tr>
    </table>


    <div class="box">
        <h1 align="center">SELESAI
        </h1>
        <h3 align="center">Tunggu sebentar, Anda akan otomatis dialihkan ke Halaman Utama</h3>
    </div>
</p>
</form>
<!--
This script downloaded from www.JavaScriptBank.com
    Come to view and download over 2000+ free javascript at www.JavaScriptBank.com
-->