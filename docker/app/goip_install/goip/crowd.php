<?php
require_once("session.php");
define("OK", true);
require_once("global.php");

if(isset($_GET['action'])) {
	if($_GET['action'] != "modifyself" && $_GET['action'] != "savemodifyself" && $_SESSION['goip_permissions'] > 1 )
		WriteErrMsg("<br><li>需要admin权限!</li>");
	$action=$_GET['action'];	
	if($action=="del")
	{
					$ErrMsg="";
					$Id=$_GET['id'];

					
					if(empty($Id)){
						$num=$_POST['boxs'];
						for($i=0;$i<$num;$i++)
						{	
							if(!empty($_POST["Id$i"])){
							  
								if($Id=="")
									$Id=$_POST["Id$i"];
								else
									$Id=$_POST["Id$i"].",$Id";
							}
						}
					}
					//WriteErrMsg($num."$Id");
					
					if(empty($Id))
						$ErrMsg ='<br><li>Please choose one</li>';
					if($ErrMsg!="")
						WriteErrMsg($ErrMsg);
					else{
						$query=$db->query("DELETE FROM crowd WHERE id IN ($Id)");
						//$query=$db->query("DELETE FROM refcrowd WHERE crowdid IN ($Id)");
						WriteSuccessMsg("<br><li>删除成功</li>","crowd.php");
						
					}
	}
	elseif($action=="add")
	{
		
	}
	elseif($action=="modify")
	{
		$id=$_GET['id'];
		//if($id)
		$rs=$db->fetch_array($db->query("SELECT * FROM crowd where id=$id"));

	}
	elseif($action=="saveadd")
	{
					//WriteErrMsg("'$_POST['name']'");
					$username=$_POST['name'];

					$info=$_POST['info'];
					$ErrMsg="";
					if(empty($username))
						$ErrMsg ='<br><li>请输入名称</li>';
					if($ErrMsg!="")
						WriteErrMsg($ErrMsg);
					else{
					
						$query=$db->query("SELECT id FROM crowd WHERE name='$username' ");
						$rs=$db->fetch_array($query);
						if(empty($rs[0])){
							$query=$db->query("INSERT INTO crowd (name,info) VALUES ('$username','$info')");
							WriteSuccessMsg("<br><li>添加成功</li>","crowd.php");
							/* 还要添加管理员*/
						}
						else{
							$ErrMsg=$ErrMsg."<br><li>群名 [$username]已经存在</li>";
							WriteErrMsg($ErrMsg);
						}
								
					}
	}
	elseif($action=="savemodify")
	{

					$Id=$_POST['Id'];
					$ErrMsg="";

					if($ErrMsg!="")
						WriteErrMsg($ErrMsg);
					else{
						$crs=$db->fetch_array($db->query("SELECT id FROM crowd where id=$Id and name='$_POST[name]'"));					
						$query=$db->query("UPDATE crowd SET name='$_POST[name]',info='$_POST[info]'  WHERE id='$Id'");
						
						WriteSuccessMsg("<br><li>修改成功</li>","crowd.php");
					}
	}
	/* 修改管理员*/
	elseif($action=="admin"){

	$query=$db->query("SELECT count(*) AS count FROM refcrowd where crowdid='$_GET[id]'");
	$row=$db->fetch_array($query);
	$count=$row['count'];
	$numofpage=ceil($count/$perpage);
	$totlepage=$numofpage;
	if(isset($_GET['page'])) {
		$page=$_GET['page'];
	} else {
		$page=1;
	}
	if($numofpage && $page>$numofpage) {
		$page=$numofpage;
	}
	if($page > 1) {
		$start_limit=($page - 1)*$perpage;
	} else{
		$start_limit=0;
		$page=1;
	}
	$fenye=showpage("crowd.php?",$page,$count,$perpage,true,true,"编");
	$query=$db->query("SELECT user.*, crowd.name as crowdname  FROM user,refcrowd,crowd where refcrowd.crowdid='$_GET[id]' and user.id=refcrowd.userid and crowd.id=refcrowd.crowdid ORDER BY refcrowd.id DESC LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		$rsdb[]=$row;
	}
	
	$query=$db->query("SELECT * FROM user where permissions=2 and id not in (select userid from refcrowd where crowdid=$_GET[id] ) ORDER BY id DESC ");
	while($userrow=$db->fetch_array($query)) {
		$userdb[]=$userrow;
	}
		
	}
	elseif($action=="addadmin"){

					$ErrMsg="";
					if(empty($_POST[admin]))
						$ErrMsg ='<br><li>请选择一个管理员</li>';
					if(empty($_GET[id]))
						$ErrMsg ='<br><li>无效的群ID</li>';
					if($ErrMsg!="")
						WriteErrMsg($ErrMsg);
					else{
					
						$query=$db->query("SELECT id FROM refcrowd WHERE crowdid=$_GET[id] and userid=$_POST[admin] ");
						$rs=$db->fetch_array($query);
						if(empty($rs[0])){
							
							$query=$db->query("INSERT INTO refcrowd (crowdid,userid) select crowd.id, user.id from user,crowd where user.id=$_POST[admin] and crowd.id=$_GET[id]");
							
							WriteSuccessMsg("<br><li>Add administrator success</li>","crowd.php?action=admin&id=$_GET[id]");
							/* 还要添加管理员*/
						}
						else{
							$ErrMsg=$ErrMsg."<br><li>已经是群管理员</li>";
							WriteErrMsg($ErrMsg);
						}								
					}		
	}
	elseif($action=="deladmin"){
				
					$ErrMsg="";
					$Id=$_GET['id'];

					
					if(empty($Id)){
						$num=$_POST['boxs'];
						for($i=0;$i<$num;$i++)
						{	
							if(!empty($_POST["Id$i"])){
							  
								if($Id=="")
									$Id=$_POST["Id$i"];
								else
									$Id=$_POST["Id$i"].",$Id";
							}
						}
					}
					//WriteErrMsg($num."$Id");
					
					if(empty($Id))
						$ErrMsg ='<br><li>Please choose one</li>';
					if($ErrMsg!="")
						WriteErrMsg($ErrMsg);
					else{
						$query=$db->query("DELETE FROM refcrowd WHERE id IN ($Id)");
						
						WriteSuccessMsg("<br><li>删除成功</li>","crowd.php?action=admin&id=$_GET[crowdid]");
						
					}
	}
	elseif($action=="saveaddadmin"){
		
	}

	else $action="main";
	
}
else $action="main";


