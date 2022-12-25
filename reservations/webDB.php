<?php
$connection = ssh2_connect('server254.web-hosting.com', 21098);
ssh2_auth_password($connection, 'hussjsum', 'FkWzAnmueJjV');
ssh2_tunnel($connection,"localhost:3306","3307");

$dns = "mysql:host=127.0.0.1:3307;dbname=hussjsum_clinc";
$user = "hussjsum_admin";
$pass = "Hoss@123";
$option = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
);

    $connection = null;
    try{
        $connect = new PDO($dns,$user,$pass,$option);
        $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        header("HTTP/1.1 200 connected");
    }catch(PDOException $e){
        header("HTTP/1.1 400 not connected");
        exit;
    }

?>