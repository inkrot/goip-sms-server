<?php

//!defined('OK') && exit('ForbIdden');
$perpage='30';
require_once('../inc/conn.inc.php');
//require_once('../inc/conn.php');
//$PHP_SELF=$_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
//$URL='http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF, '/')+1);
$URL=$_SERVER['HTTP_REFERER'];

function sendto_cron($cmd="goip", $log=1)
{
	global $goipcronport;
	if(!$goipcronport) $goipcronport=44444;
	$flag=0;        
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
	if($log){
		if($flag)
			echo "Mydify Success";
		else
			echo "Mydify Success,but cannot get response from process named \"goipcron\". please check this process.";
	}
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
		$fengye.=" <input type='text' size='2' style='height: 16px; border:1px solId #E7E3E7' onkeydown=\"javascript: if(event.keyCode==13) location='{$url}&page='+this.value;\"> <a href=\"{$url}&page=$numofpage\"> >></a> &nbsp;(共 $numofpage 頁)";
		return $fengye;
	}
}

//**************************************************
//過程名:showpage
//作  用:顯示“上一頁 下一頁”等信息
//參  數:sfilename  ----鏈結位址
//$CurrentPage
//       totalnumber ----總數量
//       maxperpage  ----每頁數量
//       ShowTotal   ----是否顯示總數量
//       ShowAllPages ---是否用下拉清單顯示所有頁面以供跳轉。有某些頁面不能使用，否則會出現JS錯誤。
//       strUnit     ----計數單位
//**************************************************
function showpage($sfilename,$CurrentPage,$totalnumber,$maxperpage,$ShowTotal,$ShowAllPages,$strUnit){
	if($totalnumber%$maxperpage==0)
    	$n= $totalnumber / $maxperpage;
  	else
    	$n= (int)($totalnumber / $maxperpage)+1;
  	
  	$strTemp= "<table align='center'><tr><td>";
	if($ShowTotal==true)
		$strTemp=$strTemp . "Total <b>" . $totalnumber . "</b> " . $strUnit . " &nbsp;&nbsp;";
	if($CurrentPage<2)
    	$strTemp=$strTemp . "index backward&nbsp;";
  	else{
    	$strTemp=$strTemp . "<a href='" . $sfilename . "page=1'>index</a>&nbsp;";
    	$strTemp=$strTemp . "<a href='" . $sfilename . "page=" . ($CurrentPage-1) . "'>backward</a>&nbsp;";
  	}

  	if ($n-$CurrentPage<1)
    		$strTemp=$strTemp . "forward end";
  	else{
    		$strTemp=$strTemp . "<a href='" . $sfilename . "page=" . ($CurrentPage+1) . "'>forward</a>&nbsp;";
    		$strTemp=$strTemp . "<a href='" . $sfilename . "page=" . $n . "'>end</a>";
  	}
	
   	$strTemp=$strTemp . " &nbsp;pages:<strong><font color=red>" . $CurrentPage . "</font>/" . $n . "</strong>page ";
    $strTemp=$strTemp . " &nbsp;<b>" . $maxperpage . "</b>" . $strUnit . "/page";
	
	if( $ShowAllPages=true){
		$strTemp=$strTemp . " &nbsp;goto:<select name='page' size='1' onchange=javascript:window.location='" . $sfilename . "page=" . "'+this.options[this.selectedIndex].value;>" ;
    	for($i=1;$i<=$n;$i++){
    		$strTemp=$strTemp . "<option value='" . $i . "'";
			if( (int)($CurrentPage)==(int)($i))
				$strTemp=$strTemp . " selected ";
			$strTemp=$strTemp . ">The" . $i . "page</option>"   ;
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
		$strTemp=$strTemp . "Total <b>" . $totalnumber . "</b> " . $strUnit . " &nbsp;&nbsp;";
	if($CurrentPage<2)
    	$strTemp=$strTemp . "index backward&nbsp;";
  	else{
    	$strTemp=$strTemp . "<span class='spanpage' onclick='return changepage(\"".$sfilename."page=1\", ".$form.")'>index</span>&nbsp;";
    	$strTemp=$strTemp . "<span class='spanpage' onclick='return changepage(\"".$sfilename."page=".($CurrentPage-1)."\", ".$form.")'>backward</span>&nbsp;";
  	}

  	if ($n-$CurrentPage<1)
    		$strTemp=$strTemp . "forward end";
  	else{
    		$strTemp=$strTemp . "<span class='spanpage' onclick='return changepage(\"".$sfilename."page=".($CurrentPage+1)."\", ".$form.")'>forward</span>&nbsp;";
    		$strTemp=$strTemp . "<span class='spanpage' onclick='return changepage(\"".$sfilename."page=".$n."\", ".$form.")'>end</span>&nbsp;";
  	}
	
   	$strTemp=$strTemp . " &nbsp;pages：<strong><font color=red>" . $CurrentPage . "</font>/" . $n . "</strong>page ";
    $strTemp=$strTemp . " &nbsp;<b>" . $maxperpage . "</b>" . $strUnit . "/page";
	
	if( $ShowAllPages=true){
		$strTemp=$strTemp . " &nbsp;goto：<select name='page' size='1' onchange='return selectchangepage(\"".$sfilename."\",this.options[this.selectedIndex].value, ".$form.")'>" ;
    	for($i=1;$i<=$n;$i++){
    		$strTemp=$strTemp . "<option value='" . $i . "'";
			if( (int)($CurrentPage)==(int)($i))
				$strTemp=$strTemp . " selected ";
			$strTemp=$strTemp . ">The" . $i . "page</option>"   ;
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
	$strErr="<html><head><title>Error Information</title><meta http-equiv='Content-Type' content='text/html; charset=ISO-8859-1'>" ;
	$strErr=$strErr."<link href='../style.css' rel='stylesheet' type='text/css'></head><body>" ;
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
////'過程名:WriteSuccessMsg
//'作  用:顯示成功提示資訊
//'參  數:無
//**************************************************
function WriteSuccessMsg($SuccessMsg,$URL)
{
	$strErr="<html><head><title>Success Information</title><meta http-equiv='Content-Type' content='text/html; charset=ISO-8859-1'>" ;
	$strErr=$strErr."<link href='../style.css' rel='stylesheet' type='text/css'></head><body>" ;
	$strErr=$strErr."<table cellpadding=2 cellspacing=1 border=0 wIdth=400 class='border' align=center>"; 
	$strErr=$strErr."  <tr align='center'><td height='22' class='title'><strong>Congratulation</strong></td></tr>" ;
	$strErr=$strErr."  <tr><td height='100' class='tdbg' valign='top'>$SuccessMsg</td></tr>" ;
	$strErr=$strErr."  <tr align='center'><td class='tdbg'><a href=$URL>Apply</a></td></tr>" ;
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
			echo "GoIP Line($goipname) remain count is done<br>";
			$flag=false;
			if($count>0) {
				$cmd="";
				//sendto_cron($, 0);
			}
		}
		else if($count>0) $db->query("update goip set remain_count=$total where id=$goipid");
	}
	if($rs[1]!=-1){
		$total=$rs[1]-$count;
		if($total<=0){
			$total=0;
			if($count>0) $db->query("update goip set remain_count_d=$total where id=$goipid");
			echo "GoIP Line($goipname) remain count of this day is done<br>";
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
		if(!function_exists('mb_strlen')) echo "Warning: need install php-mbstring module.<br>";
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
