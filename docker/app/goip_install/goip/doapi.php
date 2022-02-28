<?php

ob_end_flush();
set_time_limit(0);
ini_set("memory_limit", "200M");
echo str_pad(" ", 256);

//require_once("session.php");
//if($_SESSION['goip_permissions'] > 1)	
	//die("permissions error!");
define("OK", true);
require_once("global.php");

$count=0;
$line_html="";
$port=$goipcronport;
$report=array();
function ok_over($TERMID, $USSD_MSG, $value, $send)
{
        global $db;
	global $log_file;
	global $log_file1;
        global $count;
        global $line_html;
	global $report;
	$recharge_ok=0;
	echo "<br><font color='#00FF00'>CMD OK!(cmd:$USSD_MSG $send[number] $send[value] $value;goip:$TERMID)</font>";
        $USSD_MSG=$db->real_escape_string($USSD_MSG);
        $USSD_RETURN=$db->real_escape_string($USSD_RETURN);
	if($send[cmd]=="USSD"){
		if($send[card_id] && strstr($value, $send['recharge_ok_r'])){
			$recharge_ok=1;
			echo "<br><font color='#00FF00'>Recharge OK!(goip:$TERMID, card:$send[card])</font>";
		}
		else if($send[card_id] && !strstr($value, $send['recharge_ok_r']))
			echo "<br><font color='#FF0000'>Recharge ERROR!(goip:$TERMID, card:$send[card])</font>";
		if($send[card_id]){
			$db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$send[value]', USSD_RETURN='$value', INSERTTIME=now(),recharge_ok='$recharge_ok',type=2");
			$db->query("update recharge_card set used='$recharge_ok' where id='$send[card_id]'");
		}
		else
			$db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$send[value]', USSD_RETURN='$value', INSERTTIME=now()");
		$report[$TERMID]['TERMID']=$TERMID;
		$report[$TERMID]['cmd']=$send[value];
		$report[$TERMID]['value']=$value;
		$report[$TERMID]['error_msg']=$ERROR_MSG;
		$report[$TERMID]['goipid']=$send[goipid];
		$count++;
	
		file_put_contents($log_file, "OK($TERMID $USSD_MSG $value)\n", FILE_APPEND);
		file_put_contents($log_file1, "OK($TERMID $USSD_MSG $value)\n", FILE_APPEND);
	}
}

function error_over($TERMID, $USSD_MSG, $value, $ERROR_MSG, $send)
{
        global $db;
	global $log_file;
	global $log_file1;
        global $count;
        global $line_html;
	global $report;
	echo "<br><font color='#FF0000'>$ERROR_MSG(cmd:$USSD_MSG;goip:$TERMID)</font>";
        $USSD_MSG=$db->real_escape_string($USSD_MSG);
        $ERROR_MSG=$db->real_escape_string($ERROR_MSG);
	if($send[cmd]=="USSD"){
                if($send[card_id]){
			echo "<br><font color='#FF0000'>Recharge ERROR!(goip:$TERMID, card:$send[card])</font>";
			$db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$send[value]', ERROR_MSG='$ERROR_MSG', INSERTTIME=now()");
			$db->query("update recharge_card set used=0 where id='$send[card_id]'");
		}
		else
			$db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$send[value]', ERROR_MSG='$ERROR_MSG', INSERTTIME=now()");
		$report[$TERMID]['TERMID']=$TERMID;
		$report[$TERMID]['cmd']=$send[value];
		$report[$TERMID]['value']=$USSD_RETURN;
		$report[$TERMID]['error_msg']=$ERROR_MSG;
		$report[$TERMID]['goipid']=$send[goipid];
		$count++;

		file_put_contents($log_file, "ERROR($TERMID $USSD_MSG $ERROR_MSG)\n", FILE_APPEND);
		file_put_contents($log_file1, "ERROR($TERMID $USSD_MSG $ERROR_MSG)\n", FILE_APPEND);
	}
	else if($send[cmd]=="set_imei" && isset($send['imei_id'])){
		$db->query("update imei_db set used=0 where id='$send[imei_id]'");
	}
}

