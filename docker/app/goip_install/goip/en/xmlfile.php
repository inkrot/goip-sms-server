<?php
require_once("session.php");
//if($_SESSION['goip_permissions'] > 1 )
	//WriteErrMsg("<br><li>Permission denied!</li>");
define("OK", true);
require_once("global.php");

	
	function do_cron($db,$crontime,$port,$count)
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
			WriteSuccessMsg("The task has been inserted,total {$count} message(s).", 'xmlfile.php');
		else 
			WriteErrMsg("The task has been inserted,but cannot get response from process named \"goipcron\". please check this process.");		
	}
	
	function checktime($date0,$time0)
	{
		//$dabuf=explode(' ',$_POST[datehm]);
		$date=explode('-',$date0);
		$time=explode(':',$time0);
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
	
if($_POST['action']=='send'){
	//echo 'send';
	if($goipcronport)
		$port=$goipcronport;
	else 
		$port=44444;
	require_once('../xmlClass.php');

	//$file = "data.xml"; 
	$data = implode("",file($_FILES['xmlfile']['tmp_name'])) or die("could not open XML input file"); 
	$obj = new xml($data,"xml");
	
	//以下是具体节点访问方式。
	//echo sizeof($xml["sms_msg"]);
	/*
	echo $xml["sms"][0]->uid[0],' ';
	echo $xml["sms"][0]->senddate[0],' ';
	echo $xml["sms"][0]->sendtime[0],' ';
	echo $xml["sms"][0]->stopdate[0],' ';
	echo $xml["sms"][0]->stoptime[0],' ';
	echo $xml["sms"][0]->goip[0],'<br>';
	*/
	$uid=addslashes($xml["sms"][0]->uid[0]);
	$prov=addslashes($xml["sms"][0]->goip[0]);
	//$uid=addslashes($xml["sms"][0]->uid[0]);
	$crontime=checktime($xml["sms"][0]->senddate[0],$xml["sms"][0]->sendtime[0]);
	$stoptime=checktime($xml["sms"][0]->stopdate[0],$xml["sms"][0]->stoptime[0]);
	$total=get_count_from_sms($msg);
	$sql="INSERT INTO message (msg,userid,crontime,stoptime,type,tel,prov,uid,msgid,total) VALUES ";
	for($i=0;$i<sizeof($xml["sms_msg"]);$i++){
		if($i>0)
			$sql.=',';
		/* 添加到定时发送 */
		//echo $xml["sms_msg"][$i]->id[0], ' '; 
		//echo $xml["sms_msg"][$i]->text[0], ' ';
		//echo $xml["sms_msg"][$i]->rev[0], '<br>';
		$msg=addslashes($xml["sms_msg"][$i]->text[0]);
		$id=addslashes($xml["sms_msg"][$i]->id[0]);
		$tel=addslashes($xml["sms_msg"][$i]->rev[0]);
		
		$sql.="('$msg',$_SESSION[goip_userid],$crontime,$stoptime,3,'$tel','$prov','$uid','$id','$total')";	
	}
	if($i>0){
		$db->query($sql);
		do_cron($db,$crontime,$goipcronport,$i);
		
	}
	else{
		WriteErrMsg("Failed to parse the content, please check the content of xml file.");
	}
}
elseif($_POST['action']=='request'){
	//$uid=addslashes($_POST['uid']);
	//require_once('xmlClass.php');

	$file=file_get_contents($_FILES['xmlfile']['tmp_name']);
	$p = xml_parser_create();
	xml_parse_into_struct($p, $file, $vals, $index);
	xml_parser_free($p);

//print_r($vals);
	if( $vals[0]['tag']=='UID') $uid=$vals[0]['value'];
	//$data = implode("",file($_FILES['xmlfile']['tmp_name'])) or die("could not open XML input file"); 
	//$obj = new xml($data,"xml");
	//$uid=addslashes($_POST['uid']);
	//echo 1;
	//print_r($xml);
	//echo($xml[""][0]->uid[0]);
	//echo($uid);
	$query=$db->query("select `message.over`,message.msgid,message.time,sends.over as sover from message left join sends on sends.messageid=message.id where uid='$uid'");
/*
	$rs=$db->fetch_array($query);
	
	if(empty($rs[0])){
		WriteErrMsg("不存在该uid:$uid");
	}
*/
	$down="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	
  	$i=0;
	while($row=$db->fetch_array($query)) {
		if($i++==0){ //开始
			
			if($row['over']==0){ //未开始
				$down.="<response>\n";
				$down.="\t<status>3</status>\n";
				$down.="</response>\n";
				break;
			}
			elseif($row['over']==1){//正在发送
				$down.="<response>\n";
				$down.="\t<status>1</status>\n";
				$down.="</response>\n";
				break;
			}
			$down.="<msglst>\n";
		}
		
		$down.="\t<msg>\n";
		$down.="\t\t<id>$row[msgid]</id>\n";

			$dabuf=explode(' ',$row['time']);
			$down.="\t\t<date>$dabuf[0]</date>\n";
			$down.="\t\t<time>$dabuf[1]</time>\n";
			if($row['sover']==1){
				$down.="\t\t<status>Delivery</status>\n";
			}
			else{
				$down.="\t\t<status>Fail</status>\n";
			}

		$down.="\t</msg>\n";
		//$i++;
	}
	if($i==0){//无列表
		$down.="<response>\n";
		$down.="\t<status>2</status>\n"; //没有uid
		$down.="</response>\n";
	}
	elseif($i>1)
		$down.="</msglst>\n";
	//echo($down);
	$filesize=strlen($down);
	Header("Content-type: application/octet-stream");
	Header("Accept-Ranges: bytes");
	Header("Accept-Length: ".$filesize);
	Header("Content-Disposition: attachment; filename=R" . $uid.".xml");
	echo($down);
	//$e=ob_get_contents();
	//ob_end_clean();
}
elseif($_GET['action']=='request'){
	require_once('xmlfile.htm');
}

else{
	require_once('xmlfile.htm');
}
?>
