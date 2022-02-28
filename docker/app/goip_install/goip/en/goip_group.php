<?php
require_once("session.php");
define("OK", true);
require_once("global.php");
if(operator_owner_forbid()) WriteErrMsg('<br><li>forbidden</li>');

if(isset($_GET['action'])) {
	if($_GET['action'] != "modifyself" && $_GET['action'] != "savemodifyself" && $_SESSION['goip_permissions'] > 1 )
		WriteErrMsg("<br><li>Permission denied!</li>");
	$action=$_GET['action'];	
	if($action=="del")
	{
		$ErrMsg="";
		$Id=$_GET['id'];

		if(empty($Id))
			$ErrMsg ='<br><li>Please choose one</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$db->query("DELETE FROM goip_group WHERE id='$Id'");
			$db->query("update goip set group_id='' where group_id='$Id'");
			WriteSuccessMsg("<br><li>Successfully deleted</li>","goip_group.php");
		}
	}
	elseif($action=="add")
	{

	}
	elseif($action=="modify")
	{
		$rs=$db->fetch_array($db->query("SELECT * FROM goip_group where id='$_REQUEST[id]'"));

	}
	elseif($action=="saveadd")
	{
		$username=$_POST['group_name'];

		$info=$_POST['info'];
		$ErrMsg="";
		if(empty($username))
			$ErrMsg ='<br><li>please input the name</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{

			$query=$db->query("SELECT id FROM goip_group WHERE group_name='$username' ");
			$rs=$db->fetch_array($query);
			if(empty($rs[0])){
				$query=$db->query("INSERT INTO goip_group set group_name='$username'");
				WriteSuccessMsg("<br><li>Successfully added</li>","goip_group.php");
			}
			else{
				$ErrMsg=$ErrMsg."<br><li>Group name [$username]Already exists</li>";
				WriteErrMsg($ErrMsg);
			}

		}
	}
	elseif($action=="savemodify")
	{

		$Id=$_POST['Id'];
		if(empty($_POST['group_name'])) $ErrMsg="<br><li>please input the name</li>";

		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$rs=$db->fetch_array($db->query("SELECT id FROM goip_group where id!=$Id and group_name='$_POST[group_name]'"));
			if(!$rs[0]){
				$query=$db->query("UPDATE goip_group SET group_name='$_POST[group_name]'  WHERE id='$Id'");

				WriteSuccessMsg("<br><li>Successfully Modified</li>","goip_group.php");
			}
			else {
				WriteErrMsg("<br><li>Group name [$_POST[group_name]]Already exists</li>");
			}
		}
	}

	else $action="main";

}
else $action="main";


if($action=="main" && $_SESSION['goip_permissions'] < 2)	
{
	$query=$db->query("SELECT * FROM goip_group ORDER BY id");
	while($row=$db->fetch_array($query)) {

		$rsdb[]=$row;		
	}
}

require_once ('goip_group.htm');

?>

