<?php
session_start();
define("OK", true);
require_once("global.php");
require_once('../inc/conn.inc.php');

$FoundErr=false;
$username=str_replace("'","",trim($_POST['username']));
$password=str_replace("'","",trim($_POST['password']));

if ($username==''){
	$FoundErr=true;
	$ErrMsg= "<br><li>please input name!</li>";
}
if ($password==''){
	$FoundErr=true;
	$ErrMsg=$ErrMsg."<br><li>please input password!</li>";
}
if($FoundErr!=true){$password=md5($password);
	$query=$db->query("SELECT id,permissions FROM user WHERE username='$username' and password='$password' ");
	$rs=$db->fetch_array($query);
	$adminId=$rs[0];
	if(empty($adminId)){
		$FoundErr=true;
		$ErrMsg=$ErrMsg."<br><li>The name or password error</li>";
	}
	else{
		$query=$db->query("SELECT session_time from system ");
		$rss=$db->fetch_array($query);
		$session_time=$rss[0]*60;

		//if($session_time<300) $session_time=300; 
		setcookie(session_name(), session_id(), time() + $session_time, "/");
                $_SESSION['goip_username'] = $username;
                $_SESSION['goip_userid'] = $adminId;
                $_SESSION['goip_permissions'] = $rs['permissions'];

		switch($rs['permissions']){
			case 0:$s='Super Adminstrator';break;
			case 1:$s='Senior Adminstrator';break;
			case 2:$s='Crowd Adminstrator';break;
			case 3:$s='Group Adminstrator';break;
			case 4:$s='GoIP Operator';break;
			case 5:$s='GoIP Owner';break;
		}
		$_SESSION['goip_jibie'] = $s;
		Header("Location: index.php"); 
	}
}

if($FoundErr==true){
	WriteErrMsg($ErrMsg);
}

?>
