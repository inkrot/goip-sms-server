<?php
require_once("session.php");
define("OK", true);
require_once("global.php");
if($_SESSION['goip_permissions'] > 1)	
die("Permission denied!");

?>
<html>                                                                                                            
<head>                                                                                                            
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">                                               
<link href="../style.css" rel="stylesheet" type="text/css">                                                          
<title>Call Forward</title>                                                                                               
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">   

<?php

if(empty($_GET[id]))
	die("NOT find goip id");
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
	die("Not find this goip");
}

//print_r($_GET);
	if(isset($_GET[mode])) $mode=$_GET[mode];
	else $mode=2;
	$reason=$_GET[reason];
	switch($reason){
		case 0:
			$type="Unconditional";
			break;
                case 1:
                        $type="Busy";
                        break;
                case 2:
                        $type="No Answered";
                        break;
                case 3:
                        $type="Out of Reach";
                        break;
		default:
			break;
	}
	$num=$_GET[num];
	$cfstate=$_GET[cfstate];
	$recvid=time();
	ignore_user_abort(true);

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
	if($i>=3) die("Cannot get response from process named \"goipcron\". please check this process.");

	$sendbuf="CF ".$recvid." ".$goiprow[password]." ".$reason." ".$mode." ".$num." ".$ftime;

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
			//echo $buf;
			//echo "<script language=\"javascript\">alert('recvbuf:$buf')</script>";
			$comm=explode(" ",$buf);
			
			if($comm[0] == "CFOK") {
				if($mode == 0){ //关闭
					$cfstate=0;
				}
				else if($mode == 1){
					$cfstate=1;
				}
				else if($mode == 2){
					/* 啥都不干*/
				}
				else if($mode == 3){
					$cfstate=1;
				}
				else if($mode == 4){
					$cfstate=0;
				}
				break;
			}
			else if($comm[0] == "CFERROR"){
				WriteErrMsg("Send command failed");  
				//echo "<script language=\"javascript\">alert('設置指令失敗')</script>"; 
				//echo "<meta http-equiv=refresh content=0;url=\"callf.php?id=$_GET[id]&reason=$reason\">";
				break;
			}
			else if($comm[0] == "CFSTATE"){
				if($comm[2] == 1)
					$cfstate=1;
				else 
					$cfstate=0;
				$num=$comm[3];
				break;
			}
			else {
				WriteErrMsg("Unknown Reply:".$buf);
				//echo "<script language=\"javascript\">alert('不明回覆:$buf')</script>";
				break;

			}


		}
	}
	$buf1="DONE $recvid";
	if (@socket_sendto($socket,$buf1, strlen($buf1), 0, "127.0.0.1", $port)===false)
		echo ("sendto error");


//if(!strncmp($buf, "ERROR", 5) || !strncmp($buf, "GSMERROR", 8))
//echo "<script language=\"javascript\">alert('error! $buf ')</script>";
//echo $buf;
?>
<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg">
    <td height="22" colspan="2" align="center"><strong><?php echo $type?> Call Forward</strong></td>
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
      <td height="22" colspan="2"> <div align="center"><strong>GOIP(<?php echo $goiprow[name] ?>)<?php echo $type?> Call Forward</strong></div></td>
    </tr>
    <tr>
	<td width=50% align="right" class="tdbg"><strong><?php echo $type?> Call Forward Status:</strong></td>
      <td height="22"  class="tdbg"><?php if($cfstate==1) echo "$num";else if($cfstate===0) echo "Disabled"?></td>
    </tr>
    <tr> 
      <td class="tdbg" colspan="2" align="center">Forward number<input type="input" id="num" name="num" value="<?php echo $num ?>">
<a href="" onclick="window.location='<?php echo "callf.php?id=$_GET[id]&reason={$reason}&mode=3&cfstate={$cfstate}&num='+encodeURIComponent(getElementById('num').value)" ?>; return false;">Modify</a>
 | <a href="<?php echo "callf.php?id=$_GET[id]&reason={$reason}&mode=4&cfstate={$cfstate}"?>" target=main >disable</a></td>
    </tr>
  </table>                                                                                                        
</form> 
</body>
</html>
<?php 
//}
?>
