<?php
    include '../headers.php';
    session_start();

    $_POST = json_decode(file_get_contents("php://input"),true);
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['required']) && $_POST['required'] == "addNotes"){
        include '../inc/connect.php';
        $rolesId = $_POST['id'];
        $note = $_POST['note'];
        $noteDate = date("Y-m-d");
        $color = $_POST['color'];
        try{        
            $stmt = $connect->prepare("INSERT INTO notes(roles_id,notes_date,note,color) VALUES (:zid, :zdate,:znote, :zcolor)");
            $stmt->execute(array(
                "zid" => $rolesId,
                "zdate" => $noteDate,
                "znote" => $note,
                "zcolor" => $color
            ));
            $lastId = $connect->lastInsertId();
            $data = array(
                "noteId" => $lastId
            );
            echo json_encode($data);
            header("HTTP/1.1 200 OK");
        }catch(PDOException $e){
            header("HTTP/1.1 400 Bad request");
        }
    }
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['required']) && $_GET['required'] == 'getNotes'){
        include '../inc/connect.php';
        $notes = array();
        try{
            $stmt = $connect->prepare("SELECT notes.*, roles.firstname,roles.lastname 
            FROM notes 
            INNER JOIN roles 
            ON notes.roles_id = roles.id ");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach($rows as $key => $value){
                $data = [
                    "noteId" => $rows[$key]['id'],
                    "rolesId" => $rows[$key]['roles_id'],
                    "name" => $rows[$key]['firstname'] . ' ' . $rows[$key]['lastname'],
                    "noteDate" => $rows[$key]['notes_date'],
                    "note" => $rows[$key]['note'],
                    "color" => $rows[$key]['color']
                ];
                array_push($notes,$data);
            }
            echo json_encode($notes);
            header("HTTP/1.1 200 OK");
        }catch(PDOException $e){
            header("HTTP/1.1 400 Bad request");
        }
    }
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'deleteNote'){
        include '../inc/connect.php';
        $id = $_POST['noteId'];
        try{
            $stmt = $connect->prepare("DELETE FROM notes WHERE id = ?");
            $stmt->execute(array($id));
            header("HTTP/1.1 200 OK");
        }catch(PDOException $e){
            header("HTTP 1.1 400 Bad request");
        }
    }

?>