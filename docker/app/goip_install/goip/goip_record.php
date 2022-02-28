<?php
define("OK", true);
require_once("session.php");
//if($_SESSION['goip_permissions'] > 1)	
	//die("需要admin权限！");	
require_once("global.php");
$action=$_GET['action'];
if($action=="del"){
	if(operator_owner_forbid()) WriteErrMsg('<br><li>forbidden</li>');
	$ErrMsg="";
	$Id=$_GET['id'];
	if(empty($Id)){
		$num=$_POST['boxs'];
		for($i=0;$i<$num;$i++)
		{	
			if(!empty($_POST["Id$i"])){
				/*
				   if($_POST["Id$i"] == "1"){
				   $ErrMsg="<br><li>超级用户不能删除</li>";
				   WriteErrMsg($ErrMsg);
				   break;
				   }
				 */
				if($Id=="")
					$Id=$_POST["Id$i"];
				else
					$Id=$_POST["Id$i"].",$Id";
			}
		}
	}
	//WriteErrMsg("$Id");

	if(empty($Id))
		$ErrMsg ='<br><li>Please choose one</li>';
	if($ErrMsg!="")
		WriteErrMsg($ErrMsg);
	else{
		$query=$db->query("DELETE FROM record WHERE id IN ($Id)");

		WriteSuccessMsg("<br><li>删除通话记录成功</li>","goip_record.php?goipid=".$_GET[goipid]);

	}
}
else if($action=="delall"){
        if(operator_owner_forbid()) WriteErrMsg('<br><li>forbidden</li>');
	if($_REQUEST[goipid]) $where="record.goipid='$_REQUEST[goipid]' ";
	else $where=" 1";

	$db->query("DELETE FROM record WHERE $where");                                                                                                                 
	WriteSuccessMsg("<br><li>删除通话记录成功</li>","goip_record.php?goipid=".$_GET[goipid]);

}

	if($_REQUEST[goipid]) $where="record.goipid='$_REQUEST[goipid]' ";
	else $where=" 1";
	$query=$db->query("SELECT count(*) AS count FROM record where $where");

	$row=$db->fetch_array($query);
	$count=$row['count'];
	$numofpage=ceil($count/$perpage);
	$totlepage=$numofpage;
	if(isset($_GET['page'])) {
		$page=$_GET['page'];
	} else {
		$page=1;
	}
	if($numofpage && $page>$numofpage) {
		$page=$numofpage;
	}
	if($page > 1) {
		$start_limit=($page - 1)*$perpage;
	} else{
		$start_limit=0;
		$page=1;
	}
	$fenye=showpage("?goipid=$_REQUEST[goipid]&",$page,$count,$perpage,true,true,"编");
	$query=$db->query("SELECT record.*, goip.name FROM record,goip where $where and goip.id=record.goipid ORDER BY id DESC LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		if($row['dir']=="1")
			$row[dir1]='INCOMING';
		else if($row['dir']=="2")
			$row[dir1]='OUTGOING';
		else $row[dir1]='UNKNOWN';
		$rsdb[]=$row;
	}
        if($_REQUEST[goipid]) $goipname=$rsdb[0][name];
        else $goipname="ALL";

        $goip_select="<select name=\"goipid\"  style=\"width:80px\" onchange=\"javascript:window.location='?goipid='+this.options[this.selectedIndex].value\">\n\t<option value=\"0\">All</option>\n";
        $query=$db->query("SELECT id,name from goip ORDER BY name");
        while($row=$db->fetch_array($query)) {
                if($_REQUEST['goipid']==$row['id'])
                        $goip_select.="\t<option value=\"$row[id]\" selected>$row[name]</option>\n";
                else
                        $goip_select.="\t<option value=\"$row[id]\">$row[name]</option>\n";
        }
        $goip_select.="</select>";

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css">
<title>Goip Record</title>
<script language="javascript">
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
//		var ckpage=document.modifyform.elements['chkAll'+num];
	    if(document.myform.chkAll.checked){
		document.myform.chkAll.checked = document.myform.chkAll.checked&0;
	    } 	
		

		}

</script>
</head>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg"> 
    <td height="22" colspan="2" align="center"><strong>Goip通话记录</strong></td>
  </tr>
  <tr class="tdbg"> 
<td wIdth="70" height="30"><strong>goip:</strong></td>
    <td height="30"><a href="goip.php" target=main>参数管理</a>&nbsp;|&nbsp;<a href="goip.php?action=add" target=main>添加机器</a></td>
  </tr>
</table>
<table width="100%" height="25"  border="0" cellpadding="0" cellspacing="0">
  <tr class="topbg">
    <td width="8%">&nbsp;</td>
    <td width="92%" height="25"><strong>当前位置：goip(<?php echo $goipname ?>)通话记录</strong></td>
  </tr>
  <tr class="topbg">
GoIP:<?php echo $goip_select ?>
  </tr>
</table>
<form action="goip_record.php?action=del&goipid=<?php echo $_REQUEST['goipid']; ?>" method=post name=myform onSubmit="return confirm('确认删除?')">
<table wIdth="100%"  border="0" cellspacing="2" cellpadding="2">
	<tr class=title>
		<td wIdth="35" align=center height="25"><b>选择</b></td>
		<td align="center"><b>拨打时间</b></td>
		<td align="center"><b>通话计时(秒)</b></td>
		<td align="center"><b>方向</b></td>
		<td align="center"><b>通话号码</b></td>
		<td wIdth="80" align=center><b>操作</b></td>
	</tr>
<!--
<?php 
$j=0;
foreach($rsdb as $rs) {
print <<<EOT
-->
	<tr class="even" onMouseOver="mouseover(this)" onMouseOut="mouseout(this)" onMouseDown="trclick(this)">
		<td align=center wIdth="35"><input name="Id{$j}" type='checkbox' onClick="return false" value="{$rs['id']}"></td>
		<td align="center">{$rs['time']}</td>
		<td align="center">{$rs['expiry']}</td>
		<td align="center">{$rs['dir1']}</td>
		<td align="center">{$rs['num']}</td>
				
		<td align=center wIdth="80"><a href="goip_record.php?id={$rs['id']}&action=del&goipid={$rs['goipid']}" onClick="return confirm('确认删除?')">删除</a></td>
    </tr>

<!--
EOT;
$j++;
}
print <<<EOT
-->
</table>
<input type="hIdden" name="boxs" value="{$j}">
<table wIdth="100%"  border="0" cellspacing="2" cellpadding="2">


					<tr>
						<td height="30" ><input name="chkAll" type="checkbox" Id="chkAll" onclick=CheckAll(this.form) value="checkbox"> 
					  选择当前页面<input name="submit" type='submit' value='删除所选'></td>
					</tr>
					<tr>
						<td  align=center>{$fenye}</td>
					</tr>
</table>
<!--
EOT;
?>
-->
</form>

					  </td> 
					</tr>
</table>
				
</body>
</html>
