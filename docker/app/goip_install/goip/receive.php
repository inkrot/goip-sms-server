<?php
require_once("session.php");
define("OK", true);
require_once("global.php");

	if($_GET['type']=="all"){
		if($_SESSION[goip_userid]>1)
			die("没有权限");
	}

if($_SESSION[goip_userid]<2)
	$otherh='<a href="sendinfo.php?type=all" target=main>所有人的发送</a>';
if($_GET['action']=='del') //删除
{
	if(admin_only()) WriteErrMsg('<br><li>forbidden</li>');
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
		$query=$db->query("DELETE FROM receive WHERE id IN ($Id)");
		WriteSuccessMsg("<br><li>删除短信成功</li>","receive.php");

	}
}
elseif($_REQUEST['action']=="delall"){
	if(admin_only()) WriteErrMsg('<br><li>forbidden</li>');
	$query=$db->query("DELETE FROM receive WHERE 1");
	WriteSuccessMsg("<br><li>Successfully deleted</li>","receive.php");
}
elseif($_GET['action']== 'read') { //查看
	if(!$_GET['id'])
		exit;
	$db->query("update receive set status=1 where id=$_GET[id]");
	$row=$db->fetch_array($db->query("select * from receive where id=$_GET[id]"));
	require_once("receive_read.htm");
}
else { //列表
	if($_SESSION['goip_permissions']<2){

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
                $c_selected['goipname']="selected";
        }

$select="搜索项目<select name=\"column\"  style=\"width:80px\" >";
$select.="\t<option value='goipname' $c_selected[goipname]>GoIP ID</option>\n";
$select.="</select>\n";
$select.="搜索类型<select name=\"type\"  style=\"width:80px\" >";
$select.="\t<option value='equal' $t_selected[equal]>equal</option>\n";
$select.="\t<option value='contain' $t_selected[contain]>contain</option>\n";
$select.="\t<option value='unequal' $t_selected[unequal]>unequal</option>\n";
$select.="\t<option value='prefix' $t_selected[prefix]>prefix</option>\n";
$select.="\t<option value='postfix' $t_selected[postfix]>postfix</option>\n";
$select.="</select>\n";
$select.="关键字<input type=\"text\" name=\"s_key\" value=\"$_REQUEST[s_key]\" size=16>&nbsp;\n";
$select.="<input type=\"submit\" value=\"搜索\">\n";

        //if(!$_REQUEST[goipname]) $goipname='goipname';
        //else $goipname="'$_REQUEST[goipname]'";
        //echo "!$_POST[goipname] SELECT count(*) AS count FROM receive where goipname='$goipname'";
        $query=$db->query("SELECT count(*) AS count FROM receive $where");
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
	$fenye=showpage("receive.php?action=$action&column=$column&type=$type&s_key=$s_key&",$page,$count,$perpage,true,true,"编");

	//$db->query("update receive set status=1 ORDER BY time DESC LIMIT $start_limit,$perpage");
	$query=$db->query("SELECT * from receive $where ORDER BY time DESC LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)){
		if($upid) $upid.=",".$row[id];
		else $upid=$row[id];
/*
		if($row[status]==0)
			$row[status]='<font color="#FF0000">未阅</font>';
		else 
			$row[status]='已阅';
*/
		$rsdb[]=$row;

		
	}
	if($upid) $db->query("update receive set status=1 where id in ($upid)");
	require_once("receive.htm");
}
//print_r($rsdb);
?>

