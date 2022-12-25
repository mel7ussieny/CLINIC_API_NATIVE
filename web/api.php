<?php
    session_start();
    include '../headers.php';
    include '../inc/connect.php';

    $stmt = $connect->prepare("SELECT website from config LIMIT 1");
    $stmt->execute();
    $data = $stmt->fetch();
    $site =  $GLOBALS['site']."/api/panel/info.php";
    $headers = array(
        "Content-Type" => 'Application/json'
    );
    $_POST = json_decode(file_get_contents("php://input"),true);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$site);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); //timeout in seconds

if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['required']) && $_GET['required'] == 'Info'){
    $data = array(
        "required" => $_GET['required']
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    $output = curl_exec($ch); 
    echo $output;
}
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'deleteOpinion' && $_POST['id']){
    $data = array(
        "required" => $_POST['required'],
        "id" => $_POST['id']
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    $output = curl_exec($ch); 
    echo $output;
}
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'deleteDay' && $_POST['id']){
    $data = array(
        "required" => $_POST['required'],
        "id" => $_POST['id']
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    $output = curl_exec($ch); 
    echo $output;
}
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'addOpinion'){

    $data = array(
        "required" => $_POST['required'],
        "gender" => $_POST['gender'],
        "name" => $_POST['name'],
        "message" => $_POST['message'],
    );

    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    $output = curl_exec($ch); 
    echo $output;
    
}
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'addDay'){
    
    $data = array(
        "required" => $_POST['required'],
        "name" => $_POST['name'],
        "time" => $_POST['time'],
    );

    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    $output = curl_exec($ch); 
    echo $output;

}

?>