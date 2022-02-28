<?php
require_once("session.php");
define("OK", true);
require_once("global.php");
//!defined('OK') && exit('ForbIdden');
if(isset($_GET['action'])) {
	$action=$_GET['action'];
	if($action!="search" && $action!="groups" && $_SESSION[permissions]>1 )
		WriteErrMsg("<br><li>需要admin权限!</li>");
	if($action=="search")
	{
		$key=$_POST['key'];
		$type=$_POST['type'];
		switch($type){
			case 1:
				$query=$db->query("SELECT * FROM receiver where no='$key'");
				$typename="编号";
				break;
			case 2:
				$query=$db->query("SELECT * FROM receiver where name='$key' or name1='$key' or name2='$key'");
				$typename="姓名";
				break;
			case 3:
				$query=$db->query("SELECT * FROM receiver where tel='$key' or tel1='$key' or tel2='$key'");
				$typename="电话号码";
				break;
			default:
				$typename="无效项";
		}
		$searchcount=0;
		while($row=$db->fetch_array($query)) {
			$searchcount++;
			$rsdb[]=$row;
		}	
		$action="searchmain";
		$maininfo="搜索项：$typename, 查询关键字：$key, 结果共{$searchcount}项.";
	}	
	elseif($action=="groups")
	{
		if(empty($_GET['id']))
			WriteErrMsg("<br><li>没有选择一个接收人!</li>");
		$id=$_GET[id];
		$query=$db->query("SELECT count(*) AS count FROM groups");
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
		$query=$db->query("SELECT groups.id FROM groups JOIN (receiver join recvgroup on receiver.id=recvgroup.recvid) ON recvgroup.groupsid =groups.id where receiver.id=$id order by id");
		$strs0=array();
		$rcount=0;
		while($row=$db->fetch_array($query)) {
			$rcount++;
			$strs0[]=$row['id'];
		}
		//echo $rcount;
		$fenye=showpage2("receiver.php?action=groups&id=$id&",$page,$count,$perpage,true,true,"编","myform","boxs");
		//if(_GET)
		$query=$db->query("(select groups.*,1 as 'in',crowd.name as crowdname  from groups,crowd where groups.crowdid=crowd.id and groups.id in ( SELECT groups.id FROM groups JOIN (receiver join recvgroup on receiver.id=recvgroup.recvid) ON recvgroup.groupsid =groups.id where receiver.id=$id order by id )) 
union 
(select groups.*,0 as 'in',crowd.name as crowdname from groups,crowd where groups.crowdid=crowd.id and groups.id not in ( SELECT groups.id FROM groups JOIN (receiver join recvgroup on receiver.id=recvgroup.recvid) ON recvgroup.groupsid =groups.id where receiver.id=$id order by id )) LIMIT $start_limit,$perpage");

		//$query=$db->query("SELECT receiver.*,groups.id as groupsid,groups.name as groupsname  FROM receiver LEFT JOIN (groups join recvgroup on groups.id=recvgroup.groupsid) ON recvgroup.recvid = receiver.id  ORDER BY groups.id=$id DESC,id DESC LIMIT $start_limit,$perpage");

//select * from receiver where id not in( SELECT receiver.id  FROM receiver LEFT JOIN (groups join recvgroup on groups.id=recvgroup.groupsid) ON recvgroup.recvid = receiver.id  where groups.id=8)  

		
		while($row=$db->fetch_array($query)) {
			
			if($row[in]){			
				$row['yes']="是";
			}
			else 
				$row['yes']="否";
			
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
			print_r($strs0);
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

		foreach($strs0 as $v){
			$nrcount++;
			if(in_array($v,$strs)) continue;
			//$nrcount++;
			$str.=$v.",";
			
		}
		//print_r();
		$str=substr($str,0,strlen($str)-1);
		$nametmp=$db->fetch_array($db->query("SELECT name FROM receiver where id=$_GET[id]"));
		$groupsname='<font color="#FF0000">'.$nametmp[0].'</font></a>';
	}
	elseif($action=="groupsmodify"){
		if(empty($_GET['id']))
			WriteErrMsg("<br><li>没有选择一个接收人!</li>");
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
		
		$query=$db->query("select groups.id,recvgroup.id as recvgroupid from groups,recvgroup where recvgroup.groupsid=groups.id and recvgroup.recvid=$id");
		while($row=$db->fetch_array($query)) {
			$flag=0;
			foreach($strs as $rkey => $rvalue){
				if($row[0]==$rvalue){
					unset($strs[$rkey]); //不用insert了；
					$flag=1;
					break;
				}
			}
			if(!$flag) $delstrs.=$row[1].","; //数据库有，post没有，需要删除
		}
		if($delstrs){
			$delstrs=substr($delstrs,0,strlen($delstrs)-1);
			//WriteSuccessMsg("delete from receiver where id in ($delstrs)","groups.php?action=recv&id=$id");
		
			$db->query("delete from recvgroup where id in ($delstrs)");
		}
		if(count($strs)){
			$sql="insert into recvgroup values ";
			foreach($strs as $rkey => $rgid){
				$sql.="(NULL,$rgid,$id),";
			}
			$sql=substr($sql,0,strlen($sql)-1);
			//WriteSuccessMsg($sql,"groups.php?action=recv&id=$id");
			$db->query($sql);
		}
		WriteSuccessMsg("<br><li>修改成功</li>","receiver.php?action=groups&id=$id");
		
	}
	elseif($action=="del")
	{
		if(operator_owner_forbid()) WriteErrMsg('<br><li>forbidden</li>');
					$ErrMsg="";
					$Id=$_GET['id'];
					//if(($Id=$_GET['id']) == "1")
						//$ErrMsg="<br><li>超级用户不能删除</li>";
					
					if(empty($Id)){
						$num=$_POST['boxs'];
						for($i=0;$i<$num;$i++)
						{	
							if(!empty($_POST["Id$i"])){
							/*
							  if($_POST["Id$i"] == "1"){
							  		$ErrMsg="<br><li>超级用户不能删除</li>";
									WriteErrMsg($ErrMsg);
									break;
							  }
							  */
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
						$query=$db->query("DELETE FROM receiver WHERE id IN ($Id)");
						//$query=$db->query("DELETE FROM recvgroup WHERE recvid IN ($Id)");//关系
						
						WriteSuccessMsg("<br><li>删除接收人成功</li>","receiver.php");
						
					}
		//$id=$_GET['id'];
		//$query=$db->query("Delete  from ".$tablepre."admin WHERE Id=$id");
	}
	elseif($action=="delall"){
		if(operator_owner_forbid()) WriteErrMsg('<br><li>forbidden</li>');
		$query=$db->query("DELETE FROM receiver WHERE 1");
		WriteSuccessMsg("<br><li>删除接收人成功</li>","receiver.php");
	}
	elseif($action=="add")
	{		
		if($_SESSION[permissions]>1)
			$query=$db->query("SELECT groups.id,groups.name,crowd.name as crowdname FROM refgroup,groups,crowd where refgroup.userid=$_SESSION[goip_userid] and refgroup.groupsid=groups.id and groups.crowdid=crowd.id ORDER BY crowd.id,groups.id DESC ");
		else 
			$query=$db->query("SELECT groups.id,groups.name,crowd.name as crowdname FROM groups,crowd where groups.crowdid=crowd.id ORDER BY crowdid,id DESC ");
		while($userrow=$db->fetch_array($query)) {
			$groupsdb[]=$userrow;
		}
		$query=$db->query("SELECT id,prov from prov ");
		while($row=$db->fetch_array($query)) {
			$pdb[]=$row;
		}
	}
	elseif($action=="modify")
	{
		$id=$_GET['id'];
		//$=$db->fetch_array($db->query("SELECT * FROM receiver where id=$id"));
		/*需要该组的管理权限 从id找到组， 在从组找到管理员，再比较;需要管理员的群组管理信息*/
		$query=$db->query("SELECT id,prov from prov ");
		while($row=$db->fetch_array($query)) {
			$pdb[]=$row;
		}		
		$rs=$db->fetch_array($db->query("SELECT * FROM receiver where id=$id "));
		//$rs[prov]=$pdb[$rs[provider]];
		//$rs[prov1]=$pdb[$rs[provider1]];
		//$rs[prov2]=$pdb[$rs[provider2]];		
	}
	elseif($action=="saveadd")
	{
					//WriteErrMsg(print_r($_POST));
					$name=$_POST['name'];

					$provider=$_POST['provider'];

		//$rs=$db->fetch_array($db->query("SELECT * FROM receiver where id=$id "));
							
					//$info=$_POST['info'];
					$ErrMsg="";
					if(empty($name))
						$ErrMsg .='<br><li>请输入姓名</li>';
					if(empty($_POST[no]))
						$ErrMsg .='<br><li>请输入编号</li>';
					if($_POST[groupid]!='' && !eregi('^[0-9]+$',$_POST[groupid]))
						$ErrMsg .='<br><li>组号错误: '.$_POST[groupid].'</li>';
					$no_t=$db->fetch_array($db->query("select id from receiver where no='".$_POST[no]."'"));
					if($no_t[0])
						$ErrMsg	.='<br><li>已存在编号: '.$_POST[no].'</li>';
					
					if($ErrMsg!="")
						WriteErrMsg($ErrMsg);
					else{
					 $groupid=$_POST[groupid];
/*					
		if($_SESSION[permissions]>1){
			$tmprs=$db->fetch_array($db->query("select * from refcrowd,refgroup,groups where  ( refgroup.groupsid=$_POST[groupid] and refgroup.userid=$_SESSION[goip_userid]) or (refgroup.groupsid=$_POST[groupid] and groups.crowdid=refcrowd.crowdid and refcrowd.userid=$_SESSION[goip_userid])"));
			if(!$tmprs[0]) WriteErrMsg("无此群组添加接收人的权限");
		}
*/
					if($_SESSION[permissions]>1) WriteErrMsg("无添加接收人的权限");
					
					
						
					$query=$db->query("INSERT INTO receiver (no,name,info,tel,provider,name1,tel1,provider1,name2,tel2,provider2) value ('".$_POST['no']."','".$_POST['name']."','".$_POST[info]."','".$_POST['tel']."','".$_POST['provider']."','".$_POST['name1']."','".$_POST['tel1']."','".$_POST['provider1']."','".$_POST['name2']."','".$_POST['tel2']."','".$_POST['provider2']."') ");
					if($groupid){
						$recvid=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
						$db->query("insert into recvgroup (groupsid,recvid) value ($groupid,$recvid[0])");
					}
						
						WriteSuccessMsg("<br><li>添加接收人成功</li>","receiver.php");				
					}
	}
	elseif($action=="savemodify")
	{
					$name=$_POST['name'];
					//$password=$_POST['Password'];
					$id=$_POST['id'];
					$ErrMsg="";
					if(empty($name))
						$ErrMsg .='<br><li>请输入姓名</li>';
					if(empty($_POST[no]))
						$ErrMsg .='<br><li>请输入编号</li>';
					$no_t=$db->fetch_array($db->query("select id from receiver where no='".$_POST[no]."' and id!=$id"));
					if($no_t[0])
						$ErrMsg	.='<br><li>已存在编号: '.$_POST[no].'</li>';
					if($ErrMsg!="")
						WriteErrMsg($ErrMsg);
					
/*		
		if($_SESSION[permissions]>1){
			$tmprs=$db->fetch_array($db->query("select * from refcrowd,refgroup,groups where  ( refgroup.groupsid=$_POST[groupid] and refgroup.userid=$_SESSION[goip_userid]) or (refgroup.groupsid=$_POST[groupid] and groups.crowdid=refcrowd.crowdid and refcrowd.userid=$_SESSION[goip_userid])"));
			if(!$tmprs[0]) WriteErrMsg("无此接收者的修改权限");
		}
*/
				if($_SESSION[permissions]>1) WriteErrMsg("无修改接收人的权限");
		
					if($ErrMsg!="")
						WriteErrMsg($ErrMsg);
					else{
						$query=$db->query("UPDATE receiver SET no='$_POST[no]',name='$_POST[name]',tel='$_POST[tel]',provider='$_POST[provider]',name1='$_POST[name1]',tel1='$_POST[tel1]',provider1='$_POST[provider1]',name2='$_POST[name2]',tel2='$_POST[tel2]',provider2='$_POST[provider2]' WHERE id=$id");

						WriteSuccessMsg("<br><li>Modify success</li>","receiver.php");
					}
	}
	else $action="main";
	
}
else {
$action="main";

//if($_SESSION['goip_adminname']=="admin")	

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
	$fenye=showpage("receiver.php?",$page,$count,$perpage,true,true,"编");
	$query=$db->query("SELECT id,prov from prov ");
	while($row=$db->fetch_array($query)) {
		$pdb[$row[id]]=$row[prov];
	}
	$query=$db->query("SELECT receiver.* FROM receiver ORDER BY id LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		$row[prov]=$pdb[$row[provider]];
		$row[prov1]=$pdb[$row[provider1]];
		$row[prov2]=$pdb[$row[provider2]];
		$rsdb[]=$row;
	}
	$maininfo="当前位置：接收人参数设置";
}
	require_once ('receiver.htm');

?>
