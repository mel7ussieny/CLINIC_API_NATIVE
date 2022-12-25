<?php
    include '../headers.php';
    session_start();
    $_POST = json_decode(file_get_contents("php://input"),true);    
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['required']) && $_POST['required'] == 'withdraw'){
        include '../inc/connect.php';
        $amount = $_POST['amount'];
        $date = date("Y-m-d");
        $detail = $_POST['detail'];
        $userId = $_POST['userId'];
        
        try{
            $stmt = $connect->prepare("INSERT INTO withdraws(roles_id,detail,amount,withdraw_date) VALUES(:zid, :zdetail, :zamount, :zdate)");
            $stmt->execute(array(
                "zid" => $userId,
                "zdetail" => $detail,
                "zamount" => $amount,
                "zdate" => $date
            ));
            setLog("!! New withdrawal request","payment");
        }catch(PDOException $e){    
            $e;
            header("HTTP/1.1 400 Bad request");
        }

    }
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['required']) && $_GET['required'] == 'getRevenue'){
        include '../inc/connect.php';
        $period = $_GET['revenueFor'];
        $day = 0;
        switch($period){
            case 'day':
                $day = 1;
            break;
            case 'week':
                $day = 7;
            break;
            case 'month':
                $day = 30;
            break;
            case 'year':
                $day = 365;
            break;
            default:
            $day = 1;
        
        }
        $profit = 0;
        $revenue = 0;
        $expenses = 0;
        $toDate = date("Y-m-d");
        $fromDate = date("Y-m-d",strtotime($toDate . "-$day day"));
        $totalTransactions = array(
            array(),
            array()
        );
        try{
            $stmt = $connect->prepare("SELECT reservations.res_date,reservations.res_price,roles.firstname AS rolesFirst,roles.lastname AS rolesLast,patients.firstname AS patientFirst,patients.lastname AS patientLast
            FROM reservations
            LEFT JOIN roles
            ON reservations.roles_id = roles.id
            INNER JOIN patients
            ON reservations.patient_id = patients.id
            WHERE reservations.res_paid = 1 AND res_paid_date BETWEEN '".$fromDate."' AND '".$toDate."'
            ");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach($rows as $key => $value){
                $revenue += $rows[$key]['res_price'];
                $data = array(
                    "type" => "income",
                    "patient_name" => $rows[$key]['patientFirst'] . " " . $rows[$key]['patientLast'],
                    "roles_name" => $rows[$key]['rolesFirst'] . " " . $rows[$key]['rolesLast'],
                    "funds" => $rows[$key]['res_price'],
                    "date" => $rows[$key]['res_date']
                );
                array_push($totalTransactions[0],$data);
            }
            header("HTTP/1.1 200 OK");
        }catch(PDOException $e){
            header("HTTP/1.1 400 Bad request");
        }
        try{
            $stmt = $connect->prepare("SELECT withdraws.detail, withdraws.amount, withdraws.withdraw_date, roles.firstname, roles.lastname FROM withdraws
            INNER JOIN roles
            ON withdraws.roles_id = roles.id
            WHERE withdraws.withdraw_date BETWEEN '".$fromDate."' AND '".$toDate."'");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach($rows as $key => $value){
                $expenses += $rows[$key]['amount'];
                $data = array(
                    "type" => "outcome",
                    "detail" => $rows[$key]["detail"],
                    "funds" => $rows[$key]['amount'],
                    "date" => $rows[$key]['withdraw_date'],
                    "roles_name" => $rows[$key]['firstname'] . ' ' . $rows[$key]['lastname']
                );
                array_push($totalTransactions[0],$data);
            }
            header("HTTP/1.1 200 OK");
        }catch(PDOException $e){
            header("HTTP/1.1 400 Bad request");
        }
        $totalTransactions[1]["revenue"] = $revenue;
        $totalTransactions[1]["expenses"] = $expenses;
        $totalTransactions[1]["profit"] = $revenue - $expenses;
        echo json_encode($totalTransactions);
    }

?>