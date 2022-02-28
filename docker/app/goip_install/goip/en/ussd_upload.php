
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>upload file</title>
<link href="style.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	background-color: #D6DFF7;
}
.invisible{display:none;}
-->
</style></head>

<SCRIPT language=javascript>
	
function addgroup(checked)
{
	if(checked.checked){
		checked.value=1;
		document.getElementById("selectgroup").style.display="none";
		document.getElementById("add").style.display="block";
	}
	else {
		checked.value=0;
		document.getElementById("selectgroup").style.display="block";
		document.getElementById("add").style.display="none";
	}
		
}

</SCRIPT>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<?php

set_time_limit(0);
ini_set("memory_limit", "200M");
echo str_pad(" ", 256);

$log_file="../log/ussd_reply.log";
$log_file1="../log/ussd_reply".date("Ymd-His").".log";
function check_prov(&$data, $prov, $prov0, &$msg, $codeflag = 0 )
{
	if($codeflag) $data=iconv("GB2312//IGNORE","UTF-8", $data);
	$tmpdata=$data;
	if(!$data){
		//echo "sdsdsz:$prov0";
		$data=$prov0;
	}
	$data=$prov[$data];
	if(!$data){
		$msg.='<br><li>不存在服务商: '.$tmpdata.'</li>';
		return 0;					
	}
	return 1;
}

require_once("session.php");
if($_SESSION['goip_permissions'] > 1)	
	die("需要admin权限！");	
define("OK", true);
require_once("global.php");

$port=44444;
function ok_over($TERMID, $USSD_MSG, $USSD_RETURN)
{
        global $db;
	global $log_file;
	global $log_file1;
	echo "****<br><font color='#00FF00'>USSD OK!(cmd:$USSD_MSG;goip:$TERMID)</font>USSD return:<br>$USSD_RETURN<br>****<br>";
        $USSD_MSG=$db->real_escape_string($USSD_MSG);
        $USSD_RETURN=$db->real_escape_string($USSD_RETURN);
        $db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$USSD_MSG', USSD_RETURN='$USSD_RETURN', INSERTTIME=now()");
	file_put_contents($log_file, "OK($TERMID $USSD_MSG $USSD_RETURN)\n", FILE_APPEND);
	file_put_contents($log_file1, "OK($TERMID $USSD_MSG $USSD_RETURN)\n", FILE_APPEND);
}

function error_over($TERMID, $USSD_MSG, $ERROR_MSG)
{
        global $db;
	global $log_file;
	global $log_file1;
	echo "<font color='#FF0000'>$ERROR_MSG(cmd:$USSD_MSG;goip:$TERMID)</font><br>";
        $USSD_MSG=$db->real_escape_string($USSD_MSG);
        $ERROR_MSG=$db->real_escape_string($ERROR_MSG);                                                           
        $db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$USSD_MSG', ERROR_MSG='$ERROR_MSG', INSERTTIME=now()");
	file_put_contents($log_file, "ERROR($TERMID $USSD_MSG $ERROR_MSG)\n", FILE_APPEND);
	file_put_contents($log_file1, "ERROR($TERMID $USSD_MSG $ERROR_MSG)\n", FILE_APPEND);
}

function ussd_add(&$goiprow, $prov, $ussd_cmd, $provname, $goipname)
{
	if($goipname) $goiprow[$goipname][cmd][]=$ussd_cmd;
	else if($provname) {
		//echo "lala:$provname";
		if($prov[$provname])
		foreach($prov[$provname] as $grow){
			$goiprow[$grow][cmd][]=$ussd_cmd;
		}
	}
}


