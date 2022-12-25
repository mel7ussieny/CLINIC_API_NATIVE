<?php
    include '../headers.php';
	session_start();

    $_POST = json_decode(file_get_contents("php://input"),true);
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['required']) && $_GET['required'] == "getPatients"){
    include "../inc/connect.php";
    $stmt = $connect->prepare("SELECT * FROM patients ORDER BY id DESC");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    
    $array = array();
    foreach ($rows as $key => $value) {
        $phones = explode(",",$rows[$key]["phones"]);
        $disease = explode(",",$rows[$key]["disease"]);
        $data = array(
            "id"=>$rows[$key]["id"],
            "gender"=> $rows[$key]["gender"],
            "firstname" => $rows[$key]["firstname"],
            "lastname"=> $rows[$key]["lastname"],
            "birth" => $rows[$key]["birth"],
            "phones"=> $phones,
            "date" => $rows[$key]["regdate"],
            "disease" => $disease,
            "notes" => $rows[$key]["notes"],
            "status" => $rows[$key]["status"]
    
        );
        if(isset($_GET['page']) && $_GET['page'] == 'reservation'){
            $data = array(
                "id" => $rows[$key]["id"],
                "name" => $rows[$key]["firstname"] . " " . $rows[$key]["lastname"],
                "phones" => implode(' - ',$phones),
                "birth" => $rows[$key]['birth']
            );
        }
    array_push($array,$data);
    }
    echo json_encode($array);
}
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['required']) && $_POST['required'] == "addPatient"){
    include "../inc/connect.php";

    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $phones     = implode(",",$_POST['phones']);
    $disease    =  implode(",",$_POST['disease']);
    $note = $_POST['notes'];
    $birth = $_POST['birth'];
    $regDate = $_POST['regDate'];
    $gender = $_POST['gender'];
    $stmt = $connect->prepare("INSERT INTO 
    patients(firstname, lastname, phones, disease, notes, regdate,birth, gender)
    VALUES (:kfirst, :klast, :kphones, :kdisease, :knote, :kdate,:kbirth, :kgender)
    ");
    $stmt->execute(array(
        "kfirst"    => $firstName,
        "klast"     => $lastName,
        "kphones"   => $phones,
        "kdisease"  => $disease,
        "knote"     => $note,
        "kdate"     => $regDate,
        "kbirth"    => $birth,
        "kgender"   => $gender
    ));
    setLog("New patient has been added","add");

}
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['required']) && $_POST['required'] == "editPatient"){
    include '../inc/connect.php';
    $id = $_POST['id'];
    $firstName = trim(preg_replace('!\s+!', ' ', $_POST['firstName']));
    $lastName = trim(preg_replace('!\s+!', ' ', $_POST['lastName']));
    $phones = implode(',',$_POST['phones']);
    $disease =  implode(',',$_POST['disease']);
    $note = $_POST['notes'];
    $birth = $_POST['birth'];
    $gender = $_POST['gender'];
    try{
        $stmt = $connect->prepare("UPDATE patients SET firstName = ?, lastName = ?, gender = ?, birth = ?, disease = ?,phones = ?, notes = ? WHERE id = ?");
        $stmt->execute(array($firstName, $lastName, $gender, $birth, $disease, $phones, $note,$id));     
        setLog("patient has been edited","add");   
        header("HTTP/1.1 200 OK");
    }catch(PDOException $e){
        header("HTTP/1.1 400 Bad");
    }
    
}
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['required']) && $_POST['required'] == "deletePatient"){
    include '../inc/connect.php';
    $id = $_POST['userId'];

    try{
        $stmt = $connect->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->execute(array($id));
        setLog("patient has been deleted","delete");
        header("HTTP/1.1 200 OK");
    }catch(PDOException $e){
        header("HTTP/1.1 400 Bad");
    }

}
if(isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['required']) && $_GET['required'] == 'getMedicines' && isset($_GET['q'])){
    include '../inc/connect.php';
    $search = '%' . $_GET['q'] . '%';
    
    $medicines = array();
    try{
        $stmt = $connect->prepare("SELECT medicine,medicine_price FROM medicines WHERE medicine LIKE '".$search."'");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach($rows as $key => $value){
            $medicine = array(
                "medicine" => $rows[$key]['medicine'],
                "price" => $rows[$key]['medicine_price']
            );
            array_push($medicines,$medicine);
        }
        echo json_encode($medicines);
    }catch(PDOException $e){
        $e;
    }
}




?>