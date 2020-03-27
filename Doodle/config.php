<?php 
ob_start(); //output buffering: saves the o/p of any data until the end
try{
	/*created PDO object to connect database "doodle",from localhost, username as root and passwd is none*/
	$con = new PDO("mysql:dbname=doodle;host=localhost", "root", "");// here con is our connection var to our database
	/* give error(if any) as warnings, so that our page keeps on executing*/
	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
}
catch(PDOException $e){
	echo "Connection Failed: ".$e->getMessage();
}
?>
