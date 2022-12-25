<?php

	
	$dns	= "mysql:host=localhost;dbname=clinc";
	$user	= "root";
	$pass	= "";
	$option = array(
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
	);

	try{
		$connect = new PDO($dns,$user,$pass,$option);
		$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch(PDOExcetption $e){
		echo $e->getMessage();
	}

	$stmt = $connect->prepare("SELECT website from config LIMIT 1");
    $stmt->execute();
    $data = $stmt->fetch();
    $GLOBALS['site'] =  $data['website'];

?>