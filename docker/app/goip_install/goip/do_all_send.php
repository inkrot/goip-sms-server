<?php
require_once("session.php");
if($_SESSION['goip_permissions'] > 1)	
	die("需要admin权限！");	
define("OK", true);
require_once("global.php");
require_once('inc/conn.inc.php');


      function do_cron($db,$port)
        {
		if(!$port) $port=44444;
		$flag=0;
		/* 此是最新计划， 唤醒服务进程*/
		if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
			echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
			exit;
		}
		if (socket_sendto($socket,"CRON", 4, 0, "127.0.0.1", $port)===false)
			echo ("sendto error:". socket_strerror($socket));
		for($i=0;$i<3;$i++){
			$read=array($socket);
			$err=socket_select($read, $write = NULL, $except = NULL, 5);
			if($err>0){
				if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port))==false){
					//echo("recvform error".socket_strerror($ret)."<br>");
					continue;
				}
				else{
					if($buf=="OK"){
						$flag=1;
						break;
					}
				}
			}
		}//for
		if($flag)
			echo "已加入";
		else
			echo "已加入,但goipcron进程未响应，请检查该进程";
	}
if(!get_magic_quotes_gpc()){
        $_REQUEST[USERNAME]=addslashes($_REQUEST[USERNAME]);
        $_REQUEST[PASSWORD]=addslashes($_REQUEST[PASSWORD]);
}

if(!isset($_SESSION['goip_username'])){
        //echo "SELECT id FROM user WHERE username='".$_REQUEST[USERNAME]."' and password='".md5($_REQUEST[PASSWORD])."'";
        $rs=$db->fetch_array($db->query("SELECT id FROM user WHERE username='".$_REQUEST[USERNAME]."' and password='".md5($_REQUEST[PASSWORD])."'"));

        if(empty($rs[0])){
                require_once ('login.php');
                exit;
        }
        $userid=$rs[0];
}
else $userid=$_SESSION[goip_userid];

$query=$db->query("SET NAMES 'utf8'");
$query=$db->query("SELECT all_send_num,all_send_msg FROM auto WHERE 1 ");
$rs=$db->fetch_array($query);
$all_send_num=$rs['all_send_num'];
$all_send_msg =$rs['all_send_msg'];
//echo $all_send_num.$all_send_msg;
if(!$all_send_num || !$all_send_msg)
	die("number or message error!");
$nowtime=time();
$db->query("INSERT INTO message (type,msg,userid,crontime,tel) VALUES (6, '$all_send_msg',$userid, $nowtime,'$all_send_num')");
do_cron($db,$port);
WriteSuccessMsg("<br><li>保存成功</li>","all_send.php");	
?>