function ussd_send(&$send)
{
	global $port;
	if( ++$send[resend] > 3){
		if($send[status] == "START") {
			$send[status]="OVER";
			error_over($send[goipname], $send[cmd], "goipcron no response");
		}
		elseif($send[status] == "SEND"){
			$send[status]="OVER";
			error_over($send[goipname], $send[cmd], "goipcron no response from goip");
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
				error_over($send[goipname], $send[cmd], $errormsg);
			}
		}
		elseif($send[status] == "SEND"){
			$buf="USSD $send[recvid] $send[password] $send[cmd]";
			//echo $buf;
			//echo $send[goipname];
			if (@socket_sendto($send[socket],$buf, strlen($buf), 0, "127.0.0.1", $port)===false){
				$errormsg = "ERROR sendto error".socket_strerror($socket) . "\n";
				error_over($send[goipname], $send[cmd], $errormsg);
			}
		}
	}
}

function ussd_start(&$sendrow, &$goiprow, &$socks)
{
	global $log_file;
	$recvid=time();
	$prefix=0;
	file_put_contents($log_file, "");
	foreach($goiprow as $goip){
		$send[cmd] = @array_shift($goip[cmd]);
		if(!$send[cmd]) continue;
		//print_r($goip);
		$send[goipname] = $goip[name];
		$send[host] = $goip[host];
		$send[port] = $goip[port];
		$send[password] = $goip[password];
		$send[resend] = 0;
		$send[status] = "START";
		if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
        		$errormsg = "ERROR socket_create() failed: reason: " . socket_strerror($socket) . "\n";
			//echo $errormsg;
			error_over($send[name], $send[cmd], $errormsg);
			continue;
		}
		$prefix++;
		$send['recvid'] = $prefix*100000+$recvid;
		$send['socket'] = $socket;
		$sendrow[]=$send;
		$socks[]=$socket;
		//print_r($send);
		ussd_send($send);
		
	}
	echo "<br><li>Start to send USSD, total:$prefix</li><br>"; 
}

if ( $_POST["action"]=="mingsenupload")
{
	//echo "upload";
	
	$attach_name=$_FILES["img1"]['name'];
	$attach_size=$_FILES["img1"]['size'];
	$attachment=$_FILES["img1"]['tmp_name'];
	
	$db_uploadmaxsize='5120000';
	$db_uploadfiletype='txt cfg';
	$attachdir="upload";

	if(!$attachment || $attachment== 'none'){
		showerror('upload_content_erro');
	} elseif(function_exists('is_uploaded_file') && !is_uploaded_file($attachment)){
		showerror('upload_content_erro');
	} elseif(!($attachment && $attachment['error']!=4)){
		showerror('upload_content_erro');
	}
	if ($attach_size>$db_uploadmaxsize){
		showerror("upload_type_error:too large");
	}
	
	$available_type = explode(' ',trim($db_uploadfiletype));
	$attach_ext = substr(strrchr($attach_name,'.'),1);
	$attach_ext=strtolower($attach_ext);
//	if($attach_ext == 'php' || empty($attach_ext) || !@in_array($attach_ext,$available_type)){
//		showerror('对不起，您上传的文件类型已被系统禁止！');
//	}
	$randvar=num_rand('15');
	$uploadname=$randvar.'.'.$attach_ext;

	if(!is_dir($attachdir)) {
			@mkdir($attachdir);
			@chmod($attachdir,0777);
		}
		
	$source=$attachdir.'/'.$uploadname;
	$returnfile="upload";
	$returnfile=$returnfile.'/'.$uploadname;
	if(function_exists("move_uploaded_file") && @move_uploaded_file($attachment, $source)){
		chmod($source,0777);
		$attach_saved = 1;
	}elseif(@copy($attachment, $source)){
		chmod($source,0777);
		$attach_saved = 1;
	}elseif(is_readable($attachment) && $attcontent=readover($attachment)){
		$attach_saved = 1;
		writeover($source,$attcontent);
		chmod($source,0777);
	}
	
	if($attach_saved == 1){	
		//echo "1111";
		$query=$db->query("SELECT * from goip,prov where goip.provider=prov.id and alive=1 order by prov");
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
			
		$ext=substr($source, strlen($source) - 4);
		if($ext=='.csv')
		{
			//echo "2222";
			$fp   =   fopen($source,"r");
			/*解析列名*/  
			if( fgetcsv($fp,'1024',',')){   
				$namenum = count ($name);
			}   
			$row=0;
			$srow=0;
			while($data   =   fgetcsv($fp,'1024',',')){   
				//echo "2222 $data[0], $data[1], $data[2]";
				$row++;
				ussd_add($goiprow, $prov,$data[0], $data[1], $data[2]);
	
      	  		}
			ussd_start($sendrow, $goiprow, $socks);
			fclose($fp); 
			wait_answer($socks, $sendrow, $goiprow);
			//WriteSuccessMsg($Msg,"receiver.php");
		
		}
		else if($ext=='.xls') 
		{
			//echo "excel";
			require_once "excel_class.php";
			Read_Excel_File($source,$return);
			$srow=0;
			//echo ("1111:".count($return[Sheet1]));
			for ($row=1;$row<count($return[Sheet1]);$row++)
            		{
				//echo "11111:$return[Sheet1][$row][0], $return[Sheet1][$row][1], $return[Sheet1][$row][2]";
				ussd_add($goiprow, $prov, $return[Sheet1][$row][0], $return[Sheet1][$row][1], $return[Sheet1][$row][2]);
            		}  
			--$row;  
			ussd_start($sendrow, $goiprow, $socks);
			//$Msg= "<br><li>导入接收人完毕,总共 $row 位，成功 $srow 位</li>".$Msg; 
			wait_answer($socks, $sendrow, $goiprow); 
		}
	}  
}