function ussd_add(&$goiprow, $prov, $ussd_cmd, $value, $number, $provname, $goipname)
{
	if($goipname) {
		$goiprow[$goipname][cmd][]=$ussd_cmd;
		$goiprow[$goipname][value][]=$value;
		$goiprow[$goipname][number][]=$number;
	}
	else if($provname) {
		if($prov[$provname])
		foreach($prov[$provname] as $grow){
			$goiprow[$grow][cmd][]=$ussd_cmd;
			$goiprow[$grow][value][]=$value;
			$goiprow[$grow][number][]=$number;
		}
	}else {
		foreach($goiprow as $key =>$grow){
			$goiprow[$key][cmd][]=$ussd_cmd;
			$goiprow[$key][value][]=$value;
			$goiprow[$key][number][]=$number;
		}
	}
}

function ussd_send(&$send)
{
	global $port;
	if( ++$send[resend] > 3){
		if($send[status] == "START") {
			$send[status]="OVER";
			error_over($send[goipname], $send[cmd], $send[value], "goipcron no response", $send);
		}
		elseif($send[status] == "SEND"){
			$send[status]="OVER";
			error_over($send[goipname], $send[cmd], $send[value], "goipcron no response from goip", $send);
		} 
	}
	else { 
		$send[sendtime]=time();
		if($send[status] == "START"){
			$buf="START $send[recvid] $send[host] $send[port]\n";
			//echo $buf;
			//echo $send[goipname];
			if (@socket_sendto($send[socket],$buf, strlen($buf), 0, "127.0.0.1", $port)===false){
				$errormsg = "ERROR sendto error:".socket_strerror($socket) . "\n";
				error_over($send[goipname], $send[cmd], $send[value], $errormsg, $send);
			}
		}
		elseif($send[status] == "SEND"){
/*
			if($send[cmd]=='USSD')
				$buf="$send[cmd] $send[recvid] $send[password] $send[value]";
			else if($send[value])
				$buf="$send[cmd] $send[recvid] $send[value] $send[password]";
			else
				$buf="$send[cmd] $send[recvid] $send[password]";
*/
			echo "<br>Send to $send[goipname]:".$send[msg];
			//echo $send[goipname];
			if (@socket_sendto($send[socket],$send[msg], strlen($send[msg]), 0, "127.0.0.1", $port)===false){
				$errormsg = "ERROR sendto error".socket_strerror($socket) . "\n";
				error_over($send[goipname], $send[cmd], $send[value], $errormsg, $send);
			}
		}
	}
}

