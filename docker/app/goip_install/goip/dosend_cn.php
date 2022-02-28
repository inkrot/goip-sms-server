<?php
define("OK", true);
session_start();
require_once("global.php");

if(!get_magic_quotes_gpc()){
        $_REQUEST[USERNAME]=addslashes($_REQUEST[USERNAME]);
        $_REQUEST[PASSWORD]=addslashes($_REQUEST[PASSWORD]);
}
if($_REQUEST[Memo]) $_POST[Memo]=$_REQUEST[Memo];
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

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css">
<title>发送信息</title>
</head>
<body>
<?php
ignore_user_abort(true);
set_time_limit(0);
ini_set("memory_limit", "200M");
echo str_pad(" ", 256);
if($goipcronport)
	$port=$goipcronport;
else 
	$port=44444;
	//ob_end_flush();
/*发送程序*/
	function dolastsend( &$goipsend,$len,$msg)
	{
		global $port;
		$sendid=$goipsend[messageid];
		echo "dolastsend $goipsend[send] time:".time()."<br>";
		
		if($goipsend[send]=="RMSG"){
			if($goipsend[timer] <=1 ){
				$goipsend[send]="MSG";
				$goipsend[timer]=3;
			}
			else return;
		}
		
		if($goipsend[send]=="HELLO"){
			$buf="HELLO $sendid\n";
			if (@socket_sendto($goipsend[sock],$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
				echo ("sendto error");						
			//$goipsend[timer]=0;
			
		}
		elseif($goipsend[send]=="PASSWORD"){
			$buf="PASSWORD $sendid $goipsend[password]\n";
			
			@socket_sendto($goipsend[sock],$buf, strlen($buf), 0, "127.0.0.1", $port);
			//$goipsend[timer]=0;					
		}
		elseif($goipsend[send]=="SEND"){
			$buf="SEND $sendid $goipsend[telid] $goipsend[tel]\n";
			@socket_sendto($goipsend[sock],$buf, strlen($buf), 0, "127.0.0.1", $port);
			echo "$buf ($goipsend[name] $goipsend[prov]) <br>";
			//$goipsend[timer]=0;
		}	
		elseif($goipsend[send]=="MSG"){
			$buf="MSG ".$sendid." $len $msg\n";
		
			@socket_sendto($goipsend[sock],$buf, strlen($buf), 0, "127.0.0.1", $port);
			//$goipsend[timer]=0;
		}				
	}


	function do_cron($db,$crontime,$port)
	{
		if(!$port) $port=44444;
		$rs=$db->fetch_array($db->query("SELECT id FROM message WHERE crontime>0 and crontime<$crontime and `over`=0"));//是否有未执行的比新计划还要前的计划
		$flag=1;
		if(empty($rs[0])){
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

		}//最新计划
		if($flag)
			echo "已加入";
		else 
			echo "已加入,但goipcron进程未响应，请检查该进程";		
	}
	
	function checktime()
	{
		$dabuf=explode(' ',$_POST[datehm]);
		$date=explode('-',$dabuf[0]);
		$time=explode(':',$dabuf[1]);
		//print_r($dabuf);
		//print_r($date);
		//print_r($time);
		//echo date ("Y-m-d H:i:s", $_POST[datehm]);
		$crontime=mktime ($time[0],$time[1],0,$date[1],$date[2],$date[0]);
		$nowtime=time();
		//if($nowtime > $crontime)
			//die("现在时间".date ("Y-m-d H:i:s")."比设定的时间$_POST[datehm]晚");
		return $crontime;
	}
	
	function checkover($goipdb)
	{
		//echo "checkover <br>";
		foreach($goipdb as $the0 => $goipsend){
			if($goipsend[timer]>0){//重试
				if($goipdb[$the0][send]!="RMSG"){  //如果其他已结束，将不等待连不上的GOIP
					return false;//未完成
				}
			}
		}
		return true;				
	}


/*要有一个权限检查*/
if(!isset($_POST['crowdid']) && !isset($_POST['groupid']) && !isset($_POST['alltype']) && !isset($_REQUEST['method']))
	die("Permission denied");
if(get_magic_quotes_gpc())
	$memo=stripslashes($_POST[Memo]);
else 
	$memo=$_POST[Memo];
if(!get_magic_quotes_gpc())
	$_POST[Memo]=addslashes($_POST[Memo]);

//$_POST[Memo]=$db->real_escape_string($_POST[Memo]);
	//exit;

$query=$db->query("SELECT * from prov ");
while($row=$db->fetch_assoc($query)) {
	//echo $row[id]." ".$row[inter]."<br>";
	$row[interlen]=strlen($row[inter]);
	$row[locallen]=strlen($row[local]);
	$prov[$row[id]]=$row;
}

if(isset($_POST['groupid'])){	//发送一个组的若干id
if(empty($_POST[groupid]))
	die("Permission denied");
if($_SESSION['goip_permissions'] > 1){

	$query=$db->query("SELECT id FROM refgroup WHERE groupsid=$_POST[groupid] and userid=$_SESSION[goip_userid]");
	$rs=$db->fetch_array($query);
	if(empty($rs[0])){
		$query=$db->query("SELECT groups.crowdid FROM refcrowd,groups WHERE groups.id=$_POST[groupid] and refcrowd.crowdid=groups.crowdid and refcrowd.userid=$_SESSION[goip_userid]");
		$rs=$db->fetch_array($query);
		if(empty($rs[0])){
			die("Permission denied");
		}
	}
}

//	exit;

		$totalnum=0;


/*tels 6元素的数组*/
		$num=$_POST['boxsa'];

		for($i=0;$i<$num;$i++)
		{	
			if(!empty($_POST["ida$i"])){
				if($Ida=="")
					$Ida=$_POST["ida$i"];
				else
					$Ida=$_POST["ida$i"].",$Ida";
			}
		}
		for($i=0;$i<$num;$i++)
		{	
			if(!empty($_POST["idb$i"])){
				if($Idb=="")
					$Idb=$_POST["idb$i"];
				else
					$Idb=$_POST["idb$i"].",$Idb";
			}
		}
		for($i=0;$i<$num;$i++)
		{	
			if(!empty($_POST["idc$i"])){
				if($Idc=="")
					$Idc=$_POST["idc$i"];
				else
					$Idc=$_POST["idc$i"].",$Idc";
			}
		}

		
		if($_POST[submit2]){
			$crontime=checktime();
			if($_GET[id])
				$db->query("update message set receiverid='$Ida', receiverid1='$Idb', receiverid2='$Idc', msg='$_POST[Memo]', userid=$_SESSION[goip_userid],crontime=$crontime where id=$_GET[id]");
			else 
				$db->query("INSERT INTO message (receiverid,receiverid1,receiverid2,msg,userid,crontime,groupid) VALUES ('$Ida','$Idb','$Idc','$_POST[Memo]',$_SESSION[goip_userid],$crontime,'$_POST[groupid]')");	
			do_cron($db,$crontime,$goipcronport);
			exit;
		}
		
		$tels=array();
		if(!$Ida) $Ida=0;
		if(!$Idb) $Idb=0;
		if(!$Idc) $Idc=0;
		$query=$db->query("(SELECT id,name,provider,tel,0 as lev FROM receiver where id in ($Ida)) union (SELECT id,name1,provider1,tel1,1 FROM receiver where id in ($Idb) ) union (SELECT id,name2,provider2,tel2,2 FROM receiver where id in ($Idc) ) ORDER BY tel");	
		$teltmp=0;
		while($row=$db->fetch_assoc($query)) {
			if(!$row[tel]) continue;
			if(!($prov[$row[provider]][locallen] && !strncmp($row[tel], $prov[$row[provider]][local], $prov[$row[provider]][locallen])) && $row[tel][0] !='+')
				$row[tel]=$prov[$row[provider]][inter].$row[tel];
			if($row[tel] && $row[tel] != $teltmp) {
				$tels[$row[provider]][]=$row;
				$teltmp=$row[tel];
				$totalnum++;
			}
		}
			//print_r($rsdb);
		/*删除重复的号码*/
		//foreach($tels as $prov => $telrow){
			//$tels[$prov]=array_unique($telrow);
		//}
		$db->query("INSERT INTO message (receiverid,receiverid1,receiverid2,msg,userid,groupid) VALUES ('$Ida','$Idb','$Idc','$_POST[Memo]',$_SESSION[goip_userid],'$_POST[groupid]')");	
		$sendsiddb=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
		$sendid=$sendsiddb[0];
		echo "将发送短信内容：$memo <br>";
		echo "将发送总计: $totalnum 项<br>";
		startdo($db, $tels, $sendid);
}elseif(isset($_POST['crowdid'])){//以组为最小单位发送
		$num=$_POST['boxsa'];

		for($i=0;$i<$num;$i++)
		{	
			if(!empty($_POST["id$i"])){
				if($Id=="")
					$Id=$_POST["id$i"];
				else
					$Id=$_POST["id$i"].",$Id";
			}
		}
		if($_POST['jr']) $Ida=$Id;
		if($_POST['jr1']) $Idb=$Id;
		if($_POST['jr2']) $Idc=$Id;
		if($_POST[submit2]){
			$crontime=checktime();
			if($_GET[id])
				$db->query("update message set type=1,groupid='$Ida', groupid1='$Idb', groupid2='$Idc', msg='$_POST[Memo]', userid=$_SESSION[goip_userid],crontime=$crontime where id=$_GET[id]");
			else 
				$db->query("INSERT INTO message (type,groupid,groupid1,groupid2,msg,userid,crontime) VALUES (1,'$Ida','$Idb','$Idc','$_POST[Memo]',$_SESSION[goip_userid],$crontime)");	
			do_cron($db,$crontime,$goipcronport);
			exit;
		}
		if(!$Ida) $Ida=0;
		if(!$Idb) $Idb=0;
		if(!$Idc) $Idc=0;		
		$query=$db->query("(SELECT receiver.id,name,provider,tel,0 as lev FROM receiver inner join recvgroup on (receiver.id=recvgroup.recvid ) where groupsid in ($Ida)) 
union ( SELECT receiver.id,name,provider,tel,1 FROM receiver inner join recvgroup on (receiver.id=recvgroup.recvid ) where groupsid in ($Idb)) 
union ( SELECT receiver.id,name,provider,tel,2 FROM receiver inner join recvgroup on (receiver.id=recvgroup.recvid ) where groupsid in ($Idc)) 
order by tel");	
		$tels=array();
		$teltmp=0;
		while($row=$db->fetch_assoc($query)) {
			if(!$row[tel]) continue;
			if(!($prov[$row[provider]][locallen] && !strncmp($row[tel], $prov[$row[provider]][local], $prov[$row[provider]][locallen])) && $row[tel][0] !='+')
				$row[tel]=$prov[$row[provider]][inter].$row[tel];
			if($row[tel] && $row[tel] != $teltmp) {
				$tels[$row[provider]][]=$row;
				$teltmp=$row[tel];
				$totalnum++;
				//echo $teltmp."<br>";
			}
		}
		//die("haha");

		echo "将发送短信内容：$memo <br>";
		echo "将发送总计: $totalnum 项<br>";
		//print_r($tels);
		$db->query("INSERT INTO message (type,groupid,groupid1,groupid2,msg,userid) VALUES (1,'$Ida','$Idb','$Idc','$_POST[Memo]',$_SESSION[goip_userid])");	
		$sendsiddb=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
		$sendid=$sendsiddb[0];
		startdo($db, $tels, $sendid);
		//}

}
elseif(isset($_POST['alltype'])) {//向全部人发送
	//print_r($_POST);
	$jr=$_POST['jr']?1:0;
	$jr1=$_POST['jr1']?1:0;
	$jr2=$_POST['jr2']?1:0;
	if($_POST[submit2]){
		$crontime=checktime();
		if($_GET[id])
			$db->query("update message set type=2, recv=$jr, recv1=$jr1, recv2=$jr1, msg='$_POST[Memo]', userid=$_SESSION[goip_userid],crontime=$crontime where id=$_GET[id]");
		else 
			$db->query("INSERT INTO message (type,msg,userid,crontime,recv,recv1,recv2) VALUES (2, '$_POST[Memo]', $_SESSION[goip_userid], $crontime,$jr,$jr1,$jr2)");	
		do_cron($db,$crontime,$goipcronport);
		exit;
	}
	$query=$db->query("(SELECT id,name,provider,tel,0 as lev FROM receiver where $jr) union (SELECT id,name1,provider1,tel1,1 FROM receiver where $jr1 ) union (SELECT id,name2,provider2,tel2,2 FROM receiver where $jr2) ORDER BY tel");	
	$tels=array();
	$teltmp=0;
	while($row=$db->fetch_assoc($query)) {
		if(!$row[tel]) continue;
		if(!($prov[$row[provider]][locallen] && !strncmp($row[tel], $prov[$row[provider]][local], $prov[$row[provider]][locallen])) && $row[tel][0] !='+')
			$row[tel]=$prov[$row[provider]][inter].$row[tel];
		if($row[tel] && $row[tel] != $teltmp) {
			$tels[$row[provider]][]=$row;
			$teltmp=$row[tel];
			$totalnum++;
		}
	}
	$db->query("INSERT INTO message (type,msg,userid,recv,recv1,recv2) VALUES (2,'$_POST[Memo]',$_SESSION[goip_userid],$jr,$jr1,$jr2)");	
	$sendsiddb=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
	$sendid=$sendsiddb[0];
	echo "将发送短信内容：$memo <br>";
	echo "将发送总计: $totalnum 项<br>";
	startdo($db, $tels, $sendid);
}
elseif($_REQUEST[method]==2) //输入一个号码
{
	$provid=$_REQUEST[smsprovider];
	$mobile=$_REQUEST[smsnum];

	if($_POST[submit2]){
		$crontime=checktime();
		if($_REQUEST[id])
			$db->query("update message set msg='$_REQUEST[Memo]', userid=$userid,crontime=$crontime,tel='$mobile',prov='$provid',goipid='$_REQUEST[smsgoip]' where id=$_REQUEST[id]");
		else 
			$db->query("INSERT INTO message (type,msg,userid,crontime,tel,prov,goipid) VALUES (4, '$_REQUEST[Memo]',$userid, $crontime,'$mobile','$provid','$_REQUEST[smsgoip]')");	
		do_cron($db,$crontime,$goipcronport);
		exit;
	}
	//print_r($_POST);
	//$query=$db->query("SELECT id,name,provider,tel,0 as lev FROM receiver where ");
	$mobiles=array();
	$mobiles=explode(",",$mobile);
	foreach($mobiles as $tel_num){
		if(!($prov[$provid][locallen] && !strncmp($tel_num, $prov[$provid][local], $prov[$provid][locallen])) && $tel_num[0] !='+')
			$tel_num=$prov[$provid][inter].$tel_num;

		$xrow['provider']=$provid;
		$xrow['lev']=0;
		$xrow['id']=0;
		$xrow['tel']=$tel_num;
		$tels[$provid][]=$xrow;
		//$teltmp=$row[tel];
		$totalnum++;
	}

	$db->query("INSERT INTO message (msg,userid,type,tel,prov) VALUES ('$_REQUEST[Memo]',$userid,4,'$mobile','$provid')");
	$sendsiddb=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
	$sendid=$sendsiddb[0];
		echo "sending message：$memo <br>";
		echo "total will send: $totalnum <br>";
	startdo($db, $tels, $sendid, $_REQUEST[smsgoip]);
}
else print_r($_POST);

function startdo($db, $tels,$sendid, $goipid=0){
		global $prov;
		global $port;
		global $memo;
		global $userid;
		//print_r($prov);
		$msg=$memo;
		$len=strlen($msg);		
		$nowtime=date ("Y-m-d H:i:s");
		//$sendid=1111;
		//$id=0;
		$query=$db->query("SELECT prov.*,goip.* FROM goip,prov where prov.id=goip.provider and alive=1 and gsm_status!='LOGOUT' ORDER BY name");	
		$socks=array();
		$errortels=array();
		$goipdb=array();
		while($goiprow=$db->fetch_array($query)) {
			$goipname[]=$goiprow[provider];
			


			/*把信息传过去*/			
			if(($goipid && $goiprow[id]==$goipid) || (!$goipid && $tels[$goiprow[provider]])){ //有要发给这个服务商的号码才通信
				echo "goipname:$goiprow[name] $goipnow[provider] $goipnow[name]";
				$errortels[$goiprow[provider]]=array();
				if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
					echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
					exit;
				}
				$goiprow['timer']=3;
				$goiprow['send']="MSG";
				$goiprow['messageid']=$sendid+($goiprow[id] << 16);
				echo "$sendid $goiprow[id] $goiprow[messageid] <br>";
				$goiprow['sock']=$socket;
				$goiprow['time']=time();//计时
				$goipdb[]=$goiprow;
				$buf="START ".$goiprow['messageid']." $goiprow[host] $goiprow[port]\n";
				if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
					echo ("sendto error");
				for($i=0;$i<3;$i++){
					$read=array($socket);
					$err=socket_select($read, $write = NULL, $except = NULL, 5);
					if($err>0){		
						if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){
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
				if($i>=3)
					die("goipcron 服务进程没有响应");
				$buf="MSG ".$goiprow['messageid']." $len $msg\n";
				if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
					echo ("sendto error");
				$socks[]=$socket;
				//print_r($goiprow);
			}
			//print_r($goiprow);
		}
		foreach($tels as $provtmp => $valuetmp){
			foreach($goipdb as $goiptmp){
				if($goiptmp['provider']==$provtmp)
					continue 2;
			}
			$n=count($valuetmp);
			echo "<font color='#FF0000'>要发送{$n}个".$prov[$provtmp]['prov']."服务商的号码，但找不到可用的".$prov[$provtmp]['prov']."GOIP</font><br>";
		}
		//$read = array($socket);
		$timeout=5;
		for(;;){
			$read=$socks;
			flush();
			if(count($read)==0)
				break;
			$err=socket_select($read, $write = NULL, $except = NULL, $timeout);
			//echo "select:$err <br>";
			if($err===false)
				echo "select error!";
			elseif($err==0){ //全体超时
				//echo "select timeout";
				$i=0;
				$flag=1;
				$nowtime=time();
				//reset($goipdb);
				//while (list (, $goipsend) = each ($goipdb)) {
				foreach($goipdb as $the0 => $goipsend){
					//$goipsend=$goipdb[$the0];
					$goipdb[$the0]['time']=$nowtime;
					if($goipsend[timer]>0){//重试
						if($goipdb[$the0][send]!="RMSG")  //如果其他已结束，将不等待连不上的GOIP
							$flag=0;//未完成
						dolastsend( $goipsend,$len,$msg);
						$goipdb[$the0]['timer']--;
						//echo("<br>$i timer:".$goipsend[timer]."<br>");
						$i++;
					}
					else{ //累计失败
						if($goipsend[send]=="OK") //已完成的
							continue;
						
						if($goipsend[send]=="SEND"){
							//echo "inser: $goipnow[telid] $goipnow[tel] faile<br>";
							echo "<font color='#FF0000'>$goipnow[telid] $goipnow[tel] faile</font><br>";
							foreach($goipdb as $the => $goiptmp){ 
								if($goiptmp[send]=="OK" && $goiptmp[provider]==$goipsend[provider]){
									if($goipsend[telrow][error] && in_array($goiptmp[id],$goipsend[telrow][error]))
										continue;//已发送错误
									$goipdb[$the][send]="SEND";
									$goipdb[$the][tel]=$goipsend[tel];
									$goipdb[$the][telid]=$goipsend[telid];
									//$goipsend[send]=="OK"; //结束
									dolastsend( $goiptmp,$len,$msg);
									$goipdb[$the0][send]="RMSG";//超时的goip，100s后通讯
									$goipdb[$the0][timer]=20;
									$goipdb[$the0][tel]=0;
									$goipdb[$the0][telid]=0;
									break;
								}									
							}
						}
						else{
							/*100s 后重新通讯*/
							echo "<font color='#FFOO00'>无响应: $goipsend[send] ($goipsend[name] $goipsend[prov])</font><br>";
							$goipdb[$the0][send]="RMSG";
							$goipdb[$the0][timer]=20; 
						}
						if($goipsend[send]=="SEND"){//没有找到空闲的goip，把号码压回，100s后重新通讯
							if($goipsend[telrow][error])
								array_push($errortels[$goipsend[provider]], $goipsend[telrow]);//压回出错数组
							else 
								array_push($tels[$goipsend[provider]], $goipsend[telrow]); //压回
							//array_push($tels[$goipsend[provider]], $goipsend[telrow]); //压回
							/*删除数据库*/
							$db->query("delete from sends where id=$goipsend[telid]");	
							$goipdb[$the0][send]="RMSG";
							$goipdb[$the0][timer]=20;
							$goipdb[$the0][tel]=0;
							$goipdb[$the0][telid]=0;						
						}	
					}	
				}
				if($flag)
					break; //全部结束
			}//全体超时
			else{ //可读
			
			  foreach($read as $socket){
				unset($buf);
				//$buf="";
				
				if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){
					//echo("recvform error".socket_strerror($ret)."<br>");
					continue;
				}
				  $comm=explode(" ",$buf);
					//$teli=(int)substr($comm[1], -1)-1;
					foreach($goipdb as $the => $goipnow){
						//echo "$key => $val\n";
						if($goipnow[sock]==$socket){
							break;
						}	
					}
					
					if(empty($goipnow)){ //不是期望的套接口
						continue; 
					}
					if(strncmp($goipnow[messageid],$comm[1], strlen($goipnow[messageid])))//不是期望的id
						continue;
					if($comm[0]=="OK"){
						//更新数据库，发送成功 
						
						$db->query("update sends set `over`=1 where id=$goipnow[telid] and messageid=$sendid");
						/**/
						if($goipnow[send]!="SEND"){//不处于发送状态，无视
							//echo "net send status <br>";
							continue;
						}	
						if($comm[2]==$goipnow[telid]){ //是现在发的号码,可以发下一个了

							echo "<font color='#00FF00'>$goipnow[telid] ".$goipnow[telrow][name]." $goipnow[tel] ($goipnow[name] $goipnow[prov]) ok</font><br>";
							$goipdb[$the]['send']="OK";//结束了					
							$goipdb[$the][telid]=0;
							$goipdb[$the][tel]=0;
							$goipdb[$the][timer]=0;
							if(($goipdb[$the][telrow]=array_pop($tels[$goipnow[provider]]))!==NULL){//出来	
								/*写入数据库，得到id, 发送*/
								 				
									$goipdb[$the][tel]=$goipdb[$the][telrow][tel];
								$db->query("INSERT INTO sends (messageid,userid,telnum,goipid,provider,recvid,recvlev) VALUES ($sendid,$userid,'".$goipdb[$the][tel]."',$goipnow[id],'$goipnow[provider]',".$goipdb[$the][telrow][id].",'".$goipdb[$the][telrow][lev]."')");		
								$telid=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
								$goipdb[$the][telid]=$telid[0];
								//$goipdb[$the][telid]=$testid++;
								//echo "inser: ".$goipdb[$the][telid]." ".$goipdb[$the][tel]." start<br>";
								$goipdb[$the]['send']="SEND";
								
								$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][telid]." ".$goipdb[$the][tel]."\n";
								echo "$buf ($goipnow[name] $goipnow[prov])<br>";
								if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
									echo ("sendto error");
								$goipdb[$the][timer]=3;
							}
							elseif($errortels[$goipnow[provider]]){
								foreach($errortels[$goipnow[provider]] as $telthe => $nowrow){
									if(!in_array($goipnow[id],$nowrow['error'])){
										$goipdb[$the][telrow]=$nowrow;
										unset($errortels[$goipnow[provider]][$telthe]);			
											$goipdb[$the][tel]=$goipdb[$the][telrow][tel];				
										$db->query("INSERT INTO sends (messageid,userid,telnum,goipid,provider,recvid,recvlev) VALUES ($sendid,$userid,'".$goipdb[$the][tel]."',$goipnow[id],'$goipnow[provider]',".$goipdb[$the][telrow][id].",'".$goipdb[$the][telrow][lev]."')");	
										$telid=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
			
										$goipdb[$the][telid]=$telid[0];
										//$goipdb[$the][telid]=$testid++;
										//echo "inser: ".$goipdb[$the][telid]." ".$goipdb[$the][tel]." start<br>";
										$goipdb[$the]['send']="SEND";
										$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][telid]." ".$goipdb[$the][tel]."\n";
										echo "$buf ($goipnow[name] $goipnow[prov])<br>";
										if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
											echo ("sendto error");
										$goipdb[$the][timer]=3;									
										break;
									}
								}							
							}
							if(checkover($goipdb)) break 2;
						}

					}
					elseif($comm[0]=="WAIT"){
						echo "WAIT $goipnow[send] $comm[2] ($goipnow[name] $goipnow[prov] $goipnow[tel])<br>";
						if($goipnow[send]=="SEND" && $comm[2]==$goipnow[telid]){
							$goipdb[$the][timer]=3;//持续发送
						}
					}
					elseif($comm[0]=="MSG"){ //不应该收到
					}
					elseif($comm[0]=="SEND"){
						//$goipnow['ok']=1;
						//$goipnow[send]="SEND";		
						if($goipnow[send]=="SEND")//已经处于发送状态
							continue;
						$goipdb[$the]['send']="OK";//结束了
						$goipdb[$the][telid]=0;
						$goipdb[$the][tel]=0;
						$goipdb[$the][timer]=0;
						if(($goipdb[$the][telrow]=array_pop($tels[$goipnow[provider]]))!==NULL){//出来	
							/*写入数据库，得到id, 发送*/				
								$goipdb[$the][tel]=$goipdb[$the][telrow][tel];					
							$db->query("INSERT INTO sends (messageid,userid,telnum,goipid,provider,recvid,recvlev) VALUES ($sendid,$userid,'".$goipdb[$the][tel]."',$goipnow[id],'$goipnow[provider]',".$goipdb[$the][telrow][id].",'".$goipdb[$the][telrow][lev]."')");	
							$telid=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));

							$goipdb[$the][telid]=$telid[0];
							//$goipdb[$the][telid]=$testid++;
							//echo "inser: ".$goipdb[$the][telid]." ".$goipdb[$the][tel]." start<br>";
							$goipdb[$the]['send']="SEND";
							$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][telid]." ".$goipdb[$the][tel]."\n";
							echo "$buf ($goipnow[name] $goipnow[prov])<br>";
							if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
								echo ("sendto error");
							$goipdb[$the][timer]=3;
						}
						elseif($errortels[$goipnow[provider]]){
							foreach($errortels[$goipnow[provider]] as $telthe => $nowrow){
								if(!in_array($goipnow[id],$nowrow[error])){
									$goipdb[$the][telrow]=$nowrow;
									unset($errortels[$goipnow[provider]][$telthe]);
								 				
									$goipdb[$the][tel]=$goipdb[$the][telrow][tel];					
									$db->query("INSERT INTO sends (messageid,userid,telnum,goipid,provider,recvid,recvlev) VALUES ($sendid,$userid,'".$goipdb[$the][tel]."',$goipnow[id],'$goipnow[provider]',".$goipdb[$the][telrow][id].",'".$goipdb[$the][telrow][lev]."')");	
									$telid=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
		
									$goipdb[$the][telid]=$telid[0];
									//$goipdb[$the][telid]=$testid++;
									//echo "inser: ".$goipdb[$the][telid]." ".$goipdb[$the][tel]." start<br>";
									$goipdb[$the]['send']="SEND";
									$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][telid]." ".$goipdb[$the][tel]."\n";
									echo "$buf ($goipnow[name] $goipnow[prov])<br>";
									if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
										echo ("sendto error");
									$goipdb[$the][timer]=3;									
									break;
								}
							}							
						}
						if(checkover($goipdb)) break 2;
					}
					elseif($comm[0]=="PASSWORD"){
						//echo "PASSWORD ".$goipnow['send'];
						//$teli=substr($comm[1], -1);
						if($goipnow['send']!="PASSWORD" && $goipnow['send']!="MSG")//不是发送密码状态就不处理
							continue;
						if($goipnow['send']=="MSG"){
							$goipdb[$the]['timer']=3;
							$goipdb[$the]['send']="PASSWORD";
						}
						if($goipdb[$the]['send']=="PASSWORD"){
							if($goipdb[$the]['timer']-- > 0)
								dolastsend( $goipdb[$the],$len,$msg);
							else {
								$goipdb[$the]['timer']=20;
								$goipdb[$the]['send']="RMSG";
							}
						}
						/*
						socket_sendto($socket,"PASSWORD $goipnow[messageid] $goipnow[password]\n", strlen("PASSWORD $comm[1] $goipnow[password]\n"), 0, "127.0.0.1", $port);
						$goipdb[$the][send]="PASSWORD";
						$goipdb[$the][timer]=3;
						*/	
					}
					elseif($comm[0]=="ERROR"){
						echo "<font color='#FF0000'>$buf($goipnow[name] $goipnow[prov] $goipnow[tel])</font><br>";
						if($goipdb[$the][send]=="PASSWORD" && $comm[2]=="SENDID"){
								$goipdb[$the]['timer']=20;
								$goipdb[$the]['send']="RMSG";							
						}
						elseif($goipdb[$the][send]=="SEND" && $comm[2]=="SENDID"){//sendid失败
							$goipdb[$the]['telrow']['error'][]=$goipdb[$the]['id'];
							$findokflag=0;
							foreach($goipdb as $the1 => $goiptmp){
								if($goiptmp[send]=="OK" && $goiptmp[provider]==$goipsend[provider] && !in_array($goiptmp[id],$goipdb[$the]['telrow']['error']) ){
									$goipdb[$the1][send]="SEND";
									$goipdb[$the1][tel]=$goipnow[tel];
									$goipdb[$the1][telid]=$goipnow[telid];
									$goipdb[$the1][telrow]=$goipdb[$the]['telrow'];
									//$goipdb[$the1][telrow]['error']=$goipnow[telrow]['error'];
									$goipdb[$the1][timer]=3;
									$findokflag=1;
									$db->query("update sends set goipid=$goiptmp[id] where id=$goipnow[telid]");
									dolastsend( $goipdb[$the1],$len,$msg);
									break;
								}
							}
						
							if(!$findokflag){
								array_push($errortels[$goipsend[provider]], $goipdb[$the][telrow]);
								$db->query("delete from sends where id=$goipnow[telid]");
								//array_push($tels[$goipsend[provider]], $goipsend[tel]); //压回
							}
							
							$goipdb[$the]['send']="MSG";//结束了
							$goipdb[$the][telid]=0;
							$goipdb[$the][tel]=0;
							$goipdb[$the][timer]=3;	
							dolastsend($goipdb[$the],$len,$msg);
						}
						elseif($goipdb[$the][send]=="SEND" && $comm[2]==$goipnow[telid]){//发送失败
							$goipdb[$the]['telrow']['error'][]=$goipdb[$the]['id'];
							$findokflag=0;
							foreach($goipdb as $the1 => $goiptmp){
								if($goiptmp[send]=="OK" && $goiptmp[provider]==$goipsend[provider] && !in_array($goiptmp[id],$goipdb[$the]['telrow']['error']) ){
									$goipdb[$the1][send]="SEND";
									$goipdb[$the1][tel]=$goipnow[tel];
									$goipdb[$the1][telid]=$goipnow[telid];
									$goipdb[$the1][telrow]=$goipdb[$the]['telrow'];
									//$goipdb[$the1][telrow]['error']=$goipnow[telrow]['error'];
									$goipdb[$the1][timer]=3;
									$findokflag=1;
									$db->query("update sends set goipid=$goiptmp[id] where id=$goipnow[telid]");
									dolastsend( $goipdb[$the1],$len,$msg);
									break;
								}
							}
						
							if(!$findokflag){
								array_push($errortels[$goipsend[provider]], $goipdb[$the][telrow]);
								$db->query("delete from sends where id=$goipnow[telid]");
								//array_push($tels[$goipsend[provider]], $goipsend[tel]); //压回
							}
							
							$goipdb[$the]['send']="OK";//结束了
							$goipdb[$the][telid]=0;
							$goipdb[$the][tel]=0;
							$goipdb[$the][timer]=0;								
							if(($goipdb[$the][telrow]=array_pop($tels[$goipnow[provider]]))!==NULL){//出来	
								/*写入数据库，得到id, 发送*/
								 				
									$goipdb[$the][tel]=$goipdb[$the][telrow][tel];				
								$db->query("INSERT INTO sends (messageid,userid,telnum,goipid,provider,recvid,recvlev) VALUES ($sendid,$userid,'".$goipdb[$the][tel]."',$goipnow[id],'$goipnow[provider]',".$goipdb[$the][telrow][id].",'".$goipdb[$the][telrow][lev]."')");	
								$telid=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
	
								$goipdb[$the][telid]=$telid[0];
								//$goipdb[$the][telid]=$testid++;
								//echo "inser: ".$goipdb[$the][telid]." ".$goipdb[$the][tel]." start<br>";
								$goipdb[$the]['send']="SEND";
								$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][telid]." ".$goipdb[$the][tel]."\n";
								echo "$buf ($goipnow[name] $goipnow[provider])<br>";
								if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
									echo ("sendto error");
								$goipdb[$the][timer]=3;
							}
							elseif($errortels[$goipnow[provider]]){
								foreach($errortels[$goipnow[provider]] as $telthe => $nowrow){
									if(!in_array($goipnow[id],$nowrow[error])){
										$goipdb[$the][telrow]=$nowrow;
										unset($errortels[$goipnow[provider]][$telthe]);			
											$goipdb[$the][tel]=$goipdb[$the][telrow][tel];	
										$db->query("INSERT INTO sends (messageid,userid,telnum,goipid,provider,recvid,recvlev) VALUES ($sendid,$userid,'".$goipdb[$the][tel]."',$goipnow[id],'$goipnow[provider]',".$goipdb[$the][telrow][id].",'".$goipdb[$the][telrow][lev]."')");	
										$telid=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
			
										$goipdb[$the][telid]=$telid[0];
										//$goipdb[$the][telid]=$testid++;
										//echo "inser: ".$goipdb[$the][telid]." ".$goipdb[$the][tel]." start<br>";
										$goipdb[$the]['send']="SEND";
										$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][telid]." ".$goipdb[$the][tel]."\n";
										echo "$buf ($goipnow[name] $goipnow[prov])<br>";
										if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
											echo ("sendto error");
										$goipdb[$the][timer]=3;									
										break;
									}
								}							
							}
							if(checkover($goipdb)) break 2;
						}
						else if($goipnow[timer]>0){
							dolastsend( $goipdb[$the],$len,$msg);
							$goipdb[$the][timer]--;
						}
						else //等待它超时吧
							continue;
					}//elseif($comm[0]=="ERROR"){
					$goipdb[$the]['time']=time();
				//}//foreach($bufline as $line){
			  }//foreach($read
				$i=0;
				
				$nowtime=time();
				foreach($goipdb as $the0 => $goipsend){
					//$flag=0;
					if($goipsend['time'] <$nowtime-$timeout && $goipsend[send]!="OK"){//超时了
						if($goipsend[timer]>0){//重试
							//$flag=1;//未完成
							dolastsend( $goipdb[$the0],$len,$msg);
							$goipdb[$the0][timer]--;
							//echo("<br>$i timer:".$goipsend[timer]."<br>");
							$i++;
						}
						else{ //累计失败
							if($goipsend[send]=="SEND"){
								echo "<font color='#FF0000'>$goipnow[telid] $goipnow[tel] cannot get answer</font><br>";
								//$db->query("dela");	
								foreach($goipdb as $the => $goiptmp){
									if($goiptmp[send]=="OK" && $goiptmp[provider]==$goipsend[provider]){
										if($goipsend[telrow][error] && in_array($goiptmp[id],$goipsend[telrow][error]))
											continue;//已发送错误
										$goipdb[$the][send]="SEND";
										$goipdb[$the][tel]=$goipsend[tel];
										$goipdb[$the][telid]=$goipsend[telid];
										$goipdb[$the0][send]=="OK"; //结束
										dolastsend( $goiptmp,$len,$msg);
										$goipdb[$the0][send]="RMSG";//超时的goip，100s后通讯
										$goipdb[$the0][timer]=20;
										$goipdb[$the0][tel]=0;
										$goipdb[$the0][telid]=0;
										break;
									}
										
								}
							}
							else{
								echo "<font color='#FFOO00'>无响应: $goipsend[send] ($goipsend[name] $goipsend[provider])</font><br>";
								/*100s 后重新通讯*/
								$goipdb[$the0][send]="RMSG";
								$goipdb[$the0][timer]=20; 
							}
							if($goipsend[send]=="SEND"){//没有找到空闲的goip，把号码压回，100s后重新通讯
								if($goipsend[telrow][error])
									array_push($errortels[$goipsend[provider]], $goipsend[telrow]);//压回出错数组
								else 
									array_push($tels[$goipsend[provider]], $goipsend[telrow]); //压回
								
								/*删除数据库*/
								$db->query("delete from sends where id=$goipsend[telid]");	
								$goipdb[$the0][send]="RMSG";
								$goipdb[$the0][timer]=20;
								$goipdb[$the0][tel]=0;
								$goipdb[$the0][telid]=0;						
							}	
							if(checkover($goipdb)) break 2;	
						}	
					}
					$goipdb[$the0]['time']=$nowtime;
					
					//else $flag++;//完成
				}
			}//else{ //可读
			/*检查超时*/
		}//for(;;){
		foreach($socks as $socket){
			foreach($goipdb as $the => $goipnow){
				//echo "$key => $val\n";
				if($goipnow[sock]==$socket){
					break;
				}	
			}
			if($goipnow[sock]==$socket)
				socket_sendto($socket,"DONE $goipnow[messageid]\n", strlen("DONE $goipnow[messageid]\n"), 0, "127.0.0.1", $port);						
		}
		
		
		$i=0;
		foreach($tels as $provname => $provtels){
			foreach($provtels as $send){
				$db->query("INSERT INTO sends (messageid,userid,telnum,provider,recvid,recvlev) VALUES ($sendid,$userid,'".$send[tel]."','$provname',".$send[id].",".$send[lev].")");
				$i++;
			}	
		}
		foreach($errortels as $provname => $provtels){
			foreach($provtels as $send){
				$db->query("INSERT INTO sends (messageid,userid,telnum,provider,recvid,recvlev) VALUES ($sendid,$userid,'".$send[tel]."','$provname',".$send[id].",".$send[lev].")");	
				$i++;
			}
		}
		echo "发送完毕！失败:{$i}项";
		echo "<br><br>";
		echo "<a href=sendinfo.php?id=$sendid target=main><font size=2'>点我查看详情</font></a>";
		
}
?>
</body>
</html>
