<?php 
require_once("session.php");
define("OK", true);
require_once("global.php");
if($_GET['type']=="all"){
	if($_SESSION['goip_permissions']>1)
		die("Permission denied!");
}	
if($_SESSION['goip_permissions']<2)
	$otherh='<a href="cron.php?type=all" target=main>All people\'s tasks of sendings</a>';
if($_GET['id'] && $_GET['action'] != "del") {

	//echo "22222";
	$row0=$db->fetch_array($db->query("select * from message where id=$_GET[id]"));
	if($_SESSION['goip_permissions'] > 1 && $row0['userid']!=$_SESSION[goip_userid])
		die("Permission denied!");
	else $db->query("update message set over=3 where id=$_GET[id]");

	$query=$db->query("SELECT count(*) AS count FROM sends where messageid=$_GET[id]");
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
	$fenye=showpage("cron.php?id=$_GET[id]&",$page,$count,$perpage,true,true,"rows");
	

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
	
	while($row=$db->fetch_array($query)) {
		
		switch($row[recvlev]){
			case 0:
				$row[recvlev]="receiver";				
				break;
			case 1:
				$row[recvlev]="relation 1";
				$row[name]=$row[name1];
				$row[provider]=$row[provider1];
				break;			
			case 2:
				$row[recvlev]='relation 2';
				$row[name]=$row[name2];
				$row[provider]=$row[provider2];
				break;
		}
		switch($row[over]){
			case 0:
				$row[over]="failed";
				$row[resend]='<a href="resend.php?id='.$row[id].'">Resend</a>';
				break;
			case 1:
				$row[over]="Done";			
				break;			
		}
		$rsdb[]=$row;
	}
}
elseif($_GET['id'] && $_GET['action'] == "del") {
	$row0=$db->fetch_array($db->query("select * from message where id=$_GET[id]"));
	if($_SESSION['goip_permissions'] > 1 && $row0['userid']!=$_SESSION[goip_userid])
		WriteErrMsg("Permission denied");
	elseif($row0[over]>0)
		WriteErrMsg("The task has been started or completed");
	else $db->query("delete from message where id=$_GET[id]");
		WriteSuccessMsg("Deleted done.", "cron.php");
}
else {
	if($_SESSION['goip_permissions']<2){

		$username='all';
		$query=$db->query("SELECT * FROM user ORDER BY id DESC ");
		while($userrow=$db->fetch_array($query)) {
			if($_GET['userid'] && $userrow['id']==$_GET['userid'])
				$username=$userrow['username'];
			$userdb[]=$userrow;
		}		
		$userdb[]=array('id'=>0,'username'=>'all people');
		if($_GET['userid'])
			$userid=$_GET['userid'];
		else 
			$userid='userid';
	}
	else $userid=$_SESSION[goip_userid];
	
	$query=$db->query("SELECT count(*) AS count FROM message where userid=$userid and crontime");
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
	$fenye=showpage("cron.php?type=$_GET[type]&userid=$_GET[userid]&",$page,$count,$perpage,true,true,"rows");
	//echo "SELECT * from message where userid=$userid where crontime ORDER BY userid,over DESC,crontime DESC LIMIT $start_limit,$perpage";
	$query=$db->query("SELECT * from message where userid=$userid and crontime ORDER BY crontime DESC LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		$row[crontime]=date("Y-m-d H:i:s", $row[crontime]);
		switch($row[over]){
			case 0:
				$row[overmsg]="Wait";
				break;
			case 1:
				$row[overmsg]="Sending";
				break;
			case 2:
				$row[overmsg]='<font color="#FF0000">Done and not be read</font>';
				break;
			case 3:
				$row[overmsg]="Done";
				break;
		}
		$query0=$db->query("SELECT count(*) as count from sends,message where message.id=$row[id] and  sends.messageid=message.id and message.crontime");
		$row0=$db->fetch_array($query0);
		$row["total"]=$row0['count'];

		$query0=$db->query("SELECT count(*) as count from sends,message where message.id=$row[id] and sends.messageid=message.id and sends.over=1 and message.crontime");
		$row0=$db->fetch_array($query0);
		$row["succ"]=$row0['count'];		
		$rsdb[]=$row;
	}
}
//print_r($rsdb);
	require_once("cron.htm");
?>
