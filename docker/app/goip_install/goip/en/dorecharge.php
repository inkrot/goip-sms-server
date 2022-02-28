<?php
define("OK", true);
require_once("global.php");
session_start();

if(!get_magic_quotes_gpc()){
        $_REQUEST[USERNAME]=addslashes($_REQUEST[USERNAME]);
        $_REQUEST[PASSWORD]=addslashes($_REQUEST[PASSWORD]);
}
//print_r($_GET);
//print_r($_REQUEST);
//die;
if($_REQUEST[Memo]) $_REQUEST[Memo]=$_REQUEST[Memo];
if(!isset($_SESSION['goip_username'])){
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
<link href="../style.css" rel="stylesheet" type="text/css">
<title>Recharging</title>
</head>
<body>
<?php
ignore_user_abort(true);
ob_end_flush();
set_time_limit(0);
ini_set("memory_limit", "1024M");
echo str_pad(" ", 256);
if($goipcronport)
	$sport=$goipcronport;
else 
	$sport=44444;


function ok_over($TERMID, $USSD_MSG, $USSD_RETURN)
{
        global $db;
        $USSD_MSG=$db->real_escape_string($USSD_MSG);
        $USSD_RETURN=$db->real_escape_string($USSD_RETURN);
	$db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$USSD_MSG', USSD_RETURN='$USSD_RETURN', INSERTTIME=now()");
}                                                                                                                 
                                                                                                                  
function error_over($TERMID, $USSD_MSG, $ERROR_MSG)                                                               
{                                                                                                                 
        global $db;
        $USSD_MSG=$db->real_escape_string($USSD_MSG);
        $ERROR_MSG=$db->real_escape_string($ERROR_MSG);
	$db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$USSD_MSG', ERROR_MSG='$ERROR_MSG', INSERTTIME=now()");
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


if($_REQUEST['Submit']=="Recharge"){
        global $db;
	global $sport;
	//echo "1212";
	$query=$db->query("SELECT * from goip where id='".$_REQUEST['send_goip']."' and alive=1");
	if($row=$db->fetch_assoc($query)) {
		$ip=$row['host'];
		$port=$row['port'];
		$password=$row['password'];
		$sid=$row['id'];
	}
	else $error_msg="Send GoIP Error or not alive";

	if($_REQUEST['chkAll0']) {
		$query=$db->query("SELECT * from goip where id!='".$_REQUEST['send_goip']."' order by id");
	}
	else {
		if($_REQUEST[id]) $ID=$_REQUEST[id];
		else $ID=get_id();
		if(!$ID){
			die("do not select id!");
		}
		$query=$db->query("SELECT * from goip where goip.id in ($ID) order by id");
	}
	while($row=$db->fetch_assoc($query)) {
		$try_c++;
		if(!$row['num']) echo "line $row[name] not set number<br>";
		elseif($row['num']) {
			$cmd=str_replace("!", $row['num'], $_REQUEST['cmd']);

			if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
				$errormsg = "ERROR socket_create() failed: reason: " . socket_strerror($socket) . "\n";
				echo $errormsg;
				//error_over($_REQUEST[TERMID], $_REQUEST['USSDMSG'], $errormsg);
				exit;
			}
			$recvid=mt_rand(10000,100000000);
			for($i=0;$i<3;$i++){             
				$read=array($socket);                               
				$buf="START $recvid $ip $port\n";
				if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $sport)===false){
					$errormsg = "ERROR sendto error".socket_strerror($socket) . "\n";
					echo $errormsg;
					//error_over($_REQUEST[TERMID], $_REQUEST['USSDMSG'], $errormsg);                                       
					exit;                            
				}       
				$err=socket_select($read, $write = NULL, $except = NULL, 5);
				if($err>0){                             
					if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip1,$port1))==false){
						if($debug) echo("recvform error".socket_strerror($ret)."<br>");
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
			if($i>=3) {
				//error_over($_REQUEST[TERMID], $_REQUEST['USSDMSG'], "goipcron no response");
				//if($debug) die("goipcron no response");
				echo "ERROR goipcron no response";
				exit;
			}
			
			$sendbuf="USSD ".$recvid." ".$password." ".$cmd;
			$socks[]=$socket;
			$ussd_step=1;
			$timer=2; 
			$timeout=5;                                                                        
			if (@socket_sendto($socket,$sendbuf, strlen($sendbuf), 0, "127.0.0.1", $sport)===false)
				echo ("ERROR sendto error");   
			for(;;){
				$read=$socks;                                                                                             
				flush();                                                                                                  
				if(count($read)==0)                                                                                       
					break;                                                                                            
				$err=socket_select($read, $write = NULL, $except = NULL, $timeout);                                       
				if($err===false)                                                                                          
					echo "ERROR select error!";                                                                       
				elseif($err==0){ //全体超时                                                                               
					if(--$timer <= 0){                                                                                
						//if($debug) echo "<script language=\"javascript\">alert('Timeout! Not get response from Goip')</script>";  
						$errormsg = "ERROR term no response";                            
						echo "line $row[name] $errormsg<br>";                    							
						break;                                                                                    
					}                                                                                                 
					if (@socket_sendto($socket,$sendbuf, strlen($sendbuf), 0, "127.0.0.1", $sport)===false)         

						echo ("ERROR sendto error");
				}
				else {
					if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip1,$port1))==false){
						echo("recvform error".socket_strerror($ret)."<br>");
						continue;
					}

					$comm=explode(" ",$buf);
					if($comm[0] == "USSD") {
						array_shift($comm);
						array_shift($comm);
						//$ussdmsg=$comm[2];
						$ussdmsg=implode(" ", $comm);
						if($ussdmsg != "USSD send failed!"){
							$ussdmsg=str_replace("@", "", $ussdmsg);
							$ussdmsg=mysql_real_escape_string($ussdmsg);
							ok_over($sid, $cmd, $ussdmsg);
							echo "line $row[name] $cmd OK $ussdmsg<br>";
							if($ussd_step==1){
								$cmd="1";
								$sendbuf="USSD ".$recvid." ".$password." ".$cmd;
								$timer=2;
								$ussd_step=2;
								if (@socket_sendto($socket,$sendbuf, strlen($sendbuf), 0, "127.0.0.1", $sport)===false)
									echo ("ERROR sendto error");
								continue;
							}
							else {
								$ok_c++;
							}
						}
						else $errormsg="ERROR ".$ussdmsg;
						$ussdmsg=htmlspecialchars($ussdmsg);
						$ussdmsg=str_replace("\n", "<br>", $ussdmsg);
						break;
					}
					else if($comm[0] == "USSDERROR"){
						array_shift($comm);
						array_shift($comm);
						$errormsg=implode(" ",$comm);
						$errormsg="ERROR $errormsg";
						echo "line $row[name] $cmd $errormsg <br>";
						error_over($sid, $cmd, $errormsg);
						break;                                                                                    
					}                                                                                                 
					else if($comm[0] == "USSDEXIT"){                                                                  
						break;                                                                                    
					}                                                                                

				}

			}	
			//else {echo "line $row['name'] not set number<br>";}
		}
	}

	echo "All Recharge done! Result:{$ok_c}/{$try_c}";
	echo "<br><br>";
	echo "<a href=ussdinfo.php target=main><font size='2'>Click me to check details.</font></a>";
}
?>
</body>
</html>
