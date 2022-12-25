<?php
    session_start();
    include '../headers.php';
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['required']) && $_GET['required'] == 'getOverView'){
        include '../inc/connect.php';
        $overview = array(
            "notifications" => array(),
            "analysis" => array()
        );
        try{
            $stmt = $connect->prepare("SELECT log_type,log_actions.log_time,log_actions.log_description,CONCAT(roles.firstname,' ', roles.lastname) AS roles_name FROM log_actions
            INNER JOIN roles
            ON 
            log_actions.log_user_id = roles.id
            ORDER BY log_actions.id DESC
            LIMIT 10");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $notifications = array();
            foreach($rows as $key => $value){
                $data = array(
                    "log_type"          => $rows[$key]['log_type'],
                    "log_time"          => $rows[$key]['log_time'],
                    "log_description"   => $rows[$key]['log_description'],
                    "roles_name"        => $rows[$key]['roles_name']
                );
                $overview["notifications"][$key] = $data;
            }
            $newDate = Date("Y-m-d");
            $oldDate = Date("Y-m-d", strtotime($newDate . '-1 day'));
            $stmt = $connect->prepare("
            SELECT COUNT(id) FROM reservations 
            UNION ALL
            SELECT COUNT(id) FROM reservations WHERE reg_date BETWEEN '".$oldDate."' AND '".$newDate."' 
            UNION ALL
            SELECT COUNT(id) FROM patients
            UNION ALL
            SELECT COUNT(id) FROM patients WHERE regdate BETWEEN '".$oldDate."' AND '".$newDate."' 
            UNION ALL
            SELECT COUNT(id) FROM roles
            ");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach($rows as $key => $value){
                $overview["analysis"][$key] = $rows[$key][0];    
            }
            echo json_encode($overview);
        }catch(PDOException $e){
            $e;
        }
    }
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['required']) && $_GET['required'] == 'getStatus'){
        include '../inc/connect.php';
        
        function internetStatus(){
            $connection = @fsockopen("www.google.com",80);
            if($connection){
                $connected = 1;
            }else{
                $connected = 0;
            }
            
            return $connected ;
        }

        // Database Connection
        

        $connections = array(
            "internet" => false,
            "database" => false
        );

        function DbStatus(){


            $headers = [
                "Content-Type: applcation/json"
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$GLOBALS['site']."/api/info/api.php?required=getStatus");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
            // curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
            $output = curl_exec($ch);
            // sleep(10);
            return $output;
        }

        
        if(DbStatus() == 1){
            $connections["database"] = true;
        }
        if(internetStatus() == 1){
            $connections["internet"] = true;
        }

        echo json_encode($connections,true);
    }

    
?>