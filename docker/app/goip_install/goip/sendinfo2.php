<?php 
define("OK", true);
require_once("global.php");


	$otherh='<a href="sendinfo.php?type=all" target=main>所有人的发送</a>';
	$query=$db->query("SELECT count(*) AS count FROM sends ");
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
	$fenye=showpage("sendinfo2.php?",$page,$count,$perpage,true,true,"编");

	$query=$db->query("SELECT receiver.*,sends . * ,  goip.name AS goipname,prov.prov, message.msg                              FROM sends left join receiver on receiver.id = sends.recvid left join message on sends.messageid=message.id left join prov on sends.provider=prov.prov left join goip on sends.goipid=goip.id
WHERE 1 order by sends.time desc LIMIT $start_limit,$perpage");
	
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
		$rsdb[]=$row;
	}
	require_once("sendinfo2.htm");
?>