if($action=="main" && $_SESSION['goip_permissions'] < 2)	
{
	$query=$db->query("SELECT count(*) AS count FROM crowd");
	$row=$db->fetch_array($query);
	$count=$row['count'];
	$numofpage=ceil($count/$perpage);
	$totlepage=$numofpage;
	if(isset($_GET['page'])) {
		$page=$_GET['page'];
	} else {
		$page=1;
	}
	if($numofpage && $page>$numofpage) {
		$page=$numofpage;
	}
	if($page > 1) {
		$start_limit=($page - 1)*$perpage;
	} else{
		$start_limit=0;
		$page=1;
	}
	$fenye=showpage("crowd.php?",$page,$count,$perpage,true,true,"编");
	$query=$db->query("SELECT * FROM crowd ORDER BY id DESC LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		
		/*找出每个组的管理员*/
		$usersql=$db->query("select username from user,refcrowd where refcrowd.crowdid=$row[id] and user.id=refcrowd.userid order by refcrowd.id desc ");

		$i=0;
		/*最多列出3个*/		
		while($userrow=$db->fetch_array($usersql)){ 
			$i++;
			$row[username].=$userrow[0]." ";
			if($i>5){
				$row[username].="等";
				break;
			}
		}
		$rsdb[]=$row;		
		//while($userrow=$db->fetch_array($query)) {
		//	$rsdb[username]=$userrow[0];
		//}
	}
}

	require_once ('crowd.htm');

?>

