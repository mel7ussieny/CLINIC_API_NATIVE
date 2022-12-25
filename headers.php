<?php

	// header("Access-Control-Allow-Origin: http://localhost:*");
	// header("Access-Control-Allow-Origin: http://localhost:8080");
	$request_headers        = apache_request_headers();
	$http_origin            = $request_headers['Origin'];
	@header("Access-Control-Allow-Origin: " . $http_origin);
	header("Access-Control-Allow-Methods: POST,GET,OPTIONS");
	header("Access-Control-Allow-Credentials: true");
	header("Access-Control-Allow-Headers: Content-Type");
	
	include "globalFunction.php";
?> 