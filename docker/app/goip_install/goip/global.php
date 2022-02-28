<?php

//!defined('OK') && exit('ForbIdden');
$perpage='30';
require_once('inc/conn.inc.php');
//require_once('../inc/conn.php');
/*
if(!isset($_SESSION['goip_username'])) {
	require_once ('login.php');
}
*/
//$PHP_SELF=$_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
//$URL='http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF, '/')+1);
//echo $URL;
//print_r($_SERVER);
$URL=@$_SERVER['HTTP_REFERER'];
//echo $URL;

function num_rand($lenth){
        mt_srand((double)microtime() * 1000000);
        for($i=0;$i<$lenth;$i++){
		if($i==0) $randval.= mt_rand(1,9); 
		else $randval.= mt_rand(0,9);
        }
        //$randval=substr(md5($randval),mt_rand(0,32-$lenth),$lenth);
        return $randval;
}

function sendto_cron($cmd="goip")
{
	global $goipcronport;
	if(!$goipcronport) $goipcronport=44444;
	$flag=0;        
	/* 此是最新计划， 唤醒服务进程*/
	if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
		echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
		exit;           
	}               
	if (socket_sendto($socket,$cmd, strlen($cmd), 0, "127.0.0.1", $goipcronport)===false)
		echo ("sendto error");
	for($i=0;$i<3;$i++){
		$read=array($socket);
		$err=socket_select($read, $write = NULL, $except = NULL, 5);
		if($err>0){             
			if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port))==false){
				//echo("recvform error".socket_strerror($ret)."<br>");
				continue;       
			}               
			else{           
				if($buf="OK"){  
					$flag=1;        
					break;          
				}               
			}               
		}               
	}
	if($flag)
		echo "已更新";
        else
                echo "已更新,但goipcron进程未响应，请检查该进程";
}

function multi($count,$page,$numofpage,$url) {
	if ($numofpage<=1){
		return ;
	}else{
		$fengye="<a href=\"{$url}&page=1\"><< </a>";
		$flag=0;
		for($i=$page-3;$i<=$page-1;$i++)
		{
			if($i<1) continue;
			$fengye.=" <a href={$url}&page=$i>&nbsp;$i&nbsp;</a>";
		}
		$fengye.="&nbsp;&nbsp;<b>$page</b>&nbsp;";
		if($page<$numofpage)
		{
			for($i=$page+1;$i<=$numofpage;$i++)
			{
				$fengye.=" <a href={$url}&page=$i>&nbsp;$i&nbsp;</a>";
				$flag++;
				if($flag==4) break;
			}
		}
		$fengye.=" <input type='text' size='2' style='height: 16px; border:1px solId #E7E3E7' onkeydown=\"javascript: if(event.keyCode==13) location='{$url}&page='+this.value;\"> <a href=\"{$url}&page=$numofpage\"> >></a> &nbsp;(共 $numofpage 页)";
		return $fengye;
	}
}

