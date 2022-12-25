<?php
	include '../headers.php';
	session_start();

	$_POST = json_decode(file_get_contents("php://input"),true);
	if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['submit'] == "submit"){
		include '../inc/connect.php';
		
		$user = isset($_POST['user']) ? filter_var($_POST['user'],FILTER_SANITIZE_STRING) : 0;
		$pass = isset($_POST['pass']) ? sha1($_POST['pass']) : 0;
		$stmt = $connect->prepare("SELECT * FROM Roles WHERE user = ? AND pass = ? LIMIT 1");
		$stmt->execute(array($user,$pass));
		$stmtToken = $connect->prepare("SELECT token from config LIMIT 1");
		$stmtToken->execute();
		$row = $stmtToken->fetch();
		$count = $stmt->rowCount();
		$tokenCount = $stmtToken->rowCount();
		if($count > 0 && $tokenCount > 0){
			$row_user = $stmt->fetch();
			$_SESSION['user_id'] = $row_user['id'];
			$_SESSION['is_admin'] = $row_user['admin'] == 1 ? 1 : 0; 
			$stmt = $connect->prepare("SELECT * FROM roles_access WHERE user_id = ?");
			$id = $row_user['id'];
			$stmt->execute(array($id));
			$count = $stmt->rowCount();
			$rows = $stmt->fetchAll();
			$respond = array();
			$pages = array();
		
			foreach ($rows as $key => $value) {
				array_push($pages, $rows[$key]['page']);
			}
			$respond["AuthCode"] = 200;
			$respond['user']['id'] = $row_user['id'];
			$respond['user']['firstName'] = $row_user['firstname'];
			$respond['user']['lastName'] = $row_user['lastname'];
			$respond['user']['isAdmin'] = $_SESSION['is_admin'];
			$respond["user"]['pages'] = $pages;
			$respond['user']['date'] = $row_user['regdate'];		
			$respond['token'] = $row['token'];
			}else{
				$respond["AuthCode"] = 400;
			};
		echo json_encode($respond);
	}
	
?>