<?php 
require_once("session.php");
define("OK", true);
require_once("global.php");

if($_GET['type']=="all"){
	if($_SESSION[goip_userid]>1)
		die("Permission denied!");
}

if($_SESSION[goip_userid]<2)
	$otherh='<a href="sendinfo.php?type=all" target=main>All sendings</a>';
if($_GET['id']) {
	$query=$db->query("SELECT count(*) AS count FROM sends where sends.goipid = $_REQUEST[id]");
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
	$fenye=showpage("outbox.php?id=$_REQUEST[id]&",$page,$count,$perpage,true,true,"rows");
	
	//$db->query("update cron set over=1 where id=$_GET[id]");
	$query=$db->query("SELECT receiver.*,sends . * ,  goip.name AS goipname,prov.prov, message.msg
FROM sends left join receiver on receiver.id = sends.recvid, message, goip, prov
WHERE sends.messageid = message.id
AND goip.id=$_REQUEST[id]                                                                           
AND goip.id = sends.goipid                                                                                        
and sends.over=1                                                                                                  
and prov.id=sends.provider  order by `time` desc LIMIT $start_limit,$perpage");
	
	$sendc=0;
	while($row=$db->fetch_array($query)) {
		$sendc++;
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
				$row[over]="Undone";
				$row[resend]='<a href="resend.php?id='.$row[id].'">Resend</a>';
				break;
			case 1:
				//$sendc++;
				$row[over]="Done";			
				break;			
		}
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
}
//print_r($rsdb);
	require_once("outbox.htm");
?>
