<?php
    // SET LOG FUNCTION
    function setLog($description,$type){
        include 'inc/connect.php';
        $log_date = Date("Y-m-d H:i:s");
        $log_user_id = $_SESSION['user_id'];
        $log_text = $description;
        $log_type = $type;
        $stmt = $connect->prepare("INSERT INTO log_actions(log_type,log_user_id,log_time,log_description) VALUES (:ztype,:zuser, :ztime, :ztext)");
        $stmt->execute(array(
            "ztype" => $log_type,
            "zuser" => $log_user_id,
            "ztime" => $log_date,
            "ztext" => $log_text
        ));
    }

?>