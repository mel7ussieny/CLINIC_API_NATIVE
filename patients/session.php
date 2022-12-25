<?php
include '../headers.php';
session_start();

$_POST = json_decode(file_get_contents("php://input"),true);
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['required']) && $_POST['required'] == 'addSession'){
    include '../inc/connect.php';
    $usrId = $_POST['userId'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $paid = 1;
    try{
        $stmt = $connect->prepare('INSERT INTO patient_session(user_id,session_title,session_date,session_description,paid) VALUES (:zuser, :ztitle ,:zdate, :zdescription, :zpaid)');
        $stmt->execute(array(
            "zuser" => $usrId,
            "ztitle" => $title,
            "zdate" => $date,
            "zdescription" => $description,
            "zpaid" => $paid));
    }catch(PDOException $e){
         $e;
    }

};
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['required']) && $_POST['required'] == 'getPatientSessions'){
    include '../inc/connect.php';
    $array = array();
    $id = $_POST['userId'];

    $stmt = $connect->prepare('SELECT id,session_date,session_title,session_description  FROM patient_session WHERE user_id = ?');
    $stmt->execute(array($id));
    $rows = $stmt->fetchAll();
    foreach($rows as $key => $value){
        $data = array(
            "id" => $rows[$key]['id'],
            "date" => $rows[$key]['session_date'],
            "title" => $rows[$key]['session_title'],
            "description" => $rows[$key]['session_description']
        );
        array_push($array,$data);
    };
    echo json_encode($array);
    
};
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'fullSessionInfo'){
    include '../inc/connect.php';
    $array = array();
    $id = $_POST['sessionId'];

    $stmt = $connect->prepare("SELECT file_name,id from session_files WHERE file_session_id = ?");
    $stmt->execute(array($id));
    $rows = $stmt->fetchAll();
    foreach($rows as $key => $value){
        $data = array(
            "file_name" => $rows[$key]['file_name'],
            "id" => $rows[$key]['id']
        );
        array_push($array,$data);
    }
    echo json_encode($array);
}
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'removeImg'){
    include '../inc/connect.php';
    $id = $_POST['imgId'];
    $file_path = "uploads/" .  $_POST['name'];
    $delete = 0;

        $stmt = $connect->prepare("DELETE FROM session_files WHERE id = ?");
        $stmt->execute(array($id));
        $effect = $stmt->rowCount();
        
        if(file_exists($file_path) && $effect > 0){
            @unlink($file_path);
            $delete = 1;
        }
        return $delete == 1 ? header("HTTP/1.1 200 OK") : header("HTTP/1.1 404 Not Found");
};
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'editSession'){
    include '../inc/connect.php';
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    $stmt = $connect->prepare("UPDATE patient_session SET session_title = ?, session_description = ? WHERE id = ?");
    $stmt->execute(array($title,$description,$id));
    $count = $stmt->rowCount();
    return $count > 0? header("HTTP/1.1 200 OK") : header("HTTP/1.1 400 Bad request");
};
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'deleteSession'){
    include '../inc/connect.php';
    $id = $_POST['id'];
    try{

        $stmt = $connect->prepare("DELETE FROM patient_session WHERE id = ?");
        $stmt->execute(array($id));
        $count = $smt->rowCount();
        if($count > 0){
            return header("HTTP/1.1 200 OK");
        }else{
            return header("HTTP/1.1 400 Bad request");
        }
    }catch(PDOException $e){
        header("HTTP/1.1 403 Bad request"); 
    }

}
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['required']) && $_GET['required'] == 'fullSessions' && isset($_GET{'userId'})){
    include '../inc/connect.php';
    $id = $_GET['userId'];
    $stmt = $connect->prepare("SELECT * FROM patient_session WHERE user_id = ? ORDER BY session_date");
    $stmt->execute(array($id));
    $rows = $stmt->fetchAll();
    $sessions = array();
    foreach($rows as $key => $value){
        $data = array(
            "id" => $rows[$key]['id'],
            "title" => $rows[$key]['session_title'],
            "date" => $rows[$key]['session_date'],
            "description" => $rows[$key]['session_description'],
            "imgs" => array()
        );
        array_push($sessions,$data);
    }
    $stmt = $connect->prepare("SELECT session_files.id,session_files.file_session_id,session_files.file_name,patient_session.user_id
    FROM session_files 
    INNER JOIN patient_session 
    ON patient_session.id = session_files.file_session_id
    WHERE patient_session.user_id = ?");
    $stmt->execute(array($id));
    $rows = $stmt->fetchAll();
    foreach($sessions as $num => $val){
        foreach($rows as $key => $value){
            if($sessions[$num]['id'] == $rows[$key]['file_session_id']){
                $sessions[$num]['imgs'][] = $rows[$key]['file_name'];
            }
        }
    }
    echo json_encode($sessions);
}
?>