/*取出组*/
		if($_SESSION['goip_permissions'] < 2){
		}
		
function wait_answer($socks, $sendrow, $goiprow){
	global $db;
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
				else if($send[status] == "SEND"){
					if($comm[0] == "USSD"){
						array_shift($comm);
						array_shift($comm);
						$ussdmsg=implode(" ", $comm);
						if($ussdmsg != "USSD send failed!"){
							$ussdmsg=str_replace("@", "", $ussdmsg);
							$ussdmsg=mysql_real_escape_string($ussdmsg);
							//if(!$debug) echo "OK $ussdmsg";
							$sendrow[$the][status]="OVER";
							ok_over($send[goipname], $send[cmd], $ussdmsg);
						}
						else {
							$sendrow[$the][status]="OVER";
							error_over($send[goipname], $send[cmd], $ussdmsg);
						}
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
		if($overflag) die("All USSD are over");
		
	}
}

function showerror($msg){
	//@extract($GLOBALS, EXTR_SKIP);
	//require_once GetLang('msg');
	//$lang[$msg] && $msg=$lang[$msg];
	echo "<script>"
		."alert('$msg');"
		."history.back();"
		."window.returnValue = '';"
		."window.close();"
		."</script>";
	exit;
}

function num_rand($lenth){
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<$lenth;$i++){
		$randval.= mt_rand(0,9);
	}
	$randval=substr(md5($randval),mt_rand(0,32-$lenth),$lenth);
	return $randval;
}

?> 

<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg"> 
    <td height="22" colspan="2" align="center"><strong>Upload USSD CMD</strong></td>
  </tr>
  <tr class="tdbg"> 
    <td wIdth="100" height="30"><strong>Navigation:</strong></td>
    <td height="30">Upload USSD CMD</td>
  </tr>
</table>
<br> 
<table wIdth="100%" border="0" align="center" cellpadding="0" cellspacing="1" class="border">
  <tr class="title">
    <td height="22"><strong></strong></td>
  </tr>
  <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'">
    <td valign="middle"><ul>

   	 </ul>
	</td>
  </tr>
</table>
<br>
<FORM  name=uploadform action="all_cmd.php" method="POST" enctype="multipart/form-data" onSubmit="return check()">
<center>
<br>
<tr><td>
<br>
please choose file to upload<INPUT TYPE="HIdDEN"  name="action" value="mingsenupload">
<input type=file name=img1><INPUT TYPE="SUBMIT" value="Upload">
</td></tr>
</center>
</FORM>
</body>
</html>
