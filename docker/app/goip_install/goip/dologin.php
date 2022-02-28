<?php
define("OK", true);
session_start();
require_once('inc/conn.inc.php');

$lan=$_POST['lan'];
$FoundErr=false;
$username=str_replace("'","",trim($_POST['username']));
$password=str_replace("'","",trim($_POST['password']));

$nameempty=array(1=>"用户名不能为空!","用戶名不能爲空!","please input name!");
$passwordempty=array(1=>"密码不能为空!","密碼不能爲空!","please input password!");
$error=array(1=>"用户名或密码错误!","用戶名或密碼錯誤!","The name or password error");
$spa=array(1=>"系统管理员","系統管理員","Super Adminstrator");
$sna=array(1=>"高级管理员","高級管理員","Senior Adminstrator");
$cra=array(1=>"群管理员","群管理員","Crowd Adminstrator");
$gra=array(1=>"组管理员","組管理員","Group Adminstrator");
$goa=array(1=>"GoIP操作者","組管理員","GoIP Operator");
$gna=array(1=>"GoIP所有者","組管理員","GoIP Owner");

if ($username==''){
	$FoundErr=true;
	$ErrMsg= "<br><li>".$nameempty[$lan]."</li>";
}
if ($password==''){
	$FoundErr=true;
	$ErrMsg=$ErrMsg."<br><li>".$passwordempty[$lan]."</li>";
}
if($FoundErr!=true){
	$password=md5($password);
	$query=$db->query("SELECT id,permissions FROM user WHERE username='$username' and password='$password' ");
	$rs=$db->fetch_array($query);
	$adminId=$rs[0];
	if(empty($adminId)){
		$FoundErr=true;
		$ErrMsg=$ErrMsg."<br><li>".$error[$lan]."</li>";
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
			case 0:$s=$spa[$lan];break;
			case 1:$s=$sna[$lan];break;
			case 2:$s=$cra[$lan];break;
			case 3:$s=$gra[$lan];break;
                        case 4:$s=$goa[$lan];break;
                        case 5:$s=$gna[$lan];break;
		}
		$_SESSION['goip_jibie'] = $s;
		switch($lan){		
			case 2:
				Header("Location: tw/index.php");
				break;
			case 3:
				Header("Location: en/index.php");
				break;
			default:
				Header("Location: index.php");
				break;
		} 
	}
}

if($FoundErr==true){
	$strErr="<html><head><title>Error Information</title><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" ;
	$strErr=$strErr."<link href='style.css' rel='stylesheet' type='text/css'></head><body>" ;
	$strErr=$strErr."<table cellpadding=2 cellspacing=1 border=0 wIdth=400 class='border' align=center>"; 
	$strErr=$strErr."  <tr align='center'><td height='22' class='title'><strong>Wrong message</strong></td></tr>" ;
	$strErr=$strErr."  <tr><td height='100' class='tdbg' valign='top'><b> Reasons:</b><br>$ErrMsg</td></tr>" ;
	$strErr=$strErr."  <tr align='center'><td class='tdbg'><a href=javascript:history.back();>&lt;&lt; Return</a></td></tr>" ;
	$strErr=$strErr."</table>" ;
	$strErr=$strErr."</body></html>" ;
	echo $strErr;
}

?>
