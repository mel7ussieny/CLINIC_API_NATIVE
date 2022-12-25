<?php
session_start();
include '../headers.php';
if(isset($_SESSION['user_id'])){
    header("HTTP/1.1 200 OK");
}else{
    header("HTTP/1.1 400 Bad request");
}

?>