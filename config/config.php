<?php
    include '../headers.php';
    session_start();
    $_POST = json_decode(file_get_contents("php://input"),true);
    if(isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1 ){
        if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['required']) && $_GET['required'] == 'getUsers' ){
            include '../inc/connect.php';
            $stmt = $connect->prepare("SELECT id,admin,firstName,lastName,user,regdate FROM roles");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $users = array();
            foreach($rows as $key => $value){
                $isAdmin = $rows[$key]['admin'] ? 'Admin' : 'User';
                $data = array(
                    "id" => $rows[$key]['id'],
                    "firstName" => $rows[$key]['firstName'],
                    "lastName" => $rows[$key]['lastName'],
                    "user" => $rows[$key]['user'],
                    "type" => $isAdmin,
                    "date" => $rows[$key]['regdate']
                );
                array_push($users,$data);
            }
            echo json_encode($users);
        }
    };
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1 && isset($_POST['required']) && $_POST['required'] == 'editUser'){
        include '../inc/connect.php';
        $id = $_POST['id'];
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $user = $_POST['user'];
        $passowrd = sha1($_POST['password']);
        $type = strtolower($_POST['type']) == 'admin' ? 1 : 0;
        
        try{
            $stmt = $connect->prepare("UPDATE roles SET admin = ?, firstName = ?, lastName = ?, user = ?, pass = ? WHERE id = ?");
            $stmt->execute(array($type,$firstName,$lastName,$user,$passowrd,$id));
            return header("HTTP/1.1 200 OK");
        }catch(PDOException $e){
            return header("HTTP/1.1 400 Bad request");
        }

    }
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1 && isset($_POST['required']) && $_POST['required'] =='addUser'){
        include '../inc/connect.php';
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $user = $_POST['user'];
        $passowrd = sha1($_POST['password']);
        $type = strtolower($_POST['type']) == 'admin' ? 1 : 0;

        try{
            $stmt = $connect->prepare("INSERT INTO roles(admin, firstName, lastName, user, pass, regDate) VALUES (:zadmin, :zfirst, :zlast, :zuser, :zpass, :zreg)");
            $stmt->execute(array(
                "zadmin" => $type,
                "zfirst" => $firstName,
                "zlast" => $lastName,
                "zuser" => $user,
                "zpass" => $passowrd,
                "zreg"  => date("Y-m-d")
            ));
            return header("HTTP/1.1 200 OK");
        }catch(PDOException $e){
            return header("HTTP/1.1 400 Bad request");
        }
    };

    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1 && isset($_GET['required']) && $_GET['required'] == 'deleteUser'){
        include '../inc/connect.php';
        $id = $_GET['userId'];
        try{
            $stmt = $connect->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->execute(array($id));
            return header("HTTP/1.1 200 OK");
        }catch(PDOException $e){
            return header("HTTP/1.1 400 Bad request");
        };
    }
?>