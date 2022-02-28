<?php
require_once("session.php");
if($_SESSION['goip_permissions'] > 1)	
	die("Permission denied!");	
define("OK", true);
require_once("global.php");
require_once('../inc/conn.inc.php');


if($_GET['action'] != "save"){

  if($FoundErr!=true){
  	$query=$db->query("SET NAMES 'utf8'");
	$query=$db->query("SELECT * FROM system WHERE 1 ");
	$rs=$db->fetch_array($query);
	$sysname=$rs['sysname'];
	$maxword=$rs['maxword'];
	$lan=$rs['lan'];
  }
}
else {
	$sysname=$_POST['sysname'];
	$maxword=$_POST['maxword'];
	$lan=$_POST['lan'];
	$query=$db->query("UPDATE system SET sysname='$sysname',lan=$lan,send_page_jump_enable='$_POST[send_page_jump_enable]', session_time='$_POST[session_time]',disable_status='$_POST[disable_status]' where 1");
	
	$session_time=$_POST[session_time]*60;
	if($session_time<300) $session_time=300; 
	setcookie(session_name(), session_id(), time() + $session_time, "/");
	sendto_cron("SYSTEM_SAVE");

	WriteSuccessMsg("<br><li>Save success!</li>","sys.php");	
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="../style.css" rel="stylesheet" type="text/css">
<title>System settings</title>
<script language="JavaScript" type="text/JavaScript">
function check()
{
  var dec_num=/^[0-9]+$/;
  if (document.myform.maxword.value=="" || !dec_num.test(document.myform.maxword.value))
  {
    alert("The maximum number of words Input error!");
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
    <td width="92%" height="25"><strong>Current Location: System Settings</strong></td>
  </tr>
</table>
<form method="post" action="sys.php?action=save" name="myform" onSubmit="javascript:return check();">
  <br>
  <br>
  <table wIdth="500" border="0" align="center" cellpadding="2" cellspacing="1" class="border" >
    <tr class="title"> 
      <td height="22" colspan="2"> <div align="center"><strong>System Settings</strong></div></td>
    </tr>
    <tr> 
      <td wIdth="250" align="right" class="tdbg"><strong>System name:</strong></td>
      <td class="tdbg"><input type="input" name="sysname" value="<?php echo $sysname ?>"></td>
    </tr>
    <tr> 
      <td wIdth="250" align="right" class="tdbg"><strong>Default Language:</strong></td>
      <td class="tdbg"><select name="lan">
	  						<option value="1" <?php if($lan==1) echo 'selected' ?>>Simplified Chinese</option>
	  						<option value="2" <?php if($lan==2) echo 'selected' ?>>Traditional Chinese</option>
							<option value="3" <?php if($lan==3) echo 'selected' ?>>English</option>
						</select>
						</td>
    </tr>
    <tr> 
      <td wIdth="250" align="right" class="tdbg"><strong>Save message before sending(browser should support javascript):</strong></td>
      <td class="tdbg"><input name="send_page_jump_enable" value="1" id="send_page_jump_enable" type="radio" <?php echo $rs['send_page_jump_enable']=='1'?'checked':''?>><span>Enable</span>
              <input name="send_page_jump_enable" value="0" id="send_page_jump_disable" <?php echo $rs['send_page_jump_enable']=='0'?'checked':''?> type="radio"><span>Disable</span>  </td>
    </tr>
    <tr> 
      <td wIdth="250" align="right" class="tdbg"><strong>Session lifetime(Minute):</strong></td>
      <td class="tdbg"><input type="input" name="session_time" value="<?php echo $rs[session_time] ?>"></td>
    </tr>
    <tr> 
      <td wIdth="250" align="right" class="tdbg"><strong>GoIP Report Record:</strong></td>
      <td class="tdbg"><input name="disable_status" value="0" id="disable_status" type="radio" <?php echo $rs['disable_status']!='1'?'checked':''?>><span>Enable</span>
              <input name="disable_status" value="1" id="enable_status" <?php echo $rs['disable_status']=='1'?'checked':''?> type="radio"><span>Disable</span>  </td>
    </tr>

<!--
    <tr> 
      <td wIdth="150" align="right" class="tdbg"><strong>Maximum words of massage:</strong></td>
      <td class="tdbg"><input type="input" name="maxword" value="<?php echo $maxword ?>"> </td>
    </tr>
-->	
    <tr> 
      <td height="40" colspan="2" align="center" class="tdbg"><input name="Action" type="hIdden" Id="Action" value="Modify"> 
        <input  type="submit" name="Submit" value="Modify" style="cursor:hand;"> 
 
        &nbsp;<input name="Cancel" type="button" Id="Cancel" value="Cancel" onClick="window.location.href='sys.php'" style="cursor:hand;"></td>
    </tr>
  </table>
  </form>
					  </td> 
					</tr>
</table>
				
</body>
</html>

