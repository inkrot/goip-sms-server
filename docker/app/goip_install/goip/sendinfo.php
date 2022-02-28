<?php 
require_once("session.php");
define("OK", true);
require_once("global.php");

if($_GET['type']=="all"){
	if($_SESSION[goip_userid]>1)
		die("没有权限");
}

if($_SESSION[goip_userid]<2)
	$otherh='<a href="sendinfo.php?type=all" target=main>所有人的发送</a>';
if($_REQUEST['action']=="del")
{
	if(admin_only()) WriteErrMsg('<br><li>forbidden</li>');
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
	if(empty($Id))
		$ErrMsg ='<br><li>Please choose one</li>';
	if($ErrMsg!="")
		WriteErrMsg($ErrMsg);
	else{
		$query=$db->query("DELETE FROM message WHERE id IN ($Id)");
		$query=$db->query("DELETE FROM sends WHERE messageid IN ($Id)");//關系
		WriteSuccessMsg("<br><li>Successfully deleted</li>","sendinfo.php");

	}
}
elseif($_REQUEST['action']=="delall"){
		if(admin_only()) WriteErrMsg('<br><li>forbidden</li>');
                $query=$db->query("DELETE FROM message WHERE 1");
		$query=$db->query("DELETE FROM sends WHERE 1");
                WriteSuccessMsg("<br><li>Successfully deleted</li>","sendinfo.php");
}
if($_GET['id']) {
	$query=$db->query("SELECT count(*) AS count FROM sends where sends.messageid = $_GET[id]");
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
	$fenye=showpage("sendinfo.php?id=$_GET[id]&userid=$_GET[userid]&",$page,$count,$perpage,true,true,"编");
	
	//$db->query("update cron set over=1 where id=$_GET[id]");
	$row0=$db->fetch_array($db->query("select * from message where id=$_GET[id]"));
	if($_SESSION['goip_permissions'] > 1 && $row0['userid']!=$_SESSION[goip_userid])
		die("没有权限~");
	$query=$db->query("(SELECT receiver . * ,sends . * ,  '-' as goipname,prov.prov
FROM sends left join receiver on receiver.id = sends.recvid, message, prov
WHERE message.id =$_GET[id]                                                                                       
AND sends.messageid = message.id                                                                                  
AND sends.over=0                                                                                                  
and prov.id=sends.provider                                                                                        
)                                                                                                                 
union (                                                                                                           
SELECT receiver.*,sends . * ,  goip.name AS goipname,prov.prov                                                    
FROM sends left join receiver on receiver.id = sends.recvid, message, goip, prov
WHERE message.id =$_GET[id]                                                                                       
AND sends.messageid = message.id                                                                                  
AND goip.id = sends.goipid                                                                                        
and sends.over=1                                                                                                  
and prov.id=sends.provider  
) LIMIT $start_limit,$perpage");
	
	$sendc=0;
	while($row=$db->fetch_array($query)) {
		$sendc++;
		switch($row[recvlev]){
			case 0:
				$row[recvlev]="接收人";				
				break;
			case 1:
				$row[recvlev]="关联人1";
				$row[name]=$row[name1];
				$row[provider]=$row[provider1];
				break;			
			case 2:
				$row[recvlev]='关联人2';
				$row[name]=$row[name2];
				$row[provider]=$row[provider2];
				break;
		}
		switch($row[over]){
			case 0:
				$row[over]="未完成";
				$row[resend]='<a href="resend.php?id='.$row[id].'">重新发送</a>';
				break;
			case 1:
				//$sendc++;
				$row[over]="完成";			
				break;			
		}
		if($row['received']==1) $row['received']='已接收';
		else if($row['sms_no'] >= 0) {$row['received']='未知'; $row[resend]='<a href="resend.php?id='.$row[id].'">重新发送</a>';}
		else $row['received']='-';
		$rsdb[]=$row;
	}

/*
	$query=$db->query("(SELECT sends. * ,prov.prov, '-' AS goipname
FROM sends,prov
WHERE sends.messageid = $_GET[id]
AND sends.over =0 
and sends.provider=prov.id
)
union (
SELECT sends . * , prov.prov, goip.name AS goipname
FROM sends,  goip, prov
WHERE sends.messageid = $_GET[id]
AND goip.id = sends.goipid
and  sends.over=1 
and sends.provider=prov.id
) LIMIT $start_limit,$perpage ");
	$sendc=0;
	while($row=$db->fetch_array($query)) {
		switch($row[over]){
			case 0:
				$row[over]="未完成";
				$row[resend]='<a href="resend.php?id='.$row[id].'">重新发送</a>';
				break;
			case 1:
				$row[over]="完成";			
				break;			
		}
		$rsdb[]=$row;
		$sendc++;
	}
*/
}
else {
	if($_SESSION['goip_permissions']<2){

		$username='所有人';
		$query=$db->query("SELECT * FROM user ORDER BY id DESC ");
		while($userrow=$db->fetch_array($query)) {
			if($_GET['userid'] && $userrow['id']==$_GET['userid'])
				$username=$userrow['username'];
			$userdb[]=$userrow;
		}		
		$userdb[]=array('id'=>0,'username'=>'全部');
		if($_GET['userid'])
			$userid=$_GET['userid'];
		else 
			$userid='userid';
	}
	else $userid=$_SESSION[goip_userid];
	
	$query=$db->query("SELECT count(*) AS count FROM message where userid=$userid  and (over>1 or crontime=0)");
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
	$fenye=showpage("sendinfo.php?type=$_GET[type]&userid=$_GET[userid]&",$page,$count,$perpage,true,true,"编");
	
	$query=$db->query("SELECT * from message where userid=$userid and (over>1 or crontime=0) ORDER BY time DESC,userid LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		//$row[crontime]=date("Y-m-d H:i:s", $row[crontime]);

		$query0=$db->query("SELECT count(*) as count from sends,message where message.id=$row[id] and sends.messageid=message.id");
		$row0=$db->fetch_array($query0);
		$row["total"]=$row0['count'];

		$query0=$db->query("SELECT count(*) as count from sends,message where message.id=$row[id] and sends.messageid=message.id and sends.over=1");
		$row0=$db->fetch_array($query0);
		$row["succ"]=$row0['count'];		
		$rsdb[]=$row;
	}
}
//print_r($rsdb);
	require_once("sendinfo.htm");
?>
