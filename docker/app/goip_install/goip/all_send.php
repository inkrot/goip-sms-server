<?php
require_once("session.php");
//if($_SESSION['goip_permissions'] > 1)	
	//die("需要admin权限！");	
define("OK", true);
require_once("global.php");
require_once('inc/conn.inc.php');


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
                        echo "已加入";
                else
                        echo "已加入,但goipcron进程未响应，请检查该进程";
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
<link href="style.css" rel="stylesheet" type="text/css">
<title>自动回复</title>
<script language="JavaScript" type="text/JavaScript">
function check()
{
  var dec_num=/^[0-9]+$/;
  if (document.myform.maxword.value=="" || !dec_num.test(document.myform.maxword.value))
  {
    alert("信息最大字数输入错误!");
	document.myform.maxword.focus();
	return false;
  }
}
</script>
</head>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table width="100%" height="25"  border="0" cellpadding="0" cellspacing="0">
  <tr class="topbg">
    <td width="8%">&nbsp;</td>
    <td width="92%" height="25"><strong>当前位置：一键群发设置</strong></td>
  </tr>
</table>
<table wIdth="100%" border="0" align="center" cellpadding="0" cellspacing="1" class="border">
  <tr class="title">
    <td height="22"><strong> 提示： </strong></td>
  </tr>
  <tr class="tdbg" >
    <td valign="middle"><ul>
        系统可以预先设好一键群发的号码和短信内容，当点击左边 其他->一键群发 链接时，系统将自动用所有在线状态的终端，向指定号码发送指定内容的短信.
         </ul>
        </td>
  </tr>
</table>
<form method="post" action="all_send.php?action=save" name="myform" onSubmit="javascript:return check();">
  <br>
  <br>
  <table wIdth="400" border="0" align="center" cellpadding="2" cellspacing="1" class="border" >
    <tr class="title"> 
      <td height="22" colspan="2"> <div align="center"><strong>一键群发参数设定</strong></div></td>
    </tr>
    <tr>
      <td wIdth="150" align="right" class="tdbg"><strong>发送号码:</strong></td>
      <td class="tdbg"><input type="input" name="all_send_num" value="<?php echo $all_send_num ?>"></td>
    </tr>
   <tr> 
      <td wIdth="150" align="right" class="tdbg"><strong>短信内容:</strong></td>
      <td class="tdbg"><textarea name="all_send_msg"  rows="8" wrap=PHYSICAL cols="16" class="textarea"><?php echo $all_send_msg ?></textarea></td>
    </tr>

    <tr> 
      <td height="40" colspan="2" align="center" class="tdbg"><input name="Action" type="hIdden" Id="Action" value="Modify"> 
        <input  type="submit" name="Submit" value="保 存" style="cursor:hand;"> 
 
        &nbsp;<input name="Cancel" type="button" Id="Cancel" value="取 消" onClick="window.location.href='all_send.php'" style="cursor:hand;"></td>
    </tr>
  </table>
  </form>
					  </td> 
					</tr>
</table>
				
</body>
</html>

