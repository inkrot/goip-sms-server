<?php
require_once("session.php");
define("OK", true);
require_once("global.php");
if($_GET['action']=='del'){
	if(operator_owner_forbid()) WriteErrMsg('<br><li>forbidden</li>');
        $ErrMsg="";
        $Id=$_GET['id'];

        if(empty($Id)){
                $num=$_POST['boxs'];
                for($i=0;$i<$num;$i++)
                {
                        if(!empty($_POST["Id$i"])){
                                if($Id=="")
                                        $Id=$_POST["Id$i"];
                                else
                                        $Id=$_POST["Id$i"].",$Id";
                        }
                }
        }
        //WriteErrMsg($num."$Id");

        if(empty($Id))
                $ErrMsg ='<br><li>Please choose one</li>';
        if($ErrMsg!="")
                WriteErrMsg($ErrMsg);
        else{
                $query=$db->query("DELETE FROM USSD WHERE id IN ($Id)");
                WriteSuccessMsg("<br><li>Successfully deleted</li>","ussdinfo.php");

        }
}
elseif($_REQUEST['action']=="delall"){
	if(operator_owner_forbid()) WriteErrMsg('<br><li>forbidden</li>');
        $query=$db->query("DELETE FROM USSD WHERE 1");
        WriteSuccessMsg("<br><li>Successfully deleted</li>","ussdinfo.php");
}

        $where=" where 1 ";
        if($_REQUEST['action']=="search"){
                $action="search";
                $column=myaddslashes($_REQUEST['column']);
                $s_key=myaddslashes($_REQUEST['s_key']);
                $type=myaddslashes($_REQUEST['type']);
                $where.="and `$column`";
                $t_selected[$type]="selected";
                $c_selected[$column]="selected";
                if($type=="equal"){
                        $where.="='$s_key'";
                        //$t_selected['equal']="selected";
                }
                else if($type=="unequal"){
                        $where.="!='$s_key'";
                        //$t_selected['unequal']="selected";
                }
                else if($type=="prefix"){
                        $where.=" like '$s_key%'";
                        //$t_selected['prefix']="selected";
                }
                else if($type=="postfix"){
                        $where.=" like '%$s_key'";
                        //$t_selected['postfix']="selected";
                }
                else if($type=="contain"){
                        $where.=" like '%$s_key%'";
                        //$t_selected['postfix']="selected";
                }
        }
        else {
                $t_selected['equal']="selected";
                $c_selected['TERMID']="selected";
                //$maininfo="Current Location: GoIP List";
        }

$select="搜索项目<select name=\"column\"  style=\"width:80px\" >";
$select.="\t<option value='TERMID' $c_selected[TERMID]>GoIP ID</option>\n";
$select.="\t<option value='USSD_MSG' $c_selected[USSD_MSG]>USSD指令</option>\n";
$select.="\t<option value='USSD_RETURN' $c_selected[USSD_RETURN]>返回</option>\n";
$select.="\t<option value='ERROR_MSG' $c_selected[ERROR_MSG]>失败信息</option>\n";
$select.="\t<option value='type' $c_selected[type]>类型</option>\n";
$select.="\t<option value='card' $c_selected[card]>充值卡号</option>\n";
$select.="\t<option value='recharge_ok' $c_selected[recharge_ok]>充值成功</option>\n";
$select.="</select>\n";
$select.="搜索类型<select name=\"type\"  style=\"width:80px\" >";
$select.="\t<option value='equal' $t_selected[equal]>equal</option>\n";
$select.="\t<option value='contain' $t_selected[contain]>contain</option>\n";
$select.="\t<option value='unequal' $t_selected[unequal]>unequal</option>\n";
$select.="\t<option value='prefix' $t_selected[prefix]>prefix</option>\n";
$select.="\t<option value='postfix' $t_selected[postfix]>postfix</option>\n";
$select.="</select>\n";
$select.="Key<input type=\"text\" name=\"s_key\" value=\"$_REQUEST[s_key]\" size=16>&nbsp;\n";
$select.="<input type=\"submit\" value=\"搜索\">\n";

        $query=$db->query("SELECT count(*) AS count FROM USSD $where");
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
        $fenye=showpage("ussdinfo.php?action=$action&column=$column&type=$type&s_key=$s_key&",$page,$count,$perpage,true,true,"编");
	
        $query=$db->query("SELECT * from USSD  $where ORDER BY INSERTTIME DESC LIMIT $start_limit,$perpage");
        while($row=$db->fetch_array($query)) {
		if($row['type']==1 || $row['type']==6){
			$row['type']='Balance';
		}
		elseif($row['type']==2){
			if($row['recharge_ok']==1)
				$row['type']='Recharge(Done)';
			else $row['type']='Recharge(Failed)';
		}
		elseif($row['type']==9){
			if($row['recharge_ok']==1)
				$row['type']='Recharge2(Done)';
			else $row['type']='Recharge2(Failed)';
		}
		else{
			$row['type']='Normal';
		}
		$rsdb[]=$row;
	}
