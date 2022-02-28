<?php
	session_start();
	if(!isset($_SESSION['goip_username'])){
		define("OK", true);
		require_once("inc/conn.inc.php");
		if(isset($_GET['lan']))
			$language=$_GET['lan'];
		else {
			$query=$db->query("SELECT * FROM system WHERE 1 ");
			$rs=$db->fetch_array($query);
			$language=$rs['lan'];		
		}
		switch($language){
			case 1:
				require_once ('login.php');
				break;
			case 2:
				//header("Location: tw"); 
				require_once ('tw/login.php');
				//exit;
				break;
			case 3:
				//header("Location: en"); 
				require_once ('en/login.php');
				//exit;
				break;
			default:
				require_once ('login.php');
				break;
		}
		exit;
	}

	function check_permission($per){
		if($_SESSION['goip_permissions'] != $per) die("Permission Denied!");
	}
?>
