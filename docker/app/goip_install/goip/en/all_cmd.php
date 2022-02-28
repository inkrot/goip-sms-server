
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>All CMD</title>
<link href="../style.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	background-color: #FFFFFF;
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

function unselectall()
        {
            if(document.myform.chkAll.checked){
                document.myform.chkAll.checked = document.myform.chkAll.checked&0;
            } 
        }

function CheckAll(form)
        {
                var trck;
                var e;
                for (var i=0;i<form.elements.length;i++)
            {
                    e = form.elements[i];
                    if (e.type == 'checkbox' && e.id != "chkAll" && e.disabled==false){
                                e.checked = form.chkAll.checked;
                                do {e=e.parentNode} while (e.tagName!="TR") 
                                if(form.chkAll.checked)
                                        e.className = 'even marked';
                                else
                                        e.className = 'even';
                        }
            }
                //form.chkAll.classname = 'even';
        }

function mouseover(obj) {
                obj.className += ' hover';
                                //alert(obj.className);
            
                        }
function mouseout(obj) {
                obj.className = obj.className.replace( ' hover', '' );
                                //alert(obj.className);
                        }

function trclick(obj) {
                //alert("ddddd");
        var checkbox = obj.getElementsByTagName( 'input' )[0];
        //if ( checkbox && checkbox.type == 'checkbox' ) 
        checkbox.checked ^= 1;
                if(checkbox.checked)
                        obj.className = 'even marked';
                else obj.className = 'even';
//              var ckpage=document.modifyform.elements['chkAll'+num];
            if(document.myform.chkAll.checked){
                document.myform.chkAll.checked = document.myform.chkAll.checked&0;
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
		$msg.='<br><li>not have that provider: '.$tmpdata.'</li>';
		return 0;					
	}
	return 1;
}
require_once("session.php");
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
			$db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$send[value]', USSD_RETURN='$value', INSERTTIME=now(),recharge_ok='$recharge_ok',type=2,card='$send[card]'");
			$db->query("update recharge_card set used='$recharge_ok' where id='$send[card_id]'");
		}
		else
			$db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$send[value]', USSD_RETURN='$value', INSERTTIME=now()");
		$report[$TERMID]['TERMID']=$TERMID;
		$report[$TERMID]['cmd']=$send[value];
		$report[$TERMID]['value']=$value;
		$report[$TERMID]['error_msg']=$ERROR_MSG;
		$report[$TERMID]['goipid']=$send[goipid];
	        $line_html.= <<<EOT
        <tr class="even" onMouseOver="mouseover(this)" onMouseOut="mouseout(this)" onMouseDown="trclick(this)">
                <td align=center wIdth="35"><input name="Id{$count}" type='checkbox' onClick="return false" value="{$send[goipid]}"></td>
                <td align="center">{$TERMID}</td>
                <td align="center">{$send[value]}</td>
                <td align="center">{$value}</td>
                <td align="center">{$ERROR_MSG}</td>
        </tr>
EOT;
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
        		$db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$send[value]', ERROR_MSG='$ERROR_MSG', INSERTTIME=now(),card='$send[card]',type=2");
			$db->query("update recharge_card set used=0 where id='$send[card_id]'");
		}
		else 
        		$db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$send[value]', ERROR_MSG='$ERROR_MSG', INSERTTIME=now()");
		$report[$TERMID]['TERMID']=$TERMID;
		$report[$TERMID]['cmd']=$send[value];
		$report[$TERMID]['value']=$USSD_RETURN;
		$report[$TERMID]['error_msg']=$ERROR_MSG;
		$report[$TERMID]['goipid']=$send[goipid];
		$line_html.= <<<EOT
        <tr class="even" onMouseOver="mouseover(this)" onMouseOut="mouseout(this)" onMouseDown="trclick(this)">
                <td align=center wIdth="35"><input name="Id{$count}" type='checkbox' onClick="return false" value="{$send[goipid]}"></td>
                <td align="center">{$TERMID}</td>
                <td align="center">{$send[value]}</td>
                <td align="center">{$USSD_RETURN}</td>
                <td align="center">{$ERROR_MSG}</td>
        </tr>
EOT;
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
		$send[number] = @array_shift($goip[number]);
		$send[value] = @array_shift($goip[value]);
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
		else if($send[value])
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

//print_r($_POST);