//print_r($rsdb);
        //require_once template("ussdinfo");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css">
<title>USSD信息查询</title>
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
                var i;
                for (i=0;i < form.elements.length; i++)
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
        //if ( checkbox && checkbox.type == 'checkbox' ) ;
        checkbox.checked ^= 1;
                if(checkbox.checked)
                        obj.className = 'even marked';
                else obj.className = 'even';
//              var ckpage=document.modifyform.elements['chkAll'+num];
                }

function check_search(obj)
{
        if(obj.column.value=="type"){
                var a=obj.s_key.value.toLocaleLowerCase();
                if(obj.s_key.value=="1" || obj.s_key.value.toLocaleLowerCase()=="balance"){
                        obj.s_key.value="1";
                }else if(obj.s_key.value=="2" || obj.s_key.value.toLocaleLowerCase().indexOf("recharge")>=0){
                        obj.s_key.value="2";
                }
                else obj.s_key.value="0";
        }
        else if(obj.column.value=="recharge_ok"){
                
        }
}
</script>
</head>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg"> 
    <td height="22" colspan="2" align="center"><strong>USSD信息</strong></td>
  </tr>
  <tr class="tdbg"> 
    <td wIdth="120" height="30"><strong>USSD信息导航</strong></td>
    <td height="30"><a href="ussd_ch.php" target=main>USSD发送</a></td>
  </tr>
</table>
<!--
<?php 

print <<<EOT
-->
  </tr>
</table>
<form action="ussdinfo.php?action=search" method="post" onSubmit="return check_search(this)">
$select
</form>
<form action="ussdinfo.php?action=del" method="post" name=myform onSubmit="return confirm('Are you sure to delete?')">
<table wIdth="100%"  border="0" cellspacing="2" cellpadding="2" nowrap=false>
        <tr class=title>
		<td wIdth="35" align=center height="25"><b>选择</b></td>
                <td align="center" width="120"><b>发送时间</b></td>
                <td align="center"><b>USSD内容</b></td>
                <td align="center"><b>USSD返回</b></td>
                <td align="center"><b>发送终端</b></td>
                <td align="center"><b>失败信息</b></td>
                <td align="center"><b>类型</b></td>
                <td align="center"><b>充值卡号</b></td>
        </tr>

<!-- 
EOT;
$j=0;
foreach($rsdb as $rs) {
$rs['USSD_RETURN']=htmlspecialchars($rs['USSD_RETURN']);
$rs['USSD_RETURN']=str_replace("\n", "<br>", $rs['USSD_RETURN']);
$rs['USSD_RETURN']=str_replace(" ", "&nbsp;", $rs['USSD_RETURN']);
print <<<EOT
-->
        <tr class="even" onMouseOver="mouseover(this)" onMouseOut="mouseout(this)"  onMouseDown="trclick(this)">
		<td align=center wIdth="25"><input name="Id{$j}" type='checkbox' onClick="return false" value="{$rs['id']}"></td>
                <td align="center">{$rs['INSERTTIME']}</td>
                <td width="10%" style="word-break : break-all; ">{$rs['USSD_MSG']}</td>
                <td width="50%" style="word-break : break-all; ">{$rs['USSD_RETURN']}</td>
                <td align="center">{$rs['TERMID']}</td>
                <td align="center">{$rs['ERROR_MSG']}</td>
                <td align="center">{$rs['type']}</td>
                <td align="center">{$rs['card']}</td>
    </tr>

<!--
EOT;
$j++;
}
print <<<EOT
-->
</table>
<input type="hIdden" name="boxs" value="{$j}">
                                        <tr>    
                                                <td height="30" ><input name="chkAll" type="checkbox" Id="chkAll" onclick=CheckAll(this.form) value="checkbox">               
                                          选择当前页面<input name="submit" type='submit' value='删除所选'><input name="button" type='button' value='删除全部' onClick="if(confirm('确认删除数据库中的所有收到的短信?')) window.location='?action=delall'"></td>                          
                                        </tr>
                                        <tr>
                                                <td  align=center>{$fenye}</td>
                                        </tr>
</form>
<!--
EOT;
?>

</body>
</html>
