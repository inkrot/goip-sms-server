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
	echo "Not find this id";
}

$recvid=time();
$buf="START $recvid $goiprow[host] $goiprow[port]\n";
if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false){
	echo ("sendto error".socket_strerror($socket) . "\n");
	exit;
}
for($i=0;$i<3;$i++){
	$read=array($socket);
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
if($i>=3) die("goipcron 服务进程没有响应");
//echo $_GET['value'];
if(isset($_GET['value']))
	$_GET['value']=' '.$_GET['value'];
$buf=$_GET['cmd']." $recvid".$_GET['value']." ".$goiprow[password];
if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
	echo ("sendto error");

$socks[]=$socket;
$timer=3;
$timeout=30;
$moudle_down=NULL;

ob_end_flush();
ob_implicit_flush(1); 
set_time_limit(0);
ini_set("memory_limit", "1024M");
echo str_pad(" ", 256);
$last_time=time();
for(;;){
	$read=$socks;
	flush();
	if(count($read)==0)
		break;
	$err=socket_select($read, $write = NULL, $except = NULL, $timeout);
	if($err===false)
		echo "select error!";
	elseif($err==0){ //全体超时
		$buf="ATALIVE $recvid $goiprow[password]\n";
		if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
			echo ("sendto error");
	}
	else {
		if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))===false){
			echo("recvform error".socket_strerror($ret)."<br>");
			continue;
		}
		echo str_repeat(" ",4096); 
		echo $buf."<br>\n";
		$now_time=time();
		if($now_time-30 > $last_time){
			$last_time=$now_time;
			$buf="ATALIVE $recvid $goiprow[password]\n";
			if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
				echo ("sendto error");
		}
		//$comm=explode(";",$buf);
		//sscanf($buf, "%*[^:]:%*[^;];%*[^:]:%[^;];%*[^:]:%[^;];%*[^:]:%[^;];%*[^:]:%[^;];%*[^:]:%[^;];%*[^:]:%[^;];%*[^:]:%[^;]", $gsm_num, $exp_time, $remain_time, $gsm_state, $imei,$out_interval,$moudle_down);
		//echo $buf;
		//break;
	}
}
$buf1="DONE $recvid";
if (@socket_sendto($socket,$buf1, strlen($buf), 0, "127.0.0.1", $port)===false)
        echo ("sendto error");

if(strncmp($buf, "GSM", 3) && strncmp($buf, "ERROR", 5)){
	echo "<script language=\"javascript\">alert('OK! $buf ')</script>";
	echo "<meta http-equiv=refresh content=0;url=\"state.php?id=$_GET[id]&cmd=GSM\">";
}

else {
	if(!strncmp($buf, "ERROR", 5) || !strncmp($buf, "GSMERROR", 8))
		echo "<script language=\"javascript\">alert('error! $buf ')</script>";
//echo $buf;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css">
<title>Goip State</title>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg">
    <td height="22" colspan="2" align="center"><strong>Goip状态信息</strong></td>
  </tr>
  <tr class="tdbg"> 
    <td wIdth="100" height="30"><strong>管理导航:</strong></td>
    <td height="30"><a href="goip.php" target=main>参数管理</a>&nbsp;|&nbsp;<a href="goip.php?action=add" target=main>添加机器</a></td>
  </tr>
</table>

<form method="post" action="state.php?" name="form1" onSubmit="javascript:return check_pw();">
  <br>
  <br>
  <table wIdth="400" border="0" align="center" cellpadding="2" cellspacing="1" class="border" >
    <tr class="title"> 
      <td height="22" colspan="3"> <div align="center"><strong>GOIP(<?php echo $goiprow[name] ?>)状态信息</strong></div></td>
    </tr>
    <tr> 
      <td wIdth="200" align="right" class="tdbg"><strong>SIM卡号码:</strong></td>
      <td class="tdbg"><input type="input" id="gsm_num" name="gsm_num" value=<?php echo(($gsm_num == "<NULL>")?"":$gsm_num);?>></td>
      <td width="100" class="tdbg"><a href="" onclick="window.location='?id=<?php echo $_GET[id];?>&cmd=set_gsm_num&value='+getElementById('gsm_num').value;return false;" >修改</a></td>
    </tr>
    <tr>
      <td wIdth="200" align="right" class="tdbg"><strong>线路状态:</strong></td>
      <td class="tdbg"><?php echo $gsm_state ?></td>
      <td class="tdbg"><a href="state.php?id=<?php echo $_GET[id]?>&cmd=svr_drop_call" target=main onclick="return confirm('确认要终止通话?');">终止通话</a></td>
    </tr>
    <tr>
      <td wIdth="200" align="right" class="tdbg"><strong>总通话限制时间(分钟):</strong></td>
      <td class="tdbg"><input type="input" id="exp_time" name="exp_time" value=<?php echo $exp_time ?>></td></td>
      <td class="tdbg"><a href="" onclick="window.location='?id=<?php echo $_GET[id];?>&cmd=set_exp_time&value='+getElementById('exp_time').value;return false;" target=main>修改</a></td>
    </tr>
    <tr>
      <td wIdth="200" align="right" class="tdbg"><strong>剩余通话时间:</strong></td>
      <td class="tdbg"><?php echo $remain_time ?></td>
      <td class="tdbg"><a href="state.php?id=<?php echo $_GET[id]?>&cmd=reset_remain_time" target=main onclick="return confirm('确认重置剩余通话时间?')">重置</a></td>
    </tr>
    <tr>
      <td wIdth="200" align="right" class="tdbg"><strong>呼叫PSTN间隔(秒):</strong></td>
      <td class="tdbg"><input type="input" id="out_interval" name="out_interval" value=<?php echo $out_interval ?>></td></td>
      <td class="tdbg"><a href="" onclick="window.location='?id=<?php echo $_GET[id];?>&cmd=set_out_call_interval&value='+getElementById('out_interval').value;return false;" target=main>修改</a></td>
    </tr>
    <tr> 
      <td wIdth="200" align="right" class="tdbg"><strong>IMEI号:</strong></td>
      <td class="tdbg"><input type="input" id="imei" name="imei" value=<?php echo(($imei == "<NULL>")?"":$imei);?>></td>
      <td width="100" class="tdbg"><a href="" onclick="window.location='?id=<?php echo $_GET[id];?>&cmd=set_imei&value='+getElementById('imei').value;return false;" >修改</a></td>
    </tr>
    <tr>
      <td wIdth="200" align="right" class="tdbg"><strong>模块控制:</strong></td>
      <td class="tdbg"><?php if($moudle_down==1) echo "DOWN"; elseif($moudle_down==="0") echo "UP";?></td>
      <td width="200" class="tdbg"><a href="?id=<?php echo $_GET[id];?>&cmd=module_ctl&value=0" >DOWN</a> <a href="?id=<?php echo $_GET[id];?>&cmd=module_ctl&value=1" >UP</a></td>
    </tr>
    <tr>                                                                                                          
      <td height="40" colspan="3" align="center" class="tdbg"><input name="Id" type="hIdden" Id="Id" value="{$rs['id']}">
	<a href="#" target=main onclick="location.reload()">刷新</a>
        <a href="state.php?id=<?php echo $_GET[id]?>&cmd=svr_reboot_module" target=main onclick="return confirm('确认重启此条线路?')">重启线路</a>
	<a href="state.php?id=<?php echo $_GET[id]?>&cmd=svr_reboot_dev" onclick="return confirm('确认重启此设备?');" target=main>重启设备</a>
      </td>
    </tr>                                                                                                         
  </table>                                                                                                        
</form> 
</body>
</html>
<?php 
}
?>
