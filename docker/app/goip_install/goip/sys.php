<?php
require_once("session.php");
if($_SESSION['goip_permissions'] > 1)	
	die("需要admin权限！");	
define("OK", true);
require_once("global.php");
require_once('inc/conn.inc.php');


if($_GET['action'] != "save"){
/*
if ($username==''){
	$FoundErr=true;
	$ErrMsg= "<br><li>用户名不能为空!</li>";
}
if ($password==''){
	$FoundErr=true;
	$ErrMsg=$ErrMsg."<br><li>密码不能为空!</li>";
}
*/
  if($FoundErr!=true){
  	$query=$db->query("SET NAMES 'utf8'");
	$query=$db->query("SELECT * FROM system WHERE 1 ");
	$rs=$db->fetch_array($query);
	$sysname=$rs['sysname'];
	$maxword=$rs['maxword'];
	$lan=$rs['lan'];
/*
	if(empty($adminId)){
		$FoundErr=true;
		$ErrMsg=$ErrMsg."<br><li>用户名或密码错误!</li> $password";
	}
	else{

	}
*/
  }
}
else {
	$sysname=$_POST['sysname'];
	$maxword=$_POST['maxword'];
	$lan=$_POST['lan'];
	$query=$db->query("UPDATE system SET sysname='$sysname',lan=$lan,send_page_jump_enable='$_POST[send_page_jump_enable]', session_time='$_POST[session_time]' where 1");
	
	$session_time=$_POST[session_time]*60;
	if($session_time<300) $session_time=300; 
	setcookie(session_name(), session_id(), time() + $session_time, "/");
	sendto_cron("SYSTEM_SAVE"); 
	WriteSuccessMsg("<br><li>保存成功</li>","sys.php");	
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css">
<title>系统参数设置</title>
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
    <td width="92%" height="25"><strong>当前位置：系统参数设置</strong></td>
  </tr>
</table>
<form method="post" action="sys.php?action=save" name="myform" onSubmit="javascript:return check();">
  <br>
  <br>
  <table wIdth="400" border="0" align="center" cellpadding="2" cellspacing="1" class="border" >
    <tr class="title"> 
      <td height="22" colspan="2"> <div align="center"><strong>系统参数参数设定</strong></div></td>
    </tr>
    <tr> 
      <td wIdth="100" align="right" class="tdbg"><strong>系统名称:</strong></td>
      <td class="tdbg"><input type="input" name="sysname" value="<?php echo $sysname ?>"></td>
    </tr>
    <tr> 
      <td wIdth="100" align="right" class="tdbg"><strong>默认语言:</strong></td>
      <td class="tdbg"><select name="lan">
	  						<option value="1" <?php if($lan==1) echo 'selected' ?>>简体中文</option>
	  						<option value="2" <?php if($lan==2) echo 'selected' ?>>繁体中文</option>
							<option value="3" <?php if($lan==3) echo 'selected' ?>>英语</option>
						</select>
						</td>
    </tr>
    <tr> 
      <td wIdth="200" align="right" class="tdbg"><strong>发送短信前先保存(浏览器需支持javascript):</strong></td>
      <td class="tdbg"><input name="send_page_jump_enable" value="1" id="send_page_jump_enable" type="radio" <?php echo $rs['send_page_jump_enable']=='1'?'checked':''?>><span>启动</span>
              <input name="send_page_jump_enable" value="0" id="send_page_jump_disable" <?php echo $rs['send_page_jump_enable']=='0'?'checked':''?> type="radio"><span>禁用</span>  </td>
    </tr>

    <tr> 
      <td wIdth="150" align="right" class="tdbg"><strong>Session生存时间(分钟):</strong></td>
      <td class="tdbg"><input type="input" name="session_time" value="<?php echo $rs[session_time] ?>"></td>
    </tr>

    <tr> 
      <td wIdth="250" align="right" class="tdbg"><strong>GoIP汇报低优先级信息:</strong></td>
      <td class="tdbg"><input name="disable_status" value="0" id="disable_status" type="radio" <?php echo $rs['disable_status']!='1'?'checked':''?>><span>启用</span>
              <input name="disable_status" value="1" id="enable_status" <?php echo $rs['disable_status']=='1'?'checked':''?> type="radio"><span>禁用</span>  </td>
    </tr>

<!--
    <tr> 
      <td wIdth="100" align="right" class="tdbg"><strong>信息最大字数:</strong></td>
      <td class="tdbg"><input type="input" name="maxword" value="<?php echo $maxword ?>"> </td>
    </tr>
-->	
    <tr> 
      <td height="40" colspan="2" align="center" class="tdbg"><input name="Action" type="hIdden" Id="Action" value="Modify"> 
        <input  type="submit" name="Submit" value="保 存" style="cursor:hand;"> 
 
        &nbsp;<input name="Cancel" type="button" Id="Cancel" value="取 消" onClick="window.location.href='sys.php'" style="cursor:hand;"></td>
    </tr>
  </table>
  </form>
					  </td> 
					</tr>
</table>
				
</body>
</html>