function ussd_start(&$sendrow, &$goiprow, &$socks)
{
	global $log_file;
	global $db;
	$log_flag=1;
	$recvid=time();
	$prefix=0;
	foreach($goiprow as $goip){
		$send[cmd] = @array_shift($goip[cmd]);
		$send[value] = @array_shift($goip[value]);
		$send[number] = @array_shift($goip[number]);
		//echo $send[value]."<br>";
		if(!$send[value] && $send[cmd]=="set_imei") $send[value]=num_rand(15);
		if(!$send[cmd]) continue;
		if($send[cmd]=='USSD'){
			if(!$send[value]) continue;
			if($log_flag){
				file_put_contents($log_file, "");
				$log_flag=0;
			}
			$comm=explode("$",$send[value]);
			if(!$comm[1]) $comm=explode("?",$send[value]);
			if($comm[1]){
				//echo $comm[0].$comm[1]." 111 <br>";
				$query=$db->query("select * from recharge_card where recharge_card.prov_id=$goip[provider] and recharge_card.used=0 order by use_time, recharge_card.id limit 1");
				if($row=$db->fetch_array($query)) {
					$send[value]=$comm[0].$row[card].$comm[1];
					$send[card_id]=$row[id];
					$send[card]=$row[card];
					$send[recharge_ok_r]=$goip[recharge_ok_r];
					$db->query("update recharge_card set used=2, use_time=now(), goipid=$goip[goipid] where id=$row[id]");
				}
				else {
					echo "<br><font color='#FF0000'>cannot find recharge card for goip:$goip[name],provid:$goip[provider]</font>";
					continue;
				}
			}
		}else if($send[cmd]=='set_imei_db'){
			$query=$db->query("select * from imei_db where imei_db.used!=1 order by imei_db.id limit 1");
			if($row=$db->fetch_array($query)) {
				$send[cmd]="set_imei";
				$send[value]=$row['imei'];
				$send[imei_id]=$row['id'];
				$db->query("update imei_db set used=1,goipid='$goip[goipid]',goipname='$goip[name]'  where id=$row[id]");
			}
			else {
				echo "<br><font color='#FF0000'>cannot find IMEI for goip:$goip[name],provid:$goip[provider]</font>";
				continue;
			}
		}
		//echo "$send[cmd] $send[recvid] $send[value] $send[password]";
		//print_r($goip);
		$send[goipname] = $goip[name];
		$send[goipid] = $goip[goipid];
		$send[host] = $goip[host];
		$send[port] = $goip[port];
		$send[password] = $goip[password];
		$send[resend] = 0;
		$send[status] = "START";
		$prefix++;
		$send['recvid'] = $prefix*100000+$recvid;

		if($send[cmd]=='USSD')
			$send[msg]="$send[cmd] $send[recvid] $send[password] $send[value]";
		else if($send[cmd]=='enable_moudle'){
			$send[msg]="module_ctl_i $send[recvid] 1 $send[password]";
		}
		else if($send[cmd]=='disable_moudle'){
			$send[msg]="module_ctl_i $send[recvid] 0 $send[password]";
		}
		else if($send[cmd]=='SMS'){
			if(empty($send[number]) || empty($send[value])) die("number or messgae empty!");
			$send[msg]="$send[cmd] $send[recvid] 1 $send[password] $send[number] $send[value]";
		}
		else if(isset($send[value]))
			$send[msg]="$send[cmd] $send[recvid] $send[value] $send[password]";
		else
			$send[msg]="$send[cmd] $send[recvid] $send[password]";
		if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
			$errormsg = "ERROR socket_create() failed: reason: " . socket_strerror($socket) . "\n";
			//echo $errormsg;
			error_over($send[name], $send[cmd], $send[value], $errormsg, $send);
			continue;
		}
		$send['socket'] = $socket;
		$sendrow[]=$send;
		$socks[]=$socket;
		//print_r($send);
		ussd_send($send);
		
	}
	//die;
	echo "<br><li>Start to send CMD, total:$prefix</li>"; 
}

function get_id()
{
	$num=$_REQUEST['boxs'];
	for($i=0;$i<$num;$i++)
	{               
		if(!empty($_REQUEST["Id$i"])){
			if($id=="")
				$id=$_REQUEST["Id$i"];
			else
				$id=$_REQUEST["Id$i"].",$id";
		}       
	}       
	if($_REQUEST['rstr']) {
		if($id=="")
			$id=$_REQUEST['rstr'];
		else
			$id=$_REQUEST['rstr'].",$id";

	}
	return $id;
}


if($_REQUEST['all']!=1 && !isset($_REQUEST['goipid']) && !$_REQUEST['goipname']) die("ERROR:not set goipid or goipname.");
if(!get_magic_quotes_gpc()){
        $_REQUEST[USERNAME]=addslashes($_REQUEST[USERNAME]);
        $_REQUEST[PASSWORD]=addslashes($_REQUEST[PASSWORD]);
}
session_start();
if(!isset($_SESSION['goip_username'])){
        $rs=$db->fetch_array($db->query("SELECT id FROM user WHERE username='".$_REQUEST[USERNAME]."' and password='".md5($_REQUEST[PASSWORD])."'"));
        if(empty($rs[0])){
                require_once ('login.php');
                exit;
        }
        $userid=$rs[0];
}       
else $userid=$_SESSION[goip_userid];


if(!$_REQUEST['cmd'])
        die("please input cmd!");
if($_REQUEST['all']==1) {
	$query=$db->query("SELECT *, goip.id as goipid from goip,prov where goip.provider=prov.id and alive=1 order by prov");
}
else if($_REQUEST['goipname']){
	//$ID=get_id();
	//if(!$ID){
	//	die("do not select id!");
	//}
	$query=$db->query("SELECT *, goip.id as goipid from goip,prov where goip.provider=prov.id and alive=1 and goip.name='$_REQUEST[goipname]' order by prov");
}else {

	$query=$db->query("SELECT *, goip.id as goipid from goip,prov where goip.provider=prov.id and alive=1 and goip.id in ($_REQUEST[goipid]) order by prov");
}

