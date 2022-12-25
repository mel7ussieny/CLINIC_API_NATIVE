<?php
    include '../headers.php';
    session_start();
    $_POST = json_decode(file_get_contents("php://input"),true);    
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'addReservation'){
        
        include '../inc/connect.php';
        $id = $_POST['id'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $note = $_POST['note'];
        $price = $_POST['price'];
        $paid = (int)$_POST['isPaid'];
        $rolesId = $_SESSION['user_id'];
        try{
            $stmt = $connect->prepare("INSERT INTO reservations(patient_id,reg_date,res_date,res_time,res_note,res_price,res_paid,roles_id,res_paid_date) VALUE (:zid,:regdate,:zdate, :ztime, :znote, :zprice, :zpaid, :roles, :paiddate)");
            $stmt->execute(array(
                "zid"       => $id,
                "regdate"   => date("Y-m-d h:i:s"),
                "zdate"     => $date,
                "ztime"     => $time,
                "znote"     => $note,
                "zprice"    => $price,
                "zpaid"     => $paid,
                "roles"     => $rolesId,
                "paiddate"  => date("Y-m-d")
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
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['required']) && $_GET['required'] == 'getReservations'){
        include '../inc/connect.php';
        
        $id = $_GET['id'];
        try{
            $stmt = $connect->prepare("SELECT reservations.id as resId,reservations.patient_id,reservations.res_online, reservations.res_type,reservations.res_status,reservations.res_price, reservations.reg_date, reservations.res_paid, reservations.res_date, reservations.res_time, patients.id, patients.firstname, patients.lastname, patients.phones 
            FROM reservations 
            INNER JOIN patients 
            ON patients.id = reservations.patient_id  
            WHERE reservations.patient_id = ? AND reservations.res_status = 1  ORDER BY reg_date DESC
            ");
            $stmt->execute(array($id));
            $rows = $stmt->fetchAll();
            $reservations = array();
            if(isset($rows[0]['phones'])){
                $phones = implode(" - ",explode(",",$rows[0]["phones"]));
            }
            
            // print_r($rows);
            foreach($rows as $key => $value){
                $paid = $rows[$key]['res_paid'] ? 'Paid' : 'Unpaid';
                $data = array(
                    "id" => $rows[$key]['resId'],
                    "name" => $rows[$key]['firstname'] . ' ' . $rows[$key]['lastname'],
                    "phones" => $phones,
                    "appointment" => $rows[$key]['res_date'] . ' ' . $rows[$key]['res_time'],
                    "paid" => $paid,
                    "price" => $rows[$key]['res_price'],
                    "reg"   => $rows[$key]['reg_date'],
                    "type"  => $rows[$key]['res_type'],
                    "status" => $rows[$key]['res_status'],
                    "online" => $rows[$key]['res_online']      
                );
                array_push($reservations,$data);
            }
            echo json_encode($reservations);
        }catch(PDOException $e){
          echo $e->getMessage();
        }

    };
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'cancelReservation'){
        include '../inc/connect.php';
        $id = $_POST['id'];

        try{
            $stmt = $connect->prepare("DELETE FROM reservations WHERE id = ?");
            $stmt->execute(array($id));
            setLog("Recervation has been canceled","delete");
            header("HTTP/1.1 200 OK");
        }catch(PDOException $e){
            header("HTTP/1.1 400 Bad request");
        }
    }
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['required']) && $_GET['required'] == 'getDateReservations'){
        include '../inc/connect.php';
        $reservations = array();
        $date = $_GET['date'];
        try{
            $stmt = $connect->prepare("SELECT reservations.patient_id,reservations.res_date,reservations.id,reservations.res_status,reservations.res_price,reservations.res_note,reservations.res_paid,reservations.res_time,patients.firstname,patients.lastname 
            FROM reservations 
            INNER JOIN patients 
            ON reservations.patient_id = patients.id
            WHERE reservations.res_status = ? AND reservations.res_date = ?
            ORDER BY reservations.res_time ASC
            ");
            
            $stmt->execute(array(1,$date));
            $rows = $stmt->fetchAll();
            foreach($rows as $key => $value){
                $data = array(
                    "id"    => $rows[$key]['id'],
                    "name"  => $rows[$key]['firstname'] . ' ' . $rows[$key]['lastname'],
                    "time"  => $rows[$key]['res_time'],
                    "note"  => $rows[$key]['res_note'],
                    "paid"  => (bool)$rows[$key]['res_paid'],
                    "patient" => $rows[$key]['patient_id']
                );
                array_push($reservations,$data);
            }
            echo json_encode($reservations);
            header("HTTP/1.1 200 OK");
        }catch(PDOException $e){
            header("HTTP/1.1 400 Bad request");
        }
    }if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'payNow'){
        include '../inc/connect.php';
        $id = $_POST['id'];
        $rolesId = $_POST['rolesId'];
        $date = date("Y-m-d");
        echo $date;
        try{
            $stmt = $connect->prepare("UPDATE reservations SET res_paid = ?, roles_id = ?,res_paid_date = ? WHERE id = ?");
            $stmt->execute(array(1,$rolesId,$date,$id));
            setLog("New payment has been received","payment");
            header("HTTP/1.1 200 OK");
        }catch(PDOException $e){
            header("HTTP/1.1 400 Bad request");
        }
    };
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'closeSession'){
        include '../inc/connect.php';
        $id = $_POST['id'];
        try{
            $stmt = $connect->prepare("UPDATE reservations SET res_status = 0 WHERE id = ?");
            $stmt->execute(array($id));
            header("HTTP/1.1 200 OK");
        }catch(PDOException $e){
            header("HTTP/1.1 400 Bad request");
        }
    }

?>