//**************************************************
//过程名:showpage
//作  用:显示“上一页 下一页”等信息
//参  数:sfilename  ----链结位址
//$CurrentPage
//       totalnumber ----总数量
//       maxperpage  ----每页数量
//       ShowTotal   ----是否显示总数量
//       ShowAllPages ---是否用下拉清单显示所有页面以供跳转。有某些页面不能使用，否则会出现JS错误。
//       strUnit     ----计数单位
//**************************************************
function showpage($sfilename,$CurrentPage,$totalnumber,$maxperpage,$ShowTotal,$ShowAllPages,$strUnit){
	if($totalnumber%$maxperpage==0)
    	$n= $totalnumber / $maxperpage;
  	else
    	$n= (int)($totalnumber / $maxperpage)+1;
  	
  	$strTemp= "<table align='center'><tr><td>";
	if($ShowTotal==true)
		$strTemp=$strTemp . "共 <b>" . $totalnumber . "</b> " . $strUnit . " &nbsp;&nbsp;";
	if($CurrentPage<2)
    	$strTemp=$strTemp . "首页 上一页&nbsp;";
  	else{
    	$strTemp=$strTemp . "<a href='" . $sfilename . "page=1'>首页</a>&nbsp;";
    	$strTemp=$strTemp . "<a href='" . $sfilename . "page=" . ($CurrentPage-1) . "'>上一页</a>&nbsp;";
  	}

  	if ($n-$CurrentPage<1)
    		$strTemp=$strTemp . "下一页 尾页";
  	else{
    		$strTemp=$strTemp . "<a href='" . $sfilename . "page=" . ($CurrentPage+1) . "'>下一页</a>&nbsp;";
    		$strTemp=$strTemp . "<a href='" . $sfilename . "page=" . $n . "'>尾页</a>";
  	}
	
   	$strTemp=$strTemp . " &nbsp;页次：<strong><font color=red>" . $CurrentPage . "</font>/" . $n . "</strong>页 ";
    $strTemp=$strTemp . " &nbsp;<b>" . $maxperpage . "</b>" . $strUnit . "/页";
	
	if( $ShowAllPages=true){
		$strTemp=$strTemp . " &nbsp;转到：<select name='page' size='1' onchange=javascript:window.location='" . $sfilename . "page=" . "'+this.options[this.selectedIndex].value;>" ;
    	for($i=1;$i<=$n;$i++){
    		$strTemp=$strTemp . "<option value='" . $i . "'";
			if( (int)($CurrentPage)==(int)($i))
				$strTemp=$strTemp . " selected ";
			$strTemp=$strTemp . ">第" . $i . "页</option>"   ;
	    }
		$strTemp=$strTemp . "</select>";
	}
	$strTemp=$strTemp . "</td></tr></table>";
	return $strTemp;
}

function showpage2($sfilename,$CurrentPage,$totalnumber,$maxperpage,$ShowTotal,$ShowAllPages,$strUnit,$form,$post){
	if($totalnumber%$maxperpage==0)
    	$n= $totalnumber / $maxperpage;
  	else
    	$n= (int)($totalnumber / $maxperpage)+1;
  	
  	$strTemp= "<table align='center'><tr><td>";
	if($ShowTotal==true)
		$strTemp=$strTemp . "共 <b>" . $totalnumber . "</b> " . $strUnit . " &nbsp;&nbsp;";
	if($CurrentPage<2)
    	$strTemp=$strTemp . "首页 上一页&nbsp;";
  	else{
    	$strTemp=$strTemp . "<span class='spanpage' onclick='return changepage(\"".$sfilename."page=1\", ".$form.")'>首页</span>&nbsp;";
    	$strTemp=$strTemp . "<span class='spanpage' onclick='return changepage(\"".$sfilename."page=".($CurrentPage-1)."\", ".$form.")'>上一页</span>&nbsp;";
  	}

  	if ($n-$CurrentPage<1)
    		$strTemp=$strTemp . "下一页 尾页";
  	else{
    		$strTemp=$strTemp . "<span class='spanpage' onclick='return changepage(\"".$sfilename."page=".($CurrentPage+1)."\", ".$form.")'>下一页</span>&nbsp;";
    		$strTemp=$strTemp . "<span class='spanpage' onclick='return changepage(\"".$sfilename."page=".$n."\", ".$form.")'>尾页</span>&nbsp;";
  	}
	
   	$strTemp=$strTemp . " &nbsp;页次：<strong><font color=red>" . $CurrentPage . "</font>/" . $n . "</strong>页 ";
    $strTemp=$strTemp . " &nbsp;<b>" . $maxperpage . "</b>" . $strUnit . "/页";
	
	if( $ShowAllPages=true){
		$strTemp=$strTemp . " &nbsp;转到：<select name='page' size='1' onchange='return selectchangepage(\"".$sfilename."\",this.options[this.selectedIndex].value, ".$form.")'>" ;
    	for($i=1;$i<=$n;$i++){
    		$strTemp=$strTemp . "<option value='" . $i . "'";
			if( (int)($CurrentPage)==(int)($i))
				$strTemp=$strTemp . " selected ";
			$strTemp=$strTemp . ">第" . $i . "页</option>"   ;
	    }
		$strTemp=$strTemp . "</select>";
	}
	$strTemp=$strTemp . "</td></tr></table>";
	return $strTemp;
}



