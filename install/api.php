<?php
    include '../headers.php';
    include '../inc/connect.php';
    session_start();
    $_POST = json_decode(file_get_contents("php://input"),true);
    if(!isset($_SESSION['id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'Installation'){
        $specialty  = $_POST['specialty'];  
        $name       = $_POST['name'];
        $token      = $_POST['token'];
        $website    = $_POST['website'];
        $firstName  = $_POST['firstName'];
        $lastName   = $_POST['lastName'];
        $username   = $_POST['username'];
        $password   = sha1($_POST['password']);


        try{
            // DELETE PREVIOUS KEYS
            $stmt_clean = $connect->prepare("DELETE FROM config");
            $stmt_clean->execute();
            // CREATE NEW KEY
            $stmt = $connect->prepare("INSERT INTO config(clinic_name,website,specialization,token) VALUES (:zname,:zwebsite,:zspecialty,:ztoken)");
            $stmt->execute(array(
                "zname" => $name,
                "zwebsite"  => $website,
                "zspecialty" => $specialty,
                "ztoken"    => $token
            ));

            $stmt_check = $connect->prepare("SELECT * FROM roles WHERE user = ?");
            $stmt_check->execute(array($username));
            $stmt_check_count = $stmt_check->rowCount();
            if($stmt_check_count == 0){                
                $stmt_create = $connect->prepare("INSERT INTO roles(admin, firstName, lastName, user, pass, regDate) VALUES (:zadmin, :zfirst, :zlast, :zuser, :zpass, :zreg)");
                $stmt_create->execute(array(
                    "zadmin" => 1,
                    "zfirst" => $firstName,
                    "zlast" => $lastName,
                    "zuser" => $username,
                    "zpass" => $password,
                    "zreg"  => date("Y-m-d")
                ));
                $respond_message = "OK";
            }else{
                $respond_message = "duplicate username";
            }
            header("HTTP/1.1 200 $respond_message");
        }catch(PDOException $e){
            echo $e;
            header("HTTP/1.1 400 bad request");
        }
    }


?>