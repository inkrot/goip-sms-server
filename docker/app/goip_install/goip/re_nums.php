<?php
define("OK", true);
require_once("session.php");
require_once("global.php");

if($_REQUEST['action']=="save"){
	$nums=explode("\r\n",$_POST['num']);
	//print_r($nums);
	//echo(strlen($nums[0]));
	array_unique($nums);
	foreach($nums as $num){
		if($num) $db->query("insert into recharge_record set num='$num', be_name='', re_group_id='".$_POST['group']."'");
	}
	WriteSuccessMsg("<br><li>保存号码成功</li>","?");
}

        $query=$db->query("select * from recharge_group order by name ASC");
        while($row=$db->fetch_array($query)) {
                $rsdb[]=$row;
        }

print <<<EOT
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>上传充值号码</title>
<meta name="Author" content="Gaby_chen">
<link href="style.css" rel="stylesheet" type="text/css">
<SCRIPT language=javascript>
</SCRIPT>
</head>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg"> 
    <td height="22" colspan="2" align="center"><strong>上传充值号码</strong></td>
  </tr>
  <tr class="tdbg"> 
    <td wIdth="120" height="30"><strong>Navigation:</strong></td>
    <td height="30"><a href="recharge_nums.php" target=main>上传充值号码</a></td>
  </tr>
</table>
</table>
<form action="?action=save" method=post name=myform>
  <table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
    <tr class="title"> 
      <td colspan="2" height="22" colspan="2" align="center"><strong>上传充值号码</strong></td>
    </tr>
    <tr align="center">
    <td><textarea name="num"  rows="10" wrap="virtual" cols="32" class="textarea" ></textarea></td>
     </tr>
    <tr align="center">
      <td>组<select style="width:100px" id="group" name="group">
<!--                                                                                                              
EOT;
foreach($rsdb as $rs){                                                                                          
                                                                                                                  
print <<<EOT
-->
	<option value="$rs[id]">$rs[name]</option>
<!--
EOT;
} 
print <<<EOT
-->
      </td>
    </td>
    <tr align="center">
    <td><input type="submit" name="Submit" value="提交" style="cursor:hand;"></td>
     </tr>
  </table>
</form>
</body>
</html>
<!--
EOT;
?>
->
