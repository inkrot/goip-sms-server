<?php
require_once("session.php");
define("OK", true);
require_once("global.php");

if(operator_owner_forbid()) WriteErrMsg('<br><li>forbidden</li>');
if(isset($_GET['action'])) {
	if($_SESSION['goip_permissions'] > 1 )
		WriteErrMsg("<br><li>Permission denied!</li>");
	$action=$_GET['action'];

	if($action=="recv"){
		if(empty($_GET['id']))
			WriteErrMsg("<br><li>plesae choose a group!</li>");
		$id=$_GET[id];
		$query=$db->query("SELECT count(*) AS count FROM receiver");
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
		$query=$db->query("SELECT receiver.id FROM receiver JOIN (groups join recvgroup on groups.id=recvgroup.groupsid) ON recvgroup.recvid =receiver.id where groups.id=$id order by id");
		$strs0=array();
		$rcount=0;
		while($row=$db->fetch_array($query)) {
			$rcount++;
			$strs0[]=$row['id'];
		}
		//echo $rcount;
		$fenye=showpage2("groups.php?action=recv&id=$id&",$page,$count,$perpage,true,true,"rows","myform","boxs");
		//if(_GET)
		$query=$db->query("(select *,1 as 'in'  from receiver where id in ( SELECT receiver.id FROM receiver JOIN (groups join recvgroup on groups.id=recvgroup.groupsid) ON recvgroup.recvid =receiver.id  where groups.id=$id order by id ))
 union 
(select *,0 from receiver where id not in ( SELECT receiver.id FROM receiver JOIN (groups join recvgroup on groups.id=recvgroup.groupsid) ON recvgroup.recvid =receiver.id  where groups.id=$id order by id )) LIMIT $start_limit,$perpage");

		//$query=$db->query("SELECT receiver.*,groups.id as groupsid,groups.name as groupsname  FROM receiver LEFT JOIN (groups join recvgroup on groups.id=recvgroup.groupsid) ON recvgroup.recvid = receiver.id  ORDER BY groups.id=$id DESC,id DESC LIMIT $start_limit,$perpage");

//select * from receiver where id not in( SELECT receiver.id  FROM receiver LEFT JOIN (groups join recvgroup on groups.id=recvgroup.groupsid) ON recvgroup.recvid = receiver.id  where groups.id=8)  

		
		while($row=$db->fetch_array($query)) {
			
			if($row[in]){			
				$row['yes']="Yes";
			}
			else 
				$row['yes']="No";
			
			//else if($row[groupsid]==$id)
			//if($row['groupsid']==$_GET['id'])
			$rsdb[]=$row;
			$strs[]=$row['id'];
		}	

		//$strs=array();
		$rsdblen=count($rsdb);
		if(isset($_POST['rstr'])){

			$nrcount=0;
			unset($strs0);
			$strs0=array();
			if($_POST['rstr']) $strs0=explode(",",$_POST['rstr']);
			
			$num=$_POST['boxs'];
			for($i=0;$i<$num;$i++)
			{	
				if(!empty($_POST["Id$i"])){
					$strs0[]=$_POST["Id$i"];
				}
			}

			//$nrcount=count($strs0);
			//if(count($strs0)) $strs0=array_unique($strs0);
			//$strs0=&$strs;

		}
		else {
			$nrcount=0;
			$rsdblen=count($rsdb);
			//print_r();
			//print_r()
			//for($i=0;$i<$rsdblen&& $rsdb[$i]['in'];$i++){
				//unset($strs[$i]);
				//$str.=$rsdb[$i]['id'].',';
				////$strs0[]=$rsdb[$i]['id'];
				//$nrcount++;
			//}
		}
/*
		for($i=0;$i<$rsdblen&& $rsdb[$i]['in'];$i++){

			//$nrcount++;
		}
*/	
		//echo $nrcount;
		foreach($strs0 as $v){
			$nrcount++;
			if(in_array($v,$strs)) continue;
			//$nrcount++;
			$str.=$v.",";
			
		}
		//print_r();
		$str=substr($str,0,strlen($str)-1);
		$nametmp=$db->fetch_array($db->query("SELECT name FROM groups where id=$_GET[id]"));
		$groupsname='<font color="#FF0000">'.$nametmp[0].'</font></a>';
	}
	elseif($action=="receivers"){

		if(empty($_GET['id']))
			WriteErrMsg("<br><li>plesae choose a group!</li>");
		$id=$_GET[id];
		$strs=array();
		if($_POST['rstr']) $strs=explode(",",$_POST['rstr']);
		$num=$_POST['boxs'];
		for($i=0;$i<$num;$i++)
		{	
			if(!empty($_POST["Id$i"])){
				$strs[]=$_POST["Id$i"];
			}
		}
		//$strs=array_unique($strs);
		
		$query=$db->query("select receiver.id,recvgroup.id as recvgroupid from receiver,recvgroup where recvgroup.recvid=receiver.id and recvgroup.groupsid=$id");

		while($row=$db->fetch_array($query)) {
			$flag=0;
			foreach($strs as $rkey => $rvalue){
				if($row[0]==$rvalue){
					unset($strs[$rkey]); //??????insert??????
					$flag=1;
					break;
				}
			}
			if(!$flag) $delstrs.=$row[1].","; //???????????????post?????????????????????
		}
		if($delstrs){
			$delstrs=substr($delstrs,0,strlen($delstrs)-1);
			//WriteSuccessMsg("delete from receiver where id in ($delstrs)","groups.php?action=recv&id=$id");
		
			$db->query("delete from recvgroup where id in ($delstrs)");
		}
		if(count($strs)){
			$sql="insert into recvgroup values ";
			foreach($strs as $rkey => $rgid){
				$sql.="(NULL,$id,$rgid),";
			}
			$sql=substr($sql,0,strlen($sql)-1);
			//WriteSuccessMsg($sql,"groups.php?action=recv&id=$id");
			$db->query($sql);
		}
		WriteSuccessMsg("<br><li>Successfully Modified</li>","groups.php?action=recv&id=$id");
		
	}
	elseif($action=="del")
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
						$query=$db->query("DELETE FROM `groups` WHERE id IN ($Id)");
						//$db->query("DELETE FROM refgroup WHERE groupsid IN ($Id)");
						//$db->query("DELETE FROM recvgroup WHERE groupsid IN ($Id)");//??????
						

						WriteSuccessMsg("<br><li>Successfully deleted</li>","groups.php");
						
					}
	}
	elseif($action=="add")
	{
		$query=$db->query("SELECT id,name FROM `crowd` order by id ");
		while($row=$db->fetch_array($query)) {
			$crowdrs[]=$row;
		}		
	}
	elseif($action=="modify")
	{
		$id=$_GET['id'];
		//if($id)
		$rs=$db->fetch_array($db->query("SELECT * FROM `groups` where id=$id"));
		$query=$db->query("SELECT id,name FROM `crowd` order by id ");
		while($row=$db->fetch_array($query)) {
			$crowdrs[]=$row;
		}

	}
	elseif($action=="saveadd")
	{
					//WriteErrMsg("'$_POST['name']'");
					$username=$_POST['name'];

					$info=$_POST['info'];
					$ErrMsg="";
					if(empty($username))
						$ErrMsg ='<br><li>plesae input a name</li>';
					if($ErrMsg!="")
						WriteErrMsg($ErrMsg);
					else{
					
						$query=$db->query("SELECT id FROM `groups` WHERE name='$username' ");
						$rs=$db->fetch_array($query);
						if(empty($rs[0])){
							$query=$db->query("INSERT INTO `groups` (name,info,crowdid) value ('$username', '$info', $_POST[crowdid]) ");
							WriteSuccessMsg("<br><li>Add group success</li>","groups.php");
							/* ?????????????????????*/
						}
						else{
							$ErrMsg=$ErrMsg."<br><li>group [$username] have existed</li>";
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
						/*??????????????????*/
						//$crs=$db->fetch_array($db->query("SELECT id FROM `groups` where id=$Id and crowdid=$_POST[crowdid]"));
						
						$query=$db->query("UPDATE `groups` INNER JOIN crowd ON groups.crowdid = crowd.id SET groups.name='$_POST[name]',groups.info='$_POST[info]',groups.crowdid='$_POST[crowdid]' WHERE groups.id=$Id");

						//if(!$crs[0]) //	????????????	
							//$db->query("update receiver INNER JOIN groups ON receiver.groupid = group.id set receiver.crowdid=group.crowdid where receiver.groupid = group.id" );			
						
						WriteSuccessMsg("<br><li>Modify  success</li>","groups.php");
					}
	}
	/* ???????????????*/
	elseif($action=="admin"){

	$query=$db->query("SELECT count(*) AS count FROM refgroup where groupsid='$_GET[id]'");
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
	$fenye=showpage("crowd.php?",$page,$count,$perpage,true,true,"rows");
	$query=$db->query("SELECT user.*, groups.name as groupsname  FROM user,refgroup,groups where refgroup.groupsid='$_GET[id]' and user.id=refgroup.userid and groups.id=refgroup.groupsid ORDER BY refgroup.id DESC LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		$rsdb[]=$row;
	}
	
	$query=$db->query("SELECT * FROM user where permissions=3 and id not in (select userid from refgroup where groupsid=$_GET[id] ) ORDER BY id DESC ");
	while($userrow=$db->fetch_array($query)) {
		$userdb[]=$userrow;
	}
		
	}
	elseif($action=="addadmin"){

					$ErrMsg="";
					if(empty($_POST[admin]))
						$ErrMsg ='<br><li>choose a administrator please</li>';
					if(empty($_GET[id]))
						$ErrMsg ='<br><li>Invalid ID of the group</li>';
					if($ErrMsg!="")
						WriteErrMsg($ErrMsg);
					else{
					
						$query=$db->query("SELECT id FROM refgroup WHERE groupsid=$_GET[id] and userid=$_POST[admin] ");
						$rs=$db->fetch_array($query);
						if(empty($rs[0])){
							
							$query=$db->query("INSERT INTO refgroup (groupsid,userid) select groups.id, user.id from user,`groups` where user.id=$_POST[admin] and groups.id=$_GET[id]");
							
							WriteSuccessMsg("<br><li>Add administrator success</li>","groups.php?action=admin&id=$_GET[id]");
							/* ?????????????????????*/
						}
						else{
							$ErrMsg=$ErrMsg."<br><li>This user is the administrator of the crowd already</li>";
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
						$query=$db->query("DELETE FROM refgroup WHERE id IN ($Id)");
						
						WriteSuccessMsg("<br><li>Successfully Deleted</li>","groups.php?action=admin&id=$_GET[groupsid]");
						
					}
	}
	elseif($actoin=="deladmin"){
	}
	else $action="main";
	
}
else $action="main";

if($action=="main" && $_SESSION['goip_permissions'] < 2)	
{
	$query=$db->query("SELECT count(*) AS count FROM `groups`");
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
	$fenye=showpage("groups.php?",$page,$count,$perpage,true,true,"rows");
	$query=$db->query("SELECT groups.*,crowd.name as crowdname FROM `groups`,crowd where crowd.id=groups.crowdid ORDER BY id DESC LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		/*???????????????????????????*/
		$usersql=$db->query("select username from user,refgroup where refgroup.groupsid=$row[id] and user.id=refgroup.userid order by refgroup.id desc ");

		$i=0;
		/*????????????3???*/		
		while($userrow=$db->fetch_array($usersql)){ 
			$i++;
			$row[username].=$userrow[0]." ";
			if($i>5){
				$row[username].="Etc.";
				break;
			}
		}
		$rsdb[]=$row;
	}
}
	require_once ('groups.htm');

?>

