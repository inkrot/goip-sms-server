<?php
define("OK", true);
require_once("global.php");
session_start();

if(!$_REQUEST[goipid] && !$_REQUEST[goipname]) die("ERROR:not set goipid or goipname.");
if(!get_magic_quotes_gpc()){
	$_REQUEST[USERNAME]=addslashes($_REQUEST[USERNAME]);
	$_REQUEST[PASSWORD]=addslashes($_REQUEST[PASSWORD]);
}

if(!isset($_SESSION['goip_username'])){
	$rs=$db->fetch_array($db->query("SELECT id FROM user WHERE username='".$_REQUEST[USERNAME]."' and password='".md5($_REQUEST[PASSWORD])."'"));
	if(empty($rs[0])){
		require_once ('login.php');
		exit;
        }
	$userid=$rs[0];
}
else $userid=$_SESSION[goip_userid];

if($goipcronport)
        $port=$goipcronport;
else 
        $port=44444;

if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
        echo "ERROR:socket_create() failed: reason: " . socket_strerror($socket) . "\n";
        exit;
}
if($_REQUEST[goipid])
	$query=$db->query("SELECT prov.*,goip.*,goip.id as id FROM goip,prov where prov.id=goip.provider and goip.id=$_REQUEST[goipid]");
else 
	$query=$db->query("SELECT prov.*,goip.*,goip.id as id FROM goip,prov where prov.id=goip.provider and goip.name='$_REQUEST[goipname]'");
if(($goiprow=$db->fetch_array($query)) ==NULL){
        die("ERROR:Not find this goip line.");
}       
 
$recvid=time()+$goiprow[id]*10000;

       
$buf="START $recvid $goiprow[host] $goiprow[port]\n";
if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false){
        echo ("ERROR:sendto error".socket_strerror($socket) . "\n");
        exit;   
}          
for($i=0;$i<3;$i++){    
        $read=array($socket);   
        $err=socket_select($read, $write = NULL, $except = NULL, 5);
        if($err>0){      
                if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){
                        echo("ERROR:recvform error".socket_strerror($ret)."<br>");
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
if($i>=3) die("ERROR:Cannot get response from process named \"goipcron\"");

if(isset($_REQUEST['value']))
        $_REQUEST['value']=' '.$_REQUEST['value'];
$buf=$_REQUEST['cmd']." $recvid".$_REQUEST['value']." ".$goiprow[password];


if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
        echo ("ERROR:sendto error");

$socks[]=$socket;
$timer=3;
$timeout=5;

for(;;){
	$read=$socks;
	flush();
	if(count($read)==0)
		break;
	$err=socket_select($read, $write = NULL, $except = NULL, $timeout);
	if($err===false)
		echo "ERROR:send error";
	elseif($err==0){ //全体超时
		if(--$timer <= 0){
			echo "ERROR:";
			break;
		}
	}
	else {
		if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){
					//echo("recvform error".socket_strerror($ret)."<br>");
					continue;
				}
		
		$comm=explode(";",$buf);
		if(!strncmp($buf, "GSM", 3)) sscanf($buf, "%*[^:]:%*[^;];%*[^:]:%[^;];%*[^:]:%[^;];%*[^:]:%[^;];%*[^:]:%[^;];%*[^:]:%[^;];%*[^:]:%[^;];%*[^:]:%[^;]", $gsm_num, $exp_time, $remain_time, $gsm_state, $imei,$out_interval,$moudle_down);
		//echo $buf;
		break;
		
		
	}
}
$buf1="DONE $recvid";
if (@socket_sendto($socket,$buf1, strlen($buf), 0, "127.0.0.1", $port)===false)
        echo ("sendto error");

if(strncmp($buf, "GSM", 3) && strncmp($buf, "ERROR", 5)){
	echo "OK";
}
else {
	if(!strncmp($buf, "ERROR", 5) || !strncmp($buf, "GSMERROR", 8))
		echo "ERROR:$buf";
}
?>