function template($template) {
	return "../template/admin/$template.htm";
}

function WriteErrMsg($ErrMsg1)
{
	$strErr="<html><head><title>Error Information</title><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" ;
	$strErr=$strErr."<link href='style.css' rel='stylesheet' type='text/css'></head><body>" ;
	$strErr=$strErr."<table cellpadding=2 cellspacing=1 border=0 wIdth=400 class='border' align=center>"; 
	$strErr=$strErr."  <tr align='center'><td height='22' class='title'><strong>错误信息</strong></td></tr>" ;
	$strErr=$strErr."  <tr><td height='100' class='tdbg' valign='top'><b>原因:</b><br> $ErrMsg1</td></tr>" ;
	$strErr=$strErr."  <tr align='center'><td class='tdbg'><a href=javascript:history.back();>&lt;&lt; 返回</a></td></tr>" ;
	$strErr=$strErr."</table>" ;
	$strErr=$strErr."</body></html>" ;
	echo $strErr;
	exit;
}

//'**************************************************
////'过程名:WriteSuccessMsg
//'作  用:显示成功提示资讯
//'参  数:无
//**************************************************
function WriteSuccessMsg($SuccessMsg,$URL)
{
	$strErr="<html><head><title>Success Information</title><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" ;
	$strErr=$strErr."<link href='style.css' rel='stylesheet' type='text/css'></head><body>" ;
	$strErr=$strErr."<table cellpadding=2 cellspacing=1 border=0 wIdth=400 class='border' align=center>"; 
	$strErr=$strErr."  <tr align='center'><td height='22' class='title'><strong>恭喜你</strong></td></tr>" ;
	$strErr=$strErr."  <tr><td height='100' class='tdbg' valign='top'>$SuccessMsg</td></tr>" ;
	$strErr=$strErr."  <tr align='center'><td class='tdbg'><a href=$URL>确定</a></td></tr>" ;
	$strErr=$strErr."</table>" ;
	$strErr=$strErr."</body></html>" ;
	echo $strErr;
	exit;
}

function check_sms_remain_count($db,$goipid,$goipname,$count=0)
{
	$flag=true;
	//echo "conut:$count<br>";
	$rs=$db->fetch_array($db->query("select remain_count,remain_count_d from goip where id=$goipid"));
	if($rs[0]!=-1){
		$total=$rs[0]-$count;
		if($total<=0){
			$total=0;
			if($count>0) $db->query("update goip set remain_count=$total where id=$goipid");
			echo "GoIP Line($goipname) remain count is down<br>";
			$flag=false;
		}
		else if($count>0) $db->query("update goip set remain_count=$total where id=$goipid");
	}
	if($rs[1]!=-1){
		$total=$rs[1]-$count;
		if($total<=0){
			$total=0;
			if($count>0) $db->query("update goip set remain_count_d=$total where id=$goipid");
			echo "GoIP Line($goipname) remain count of this day is down<br>";
			$flag=false;
		}
		else if($count>0) $db->query("update goip set remain_count_d=$total where id=$goipid");
	}
	return $flag;
}

function get_count_from_sms($a){
	if(function_exists('mb_strlen') && strlen($a)!=mb_strlen($a, 'utf8')) {
		$len=mb_strlen($a, 'utf8');
		if($len<=70) $total=1;
		else $total=ceil($len/67);
	}
	else {  
		$len=strlen($a);
		if($len<=160) $total=1;
		else $total=ceil($len/153);
	}
	if(!function_exists('mb_strlen')) echo "Warning: need install php-mbstring module<br>";
	return $total;
}

function operator_forbid()
{
	if($_SESSION['goip_permissions'] == 4) return true;
	else return false;
}

function owner_forbid()
{
	if($_SESSION['goip_permissions'] == 5) return true;
	else return false;
}

function operator_owner_forbid()
{
	if($_SESSION['goip_permissions'] == 4 || $_SESSION['goip_permissions'] == 5) return true;
	else return false;
}

function admin_only()
{
	if($_SESSION['goip_permissions'] > 1) return true;
	else return false;
}

?>
