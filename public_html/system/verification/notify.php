<?php
/**
 * Created by PhpStorm.
 * User: Core Engineering Team
 * Date: 12/11/20
 * Time: 19.21
 */
require_once __DIR__ . '/../db_connect.php';
error_reporting(0);
ini_set("display_errors",0);
error_reporting (E_ALL & ~E_NOTICE);

echo "WA BLAST";

$cek_wa_blast_sql = "SELECT nom,hp,pesan 
                FROM wa_blast
                WHERE status is NULL";
$cek_wa_blast_sql_result = pg_exec($conn,$cek_wa_blast_sql);

//echo "SQL : $cek_wa_blast_sql<br>";

while ($data=pg_fetch_array($cek_wa_blast_sql_result))
{
    $nomor_hp = $data[hp];
    $username = $data[pesan];
    $nom = $data[nom];


    $curl = curl_init();
    $token = "4NZ9vP704MgXWc9bDpnlHHoQLtRkZN3CjL1g7alqUtFcAU3hJJCtZIL7Q66HoM0q";
    $data = [
        'phone' => "$nomor_hp",
        'message' => ''.$username,
    ];

    curl_setopt($curl, CURLOPT_HTTPHEADER,
        array(
            "Authorization: $token",
        )
    );
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_URL, "https://teras.wablas.com/api/send-message");
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    $result = curl_exec($curl);
    curl_close($curl);

    $log_hasil = print_r($result,true);
    $waktu = date("Y-m-d h:i:sa");

    $update_sql = " UPDATE wa_blast 
                    SET 
                      respon_sistem = '$log_hasil',
                      status = '1', 
                      waktu = '$waktu'
                    WHERE nom = '$nom'  
                    ";
    $update = pg_exec($conn,$update_sql);
    echo "<pre>WA : $result[message]";
    print_r($result);


}
;