$pflag=0;
while($row=$db->fetch_assoc($query)) {
	if(!$goiprow[$row[name]]){
		//echo "$row[port], $row[host]<br>";
		$pflag = 1;
		$goiprow[$row[name]] = $row;  //goip列表
		$prov[$row[prov]][]=$row[name]; //每个provide下有哪些goip
	}
}
if(!$pflag)
	die("do not have that provider in database!");

//echo "11111:$return[Sheet1][$row][0], $return[Sheet1][$row][1], $return[Sheet1][$row][2]";
if(isset($_REQUEST[value])) ussd_add($goiprow, $prov, $_REQUEST[cmd], $_REQUEST[value], $_REQUEST[number]);
else ussd_add($goiprow, $prov, $_REQUEST[cmd], $_REQUEST[msg], $_REQUEST[number]);
ussd_start($sendrow, $goiprow, $socks);
wait_answer($socks, $sendrow, $goiprow); 
		
function wait_answer($socks, $sendrow, $goiprow){
	global $db;
	global $count;
	global $line_html;
	global $report;
	if(!$socks) die("over");
	for(;;){

		flush();
		$read=$socks;
		$timeout=5;
		$err=socket_select($read, $write = NULL, $except = NULL, $timeout);
		if($err===false)
			die("select error!");
		elseif($err==0){ //全体超时
			foreach($sendrow as $the => $send){
				ussd_send($sendrow[$the]);
			}
		}
		else { //可读
			foreach($read as $socket){
				unset($buf);
				if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){
					//echo("recvform error".socket_strerror($ret)."<br>");
					continue;
				}
				/*开始处理*/
				foreach($sendrow as $the => $send){
					if($send[socket] == $socket)
					break;
				}
				if($send[socket] != $socket) continue;
				//$goip=$goiprow[$sendrow[goipname]];
				$comm=explode(" ",$buf);
				if($comm[0]=="OK" && $send[status]=="START"){
					$sendrow[$the][status] = "SEND";
					$sendrow[$the][resend] = 0 ;
					ussd_send($sendrow[$the]);
				}
				else if($send[status] == "SEND" && $send[recvid]==$comm[1]){
					if($comm[0] == "USSD"){
						array_shift($comm);
						array_shift($comm);
						$ussdmsg=implode(" ", $comm);
						if($ussdmsg != "USSD send failed!"){
							$ussdmsg=str_replace("@", "", $ussdmsg);
							$ussdmsg=mysql_real_escape_string($ussdmsg);
							//if(!$debug) echo "OK $ussdmsg";
							$sendrow[$the][status]="OVER";
							ok_over($send[goipname], $send[cmd], $ussdmsg,$send);
						}
						else {
							$sendrow[$the][status]="OVER";
							error_over($send[goipname], $send[cmd], $send[value], $ussdmsg, $send);
						}
					}
					else if($comm[0] == "WAIT"){
						$sendrow[$the][resend]=1;
						//ussd_send($sendrow[$the]);
					}
					else if($comm[0] == "reset_remain_time"){
						$sendrow[$the][status]="OVER";
						ok_over($send[goipname], $send[cmd], $send[value], $send);	
					}
					else if($comm[0] == "ERROR"){
						$sendrow[$the][status]="OVER";
						error_over($send[goipname], $send[cmd], $send[value], $comm[2], $send);
					}

					//else if($comm[0] == "set_imei"){
					else {
						$sendrow[$the][status]="OVER";
						ok_over($send[goipname], $send[cmd], $send[value], $send);
					}
				}
				
			}
		}
		$nowtime=time();
		foreach($sendrow as $the => $send){
			if($send[sendtime]+5 < $nowtime)
				ussd_send($sendrow[$the]);
		}
		$overflag=1;
		foreach($sendrow as $send){
			if($send[status] != "OVER") $overflag=0;
		}
		if($overflag ) {
			echo ("<br>All CMD are over");
			if($_REQUEST[cmd]=='USSD'){
			}
			die;
		}
		
	}
}

?> 
