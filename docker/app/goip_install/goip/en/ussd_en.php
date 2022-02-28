<?php
require_once("session.php");
define("OK", true);
require_once("global.php");
if($_SESSION['goip_permissions'] > 1)	
	die("Permission denied!");


if($goipcronport)
	$port=$goipcronport;
else 
	$port=44444;

if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
	echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
	exit;
}
$query=$db->query("SELECT prov.*,goip.* FROM goip,prov where prov.id=goip.provider and goip.id=$_GET[id]");

if(($goiprow=$db->fetch_array($query)) ==NULL){
	die("Not find this goip id");
}

//print_r($_GET);
if(isset($_REQUEST['cmd'])){
ignore_user_abort(true);

$recvid=time();

for($i=0;$i<3;$i++){
	$read=array($socket);
	$buf="START $recvid $goiprow[host] $goiprow[port]\n";
	if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false){
        	echo ("sendto error".socket_strerror($socket) . "\n");
        	exit;
	}
	$err=socket_select($read, $write = NULL, $except = NULL, 5);
	if($err>0){		
		if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){
			echo("recvform error".socket_strerror($ret)."<br>");
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
if($i>=3) die("but cannot get response from process named \"goipcron\". please check this process.");

if($_GET['action'] == 'exit')
	$sendbuf="USSDEXIT ".$recvid." ".$goiprow[password];
else
	$sendbuf="USSD ".$recvid." ".$goiprow[password]." ".$_REQUEST['cmd'];

$socks[]=$socket;
$timer=2;
$timeout=10;
if (@socket_sendto($socket,$sendbuf, strlen($sendbuf), 0, "127.0.0.1", $port)===false)
                echo ("sendto error");
for(;;){
	$read=$socks;
	flush();
	if(count($read)==0)
		break;
	$err=socket_select($read, $write = NULL, $except = NULL, $timeout);
	if($err===false)
		echo "select error!";
	elseif($err==0){ //全体超时
		if(--$timer <= 0){
			echo "<script language=\"javascript\">alert('Timeout! Not get response from Goip')</script>"; 
			break;
		}
		if (@socket_sendto($socket,$sendbuf, strlen($sendbuf), 0, "127.0.0.1", $port)===false)
                	echo ("sendto error");
	}
	else {
		if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){
					echo("recvform error".socket_strerror($ret)."<br>");
					continue;
				}
		
		$comm=explode(" ",$buf);
		if($comm[0] == "USSD") {
			array_shift($comm);
			array_shift($comm);
			//$ussdmsg=$comm[2];
			$ussdmsg=implode(" ", $comm);
			//$i=0;
			$ussdmsg=htmlspecialchars($ussdmsg);
			$ussdmsg=str_replace("\n", "<br>", $ussdmsg);
			//echo $buf."<br>";
			//print_r($ussdmsg);
			break;
		}
		else if($comm[0] == "USSDERROR"){
			array_shift($comm);
			array_shift($comm);
			$errormsg=implode(" ",$comm);
			echo "<script language=\"javascript\">alert('error! $errormsg ')</script>";
			break;
		}
		else if($comm[0] == "USSDEXIT"){

			echo "<script language=\"javascript\">alert('USSD Disconnected! ')</script>";
			break;
		}
		
		
	}
}
$buf1="DONE $recvid";
if (@socket_sendto($socket,$buf1, strlen($buf1), 0, "127.0.0.1", $port)===false)
        echo ("sendto error");


}
//else {
	//if(!strncmp($buf, "ERROR", 5) || !strncmp($buf, "GSMERROR", 8))
		//echo "<script language=\"javascript\">alert('error! $buf ')</script>";
//echo $buf;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="../style.css" rel="stylesheet" type="text/css">
<title>USSD</title>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg">
    <td height="22" colspan="2" align="center"><strong>USSD</strong></td>
  </tr>
  <tr class="tdbg"> 
    <td wIdth="140" height="30"><strong>Navigation:</strong></td>
    <td height="30"><a href="goip.php" target=main>GoIP List</a>&nbsp;|&nbsp;<a href="goip.php?action=add" target=main>Add Goip</a></td>
  </tr>
</table>

<form method="post" action="ussd.php?id=<?php echo $_GET[id]?>" name="form1">
  <br>
  <br>
  <table wIdth="600" border="0" align="center" cellpadding="1" cellspacing="1" class="border" >
    <tr class="title"> 
      <td height="22" colspan="2"> <div align="center"><strong>GOIP(<?php echo $goiprow[name] ?>)USSD</strong></div></td>
    </tr>
    <tr>
	<td wIdth="180" align="right" class="tdbg"><strong>USSD Return Information:</strong></td>
      <td height="22"  class="tdbg"><?php echo $ussdmsg ?></td>
    </tr>
    <tr> 
      <td wIdth="180" align="right" class="tdbg"><strong>USSD Command:</strong></td>
      <td class="tdbg"><input type="input" id="cmd" name="cmd" >  &nbsp;&nbsp;&nbsp;&nbsp;  <a href="ussd.php?id=<?php echo $_GET[id]?>&cmd=1&action=exit" target=main onclick="return confirm('Sure to disconnect ussd?');">Disconnect</a></td>
    </tr>
    <tr>                                                                                                          
      <td height="40" colspan="2" align="center" class="tdbg"><input name="Id" type="hIdden" Id="Id" value="{$rs['id']}">
      		<input  type="submit" name="Submit" value="Send" style="cursor:hand;">
	</td>
    </tr>                                                                                                         
  </table>                                                                                                        
</form> 
</body>
</html>
<?php 
//}
?>
