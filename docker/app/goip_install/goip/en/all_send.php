<?php
require_once("session.php");
//if($_SESSION['goip_permissions'] > 1)	
	//die("Permission denied!");	
define("OK", true);
require_once("global.php");
require_once('../inc/conn.inc.php');


      function do_cron($db,$port)
        {
                if(!$port) $port=44444;
                $flag=0;
                /* 此是最新计划， 唤醒服务进程*/
                if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
                        echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
                        exit;
                }
                if (socket_sendto($socket,"AUTO_SEND", 9, 0, "127.0.0.1", $port)===false)
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
                if($flag)
                        echo "The task has been inserted.";
                else
                        echo "The task has been inserted,but cannot get response from process named \"goipcron\". please check this process.";
        } 


if($_GET['action'] != "save"){
  if($FoundErr!=true){
  	$query=$db->query("SET NAMES 'utf8'");
	$query=$db->query("SELECT all_send_num,all_send_msg FROM auto WHERE 1 ");
	$rs=$db->fetch_array($query);
	$all_send_num=$rs['all_send_num'];
	$all_send_msg =$rs['all_send_msg'];
  }
}
else {
	//print_r($_POST);
        $all_send_num=myaddslashes($_POST['all_send_num']);
        $all_send_msg=myaddslashes($_POST['all_send_msg']);
	$query=$db->query("UPDATE auto SET all_send_num='$all_send_num',all_send_msg='$all_send_msg' where 1");
	do_cron($db, 0);
	WriteSuccessMsg("<br><li>保存成功</li>","all_send.php");	
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="../style.css" rel="stylesheet" type="text/css">
<title>All Send Setting</title>
<script language="JavaScript" type="text/JavaScript">
</script>
</head>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table width="100%" height="25"  border="0" cellpadding="0" cellspacing="0">
  <tr class="topbg">
    <td width="8%">&nbsp;</td>
    <td width="92%" height="25"><strong>Current Location:All Line Send SMS Settings</strong></td>
  </tr>
</table>
<table wIdth="100%" border="0" align="center" cellpadding="0" cellspacing="1" class="border">
  <tr class="title">
    <td height="22"><strong>  Notice: </strong></td>
  </tr>
  <tr class="tdbg" >
    <td valign="middle"><ul>
	You can set settings of All lines Sending here.Then you can ues Send Message->All Lines Send to let all lines send the same SMS.
         </ul>
        </td>
  </tr>
</table>
<form method="post" action="all_send.php?action=save" name="myform">
  <br>
  <br>
  <table wIdth="400" border="0" align="center" cellpadding="2" cellspacing="1" class="border" >
    <tr class="title"> 
      <td height="22" colspan="2"> <div align="center"><strong>All lines send SMS setting</strong></div></td>
    </tr>
    <tr>
      <td wIdth="150" align="right" class="tdbg"><strong>Send to Number:</strong></td>
      <td class="tdbg"><input type="input" name="all_send_num" value="<?php echo $all_send_num ?>"></td>
    </tr>
   <tr> 
      <td wIdth="150" align="right" class="tdbg"><strong>SMS Content:</strong></td>
      <td class="tdbg"><textarea name="all_send_msg"  rows="8" wrap=PHYSICAL cols="16" class="textarea"><?php echo $all_send_msg ?></textarea></td>
    </tr>

    <tr> 
      <td height="40" colspan="2" align="center" class="tdbg"><input name="Action" type="hIdden" Id="Action" value="Modify"> 
        <input  type="submit" name="Submit" value="Save" style="cursor:hand;"> 
 
        &nbsp;<input name="Cancel" type="button" Id="Cancel" value="Cancel" onClick="window.location.href='all_send.php'" style="cursor:hand;"></td>
    </tr>
  </table>
  </form>
					  </td> 
					</tr>
</table>
				
</body>
</html>

