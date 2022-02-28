<?php
define("OK", true);
require_once("session.php");
if($_SESSION['goip_permissions'] > 1)	
	die("需要admin权限！");	
require_once("global.php");

if($_GET['action']=="save")
{
	if(operator_owner_forbid()) WriteErrMsg('<br><li>forbidden</li>');
	$sql="update prov set ";
	for($i=1;$i<=$_POST['count'];$i++){
		 //addslashes($_POST["msg$i"]);
		if(!get_magic_quotes_gpc()){
			$prov=addslashes($_POST["prov$i"]);
			$inter=addslashes($_POST["inter$i"]);
			$local=addslashes($_POST["local$i"]);
			$recharge_ok_r=addslashes($_POST["recharge_ok_r$i"]);
		}
		else{ 
			$prov=$_POST["prov$i"];
			$inter=$_POST["inter$i"];
			$local=$_POST["local$i"];
			$recharge_ok_r=$_POST["recharge_ok_r$i"];
		}
		//$sql.=" msg$i='$msg',";
	
		//$sql.=" id=id where username='$_SESSION[username]'";
		$sql="update prov set prov='$prov',inter='$inter',local='$local',recharge_ok_r='$recharge_ok_r' where id=$i";
		$query=$db->query($sql);
	
	}
	WriteSuccessMsg("<br><li>修改服务商成功!</li>","provider.php");
}	
elseif($_GET['action']=="add")
{
	if(operator_owner_forbid()) WriteErrMsg('<br><li>forbidden</li>');
	$db->query("insert into prov set prov='',inter='',local=''");
	WriteSuccessMsg("<br><li>添加服务商成功!</li>","provider.php");
}

	$query=$db->query("SELECT * FROM prov ");
	while($row=$db->fetch_array($query)) {
		$rsdb[]=$row;
	}	


	require_once ('provider.htm');
?>