if ( $_POST["action"]=="mingsenupload")
{
        //echo "upload";
	$_REQUEST[cmd]='USSD';
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
//      if($attach_ext == 'php' || empty($attach_ext) || !@in_array($attach_ext,$available_type)){
//              showerror('对不起，您上传的文件类型已被系统禁止！');
//      }
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
                $query=$db->query("SELECT *, goip.id as goipid from goip,prov where goip.provider=prov.id and alive=1 order by prov");
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
                                ussd_add($goiprow, $prov, "USSD", $data[0], "", $data[1], $data[2]);

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
                                ussd_add($goiprow, $prov, "USSD", $return[Sheet1][$row][0], "", $return[Sheet1][$row][1], $return[Sheet1][$row][2]);
                        }  
                        --$row;  
                        ussd_start($sendrow, $goiprow, $socks);
                        //$Msg= "<br><li>导入接收人完毕,总共 $row 位，成功 $srow 位</li>".$Msg; 
                        wait_answer($socks, $sendrow, $goiprow); 
                }
        }  
}
else {
if(!$_REQUEST['cmd'])
        die("please input cmd!");
if($_REQUEST['chkAll0']) {
	$query=$db->query("SELECT *, goip.id as goipid from goip,prov where goip.provider=prov.id and alive=1 order by prov");
	//die('do all');
}
else {
	$ID=get_id();
	if(!$ID){
		die("do not select id!");
	}
	//echo $ID;
	//die;
	$query=$db->query("SELECT *, goip.id as goipid from goip,prov where goip.provider=prov.id and alive=1 and goip.id in ($ID) order by prov");
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
if($_REQUEST[value]) ussd_add($goiprow, $prov, $_REQUEST[cmd], $_REQUEST[value], $_REQUEST[number]);
else ussd_add($goiprow, $prov, $_REQUEST[cmd], $_REQUEST[msg], $_REQUEST[number]);
ussd_start($sendrow, $goiprow, $socks);
wait_answer($socks, $sendrow, $goiprow); 
}
		
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
				$html= <<<EOT
<form action="all_cmd.php" method=post name=myform >
<table wIdth="100%"  border="0" cellspacing="2" cellpadding="2">
        <tr class=title>
                <td wIdth="35" align=center height="25"><b>选择</b></td>
                <td align="center"><b>Send GoIP</b></td>
                <td align="center"><b>USSD CMD</b></td>
                <td align="center"><b>Return MSG</b></td>
                <td align="center"><b>ERROR MSG</b></td>
        </tr>
EOT;
	ksort($report);
	reset($report);
	$count=0;
	foreach($report as $rs){
                $html.= <<<EOT
        <tr class="even" onMouseOver="mouseover(this)" onMouseOut="mouseout(this)" onMouseDown="trclick(this)">
                <td align=center wIdth="35"><input name="Id{$count}" type='checkbox' onClick="return false" value="{$rs[goipid]}">
</td>           
                <td align="center">{$rs[TERMID]}</td>
                <td align="center">{$rs[cmd]}</td>
                <td align="center">{$rs[value]}</td>
                <td align="center">{$rs[error_msg]}</td>
        </tr>
EOT;
                $count++;
        }

                        	$html.= <<<EOT
<input type="hIdden" name="boxs" value="{$count}">
<input type="hIdden" name="cmd" value="USSD">
<table wIdth="100%"  border="0" cellspacing="2" cellpadding="2">
        <tr>
         <td height="30" ><input name="chkAll" type="checkbox" Id="chkAll" onclick=CheckAll(this.form) value="checkbox">
          choose all
USSD
<div id="input_ussd">USSD CMD
<input type="input" name="value">
<input  type="submit" name="Submit" value="Submit" style="cursor:hand;"></div>
</td>
</tr>
</table>
</form>
</html>
EOT;
				echo $html;
			}
			die;
		}
		
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
                if($i==0) $randval.= mt_rand(1,9);
                else $randval.= mt_rand(0,9);
	}
	//$randval=substr(md5($randval),mt_rand(0,32-$lenth),$lenth);
	return $randval;
}

?> 

<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg"> 
    <td height="22" colspan="2" align="center"><strong>导入USSD指令</strong></td>
  </tr>
  <tr class="tdbg"> 
    <td wIdth="100" height="30"><strong>管理导航:</strong></td>
    <td height="30">导入USSD指令</td>
  </tr>
</table>
<br> 
<table wIdth="100%" border="0" align="center" cellpadding="0" cellspacing="1" class="border">
  <tr class="title">
    <td height="22"><strong> 提示： </strong></td>
  </tr>
  <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'">
    <td valign="middle"><ul>

   	 </ul>
	</td>
  </tr>
</table>
<br>
<FORM  name=uploadform action="<?php echo $PHP_SELF?>" method="POST" enctype="multipart/form-data" onSubmit="return check()">
<center>
<br>
<tr><td>
<br>
请上传要导入的文件<INPUT TYPE="HIdDEN"  name="action" value="mingsenupload">
<input type=file name=img1><INPUT TYPE="SUBMIT" value="上 传">
</td></tr>
</center>
</FORM>
</body>
</html>
