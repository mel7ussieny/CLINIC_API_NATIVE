<?php
    include '../headers.php';
	session_start();
    
    // $_POST = json_decode(file_get_contents("php://input"),true);
    if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['session_id'])){
        include '../inc/connect.php';
        $session_id = $_POST['session_id'];
       
        $files = $_FILES;
        
        $valid_extensions = array("jpg","jpeg","png");
        foreach($files as $file){
            $filename = $file['name'];
            
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if(in_array(strtolower($extension),$valid_extensions)){
                print_r($file);
                $avatar_name = rand(0,9999999) . "_" . $filename;
                $stmt = $connect->prepare("INSERT INTO session_files(file_session_id,file_type,file_name,file_date) VALUE (:zsession, :ztype, :zname, :zdate)");
                $stmt->execute(array(
                    'zsession' => $session_id,
                    'ztype' => $extension,
                    'zname' => $avatar_name,
                    'zdate' => date("Y-m-d")
                ));
                move_uploaded_file($file['tmp_name'],"uploads/".$avatar_name);
            }
        }
    }
?>