<?php
    include '../headers.php';
    $site = "https://bclinic.hussieny.com/api/panel/requests.php";
    $headers = array(
        "Content-Type" => 'Application/json'
    );
    session_start();
    $_POST = json_decode(file_get_contents("php://input"),true);
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'getRequests'){
        // if($_GET['r'] == 'test2'){
        $data = array(
            "required" => $_POST['required']
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$site);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 180); //timeout in seconds
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        $output = curl_exec($ch);
        
        echo $output;
    }
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'removeRequest' && isset($_POST['id'])){
        $id = is_numeric($_POST['id']) ? $_POST['id'] : 0; 
        $data = array(
            "required" => $_POST['required'],
            "id" => $_POST['id']
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$site);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_exec($ch);
    } 
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'acceptRequest' && isset($_POST['id'])){
        
        // REQUIRED IS 'REMOVEREQUEST' => TO DELETE FROM DB IN SERVER
        $id = is_numeric($_POST['id']) ? $_POST['id'] : 0; 
        $data = array(
            "required" => 'removeRequest',
            "id" => $_POST['id']
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$site);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_exec($ch);


        include '../inc/connect.php';

        $firstNameHandle = substr($_POST['name'],0,strpos($_POST['name']," "));
        $firstName = trim(preg_replace('!\s+!', ' ', $firstNameHandle));        

        $lastNameHandle = substr($_POST['name'],strpos($_POST['name']," "));
        $lastName = trim(preg_replace('!\s+!', ' ', $lastNameHandle));
        
        $phone = $_POST['phone'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $type = $_POST['type'];
        $status = 1;
        $price = $_POST['price'];
        $birth = "1990-10-10";
        $regDate = Date("Y-m-d");
        $paid = 0;
        $id = 0;
        $rolesId = $_SESSION['user_id'];
        try{            
            $stmt = $connect->prepare("INSERT INTO 
            patients(firstname, lastname, phones, regdate,birth)
            VALUES (:kfirst, :klast, :kphones, :kdate,:kbirth)
            ");
            $stmt->execute(array(
                "kfirst"    => $firstName,
                "klast"     => $lastName,
                "kphones"   => $phone,
                "kdate"     => $regDate,
                "kbirth"    => $birth,
            ));
            $id = $connect->lastInsertId();
            setLog("New patient has been added","add");
            header("HTTP/1.1 200 OK");
        }catch(PDOException $e){
            header("HTTP/1.1 400 Bad request");
            exit;
        }
        if($id != 0){
            try{
                $stmt = $connect->prepare("INSERT INTO reservations(patient_id,reg_date,res_date,res_time,res_note,res_price,res_paid,roles_id,res_paid_date,res_status,res_online) VALUE (:zid,:regdate,:zdate, :ztime, :znote, :zprice, :zpaid, :roles, :paiddate,:zstatus,:zonline)");
                $stmt->execute(array(
                    "zid"       => $id,
                    "regdate"   => date("Y-m-d h:i:s"),
                    "zdate"     => $date,
                    "ztime"     => $time,
                    "znote"     => $type,
                    "zprice"    => $price,
                    "zpaid"     => $paid,
                    "roles"     => $rolesId,
                    "paiddate"  => date("Y-m-d"),
                    "zstatus"   => $status,
                    "zonline"   => 2
                ));
                setLog("New reservation has been received","reservation");
                if($paid == 1){
                    setLog("New payment has been received","payment");
                }
                header("HTTP/1.1 200 OK");
            }catch(PDOException $e){
                header("HTTP/1.1 400 Bad Request");
    
            }
        }
    }   

?>