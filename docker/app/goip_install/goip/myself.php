<?php
	require_once("session.php");
	define("OK", true);
	require_once('inc/conn.inc.php');
	//require_once("global.php");	
	if($_POST[btnSave] =='Save'){
		function WriteErrMsg($ErrMsg1)
		{
			$strErr="<html><head><title>Error Information</title><meta http-equiv='Content-Type' content='text/html; charset=ISO-8859-1'>" ;
			$strErr=$strErr."<link href='style.css' rel='stylesheet' type='text/css'></head><body>" ;
			$strErr=$strErr."<table cellpadding=2 cellspacing=1 border=0 wIdth=400 class='border' align=center>"; 
			$strErr=$strErr."  <tr align='center'><td height='22' class='title'><strong>Wrong message</strong></td></tr>" ;
			$strErr=$strErr."  <tr><td height='100' class='tdbg' valign='top'><b> Reasons:</b><br> $ErrMsg1</td></tr>" ;
			$strErr=$strErr."  <tr align='center'><td class='tdbg'><a href=javascript:history.back();>&lt;&lt; Return</a></td></tr>" ;
			$strErr=$strErr."</table>" ;
			$strErr=$strErr."</body></html>" ;
			echo $strErr;
			exit;
		}
		
		//'**************************************************
		////'éŽç¨‹å:WriteSuccessMsg
		//'ä½œ  ç”¨:é¡¯ç¤ºæˆåŠŸæç¤ºè³‡è¨Š
		//'åƒ  æ•¸:ç„¡
		//**************************************************
		function WriteSuccessMsg($SuccessMsg,$URL)
		{
			$strErr="<html><head><title>Success Information</title><meta http-equiv='Content-Type' content='text/html; charset=ISO-8859-1'>" ;
			$strErr=$strErr."<link href='style.css' rel='stylesheet' type='text/css'></head><body>" ;
			$strErr=$strErr."<table cellpadding=2 cellspacing=1 border=0 wIdth=400 class='border' align=center>"; 
			$strErr=$strErr."  <tr align='center'><td height='22' class='title'><strong>Congratulation</strong></td></tr>" ;
			$strErr=$strErr."  <tr><td height='100' class='tdbg' valign='top'>$SuccessMsg</td></tr>" ;
			$strErr=$strErr."  <tr align='center'><td class='tdbg'><a href=$URL>Apply</a></td></tr>" ;
			$strErr=$strErr."</table>" ;
			$strErr=$strErr."</body></html>" ;
			echo $strErr;
			exit;
		}
		//$username=myaddslashes($_POST['user_name']);
		$password=myaddslashes($_POST['Password']);
		$mobile=$_POST['tel'];
		//$mobile=$_POST['tel'];

		$permissions=$_POST['permissions'];
		
		$info=$_POST['info'];

		

		if($password){
			$password=md5($password);
			$sqll.=",password='$password'";
		}	

		$query=$db->query("update user set permissions='$permissions',info='$info',tel='$mobile',email='$_POST[email]' ".$sqll. " where id=$_SESSION[goip_userid]");
		WriteSuccessMsg("<br><li>Modify administrator success</li>","index.php");
					

	}else {
		$query=$db->query("SELECT * FROM user WHERE id=$_SESSION[goip_userid] ");
		$rs=$db->fetch_array($query);
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="sms/style.css" type="text/css" />
<link rel="stylesheet" href="style.css" type="text/css" />
<title>System Management</title>
</head>
<body onload="">
<style type="text/css">
.button_title{
	background:url('images/BG/navbar_bg.png') repeat-x;
	height:30px;
	font-size:13px;
	font-weight:bolder;
	vertical-align:middle;
	border-style:solid;
	border-color:#999999;
	border-width:1px;
	text-indent:20px;
	font-family:Arial;
	color: #FFFFFF;
}
.Title8 {

	font-family: "Helvetica";
	font-size: 18px;
	color: #66b1f7;
}
.nav {
  margin: 0;
  padding: 0;
  background-image: url('images/BG/navbar_bg.png');
  font-family:Arial;
  list-style-type: none;

  width: 100%;
  float: left; /* Contain foated list items */
}
.nav li {
  margin: 0;
  padding: 0;
  float: left;
}
.nav a {
  float: left;
  padding:8px;
  text-align: center;
  color: #FFF;
  font-family:Arial;
  font-size:13px;

  text-decoration: none;
  border-right: 1px solid #FFF;
}
.nav a:hover {
  color: #666;
  font-size:13px;
  border-right: 1px solid #FFF;
}
</style>
<script language="JavaScript" type="text/JavaScript">
function check_pw()
{
  if(document.frmSystemUser.Password.value=="" && document.frmSystemUser.Submit.value=="Save")
  	return true;
  if(document.frmSystemUser.Password.value=="")
    {
      alert("please input password");
	  document.frmSystemUser.Password.focus();
      return false;
    }
    
  if((document.frmSystemUser.Password.value)!=(document.frmSystemUser.PwdConfirm.value))
    {
      alert("Password and Confirm Password are different!");
	  document.frmSystemUser.PwdConfirm.select();
	  document.frmSystemUser.PwdConfirm.focus();	  
      return false;
    }
}
</script>
<div id="div_top_logo">
	<div style="position:absolute;left: 8px;top:5px;height:30px;width:50%">
	
		<span class="Title8">SMS Management System</span>
	</div>
	<div style="width:200px;float:right;height:30px;top:0px" align="right"><?php echo $_SESSION['goip_username'] ?><a href="myself.php">[Account]</a> | <a href="logout.php">[Logout]</a>
	</div>

<div id="div_top_menu" style="position:absolute;left: 0px;top:31px;width:100%">
<ul class="nav">
	<li><a href="index.php">Home</a></li>
	<li><a href="contact/index.php">Contact</a></li>
	<li><a href="sms/index.php">SMS Management</a></li>
	<li><a href="index.php">System Management</a></li>
	<li><a href="goip_prov/index.php">Goip & Provider</a></li>
	<li></li>
</ul>
</div>


<div id="div_status" name="div_status">

</div>
<!--begin right-->
<div style="position:absolute; left: 200px; top:90px;">
<br>
<div><h1>System Account Management</h1></div>
<!--begin right-->
<div id="div_per_info">
<form id="frmSystemUser" name="frmSystemUser" method="post" action="">
<table style="width:600px">
	<tr>
	  <td colspan="2" class="button_title">Account Basic Information</td>
	</tr>
	<tr>
	  	<td colspan="2"> <div id="div_error_msg" class="error_msg"></div></td>
	</tr>
	<tr>
		<td nowrap style="width:100px">Account Name:</td>
		<td><?php echo $_SESSION['goip_username']?></td>
	</tr>
	<tr>
		<td nowrap style="width:100px">Password:</td>
		<td><input type="password" id="Password" name="Password" style="width:300px"/></td>
	</tr>
	<tr>
		<td nowrap style="width:100px">Comfirm Password:</td>
		<td><input type="password" id="PwdConfirm" name="PwdConfirm" style="width:300px"/></td>
	</tr>
	<tr>
		<td style="width:80px">Mobile:</td>
		<td><input type="text" id="tel" name="tel" style="width:300px" value="<?php echo $rs['tel']?>"/></td>
	</tr>
	<tr>
		<td style="width:80px">Email:</td>
		<td><input type="text" id="email" name="email" style="width:300px" value="<?php echo $rs['email']?>"/></td>
	</tr>
	<tr>
		<td>
		<input type="submit" id="btnSave" name="btnSave" value="Save" onclick="return check_pw()"/>
		</td>
	</tr>
</table>
</form>
</div>

<div id="div_error_msg" class="error_msg"></div>
</div>

<!--end right-->
</td>
</tr>
</table>
</form>
</body>
</html>

