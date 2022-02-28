
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>上传文件</title>
<link href="style.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	background-color: #D6DFF7;
}
.invisible{display:none;}
-->
</style></head>

<SCRIPT language=javascript>
	
function addgroup(checked)
{
	if(checked.checked){
		checked.value=1;
		document.getElementById("selectgroup").style.display="none";
		document.getElementById("add").style.display="block";
	}
	else {
		checked.value=0;
		document.getElementById("selectgroup").style.display="block";
		document.getElementById("add").style.display="none";
	}
		
}

function check() 
{
	var strFileName=document.uploadform.img1.value;
	if (strFileName=="")
	{
    	alert("请选择上传文件!");
		document.uploadform.img1.focus();
    	return false;
  	}
/*
	if(!document.uploadform.checkadd.checked && document.uploadform.group.value=="0")
	{
    	alert("请选择一个组!");
		document.uploadform.group.focus();
    	return false;
  	}
*/
	if(document.uploadform.checkadd.checked && document.uploadform.name.value=="")
	{
    	alert("请输入组的名字!");
		document.uploadform.name.focus();
    	return false;
  	}

	for (i=1;i<select2.length;i++)
		for(j=0;j<select2[i].length;j++)
		{
			if(document.uploadform.name.value==select2[i][j].text){
				alert(select2[i][j].text+"已存在");
				document.uploadform.name.focus();
				return false;
			}
		}
	
}
</SCRIPT>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<?php

function check_prov(&$data, $prov, $prov0, &$msg, $codeflag = 0 )
{
	if($codeflag) $data=iconv("GB2312//IGNORE","UTF-8", $data);
	$tmpdata=$data;
	if(!$data){
		//echo "sdsdsz:$prov0";
		$data=$prov0;
	}
	$data=$prov[$data];
	if(!$data){
		$msg.='<br><li>不存在服务商: '.$tmpdata.'</li>';
		return 0;					
	}
	return 1;
}
require_once("session.php");
if($_SESSION['goip_permissions'] > 1)	
	die("需要admin权限！");	
define("OK", true);
require_once("global.php");

set_time_limit(120);
if ( $_POST["action"]=="mingsenupload")
{
	//echo "upload";
	
	$attach_name=$_FILES["img1"]['name'];
	$attach_size=$_FILES["img1"]['size'];
	$attachment=$_FILES["img1"]['tmp_name'];
	
	$db_uploadmaxsize='5120000';
	$db_uploadfiletype='txt cfg';
	$attachdir="upload";

	if(!$attachment || $attachment== 'none'){
		showerror('上传文件为空');
	} elseif(function_exists('is_uploaded_file') && !is_uploaded_file($attachment)){
		showerror('上传文件为空');
	} elseif(!($attachment && $attachment['error']!=4)){
		showerror('上传文件为空');
	}
	if ($attach_size>$db_uploadmaxsize){
		showerror("文件过大！");
	}
	
	$available_type = explode(' ',trim($db_uploadfiletype));
	$attach_ext = substr(strrchr($attach_name,'.'),1);
	$attach_ext=strtolower($attach_ext);
//	if($attach_ext == 'php' || empty($attach_ext) || !@in_array($attach_ext,$available_type)){
//		showerror('对不起，您上传的文件类型已被系统禁止！');
//	}
	$randvar=num_rand('15');
	$uploadname=$randvar.'.'.$attach_ext;

	if(!is_dir($attachdir)) {
			@mkdir($attachdir);
			@chmod($attachdir,0777);
		}
		
	$source=$attachdir.'/'.$uploadname;
	$returnfile="upload";
	$returnfile=$returnfile.'/'.$uploadname;
	if(function_exists("move_uploaded_file") && @move_uploaded_file($attachment, $source)){
		chmod($source,0777);
		$attach_saved = 1;
	}elseif(@copy($attachment, $source)){
		chmod($source,0777);
		$attach_saved = 1;
	}elseif(is_readable($attachment) && $attcontent=readover($attachment)){
		$attach_saved = 1;
		writeover($source,$attcontent);
		chmod($source,0777);
	}
	
	if($attach_saved == 1){	
	/* 检查列名*/	
	/* 
		$query=$db->query("desc receiver");
		while($rs=$db->fetch_array($query)){
			$vname[]=$rs[0];
			//echo $rs[0].'<br>';
		}
		$vnum=count($vname);
	*/
		if($_POST[checkadd]){
			$username=$_POST['name'];

			$info=$_POST['info'];
			$ErrMsg="";
			if(empty($username))
				$ErrMsg ='<br><li>请输入名称</li>';
			if($ErrMsg!="")
				WriteErrMsg($ErrMsg);
			else{
			
				$query=$db->query("SELECT id FROM `groups` WHERE name='$username' ");
				$rs=$db->fetch_array($query);
				if(empty($rs[0])){
					$query=$db->query("INSERT INTO `groups` (name,info,crowdid) value ('$username', '$info', $_POST[crowdid]) ");
					//WriteSuccessMsg("<br><li>Add group success</li>","groups.php");
					$groupiddb=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
					$groupid=$groupiddb[0];
				}
				else{
					$ErrMsg=$ErrMsg."<br><li>group [$username] have existed</li>";
					WriteErrMsg($ErrMsg);
				}
						
			}
		}
		else
			$groupid=$_POST[group];
		//print_r($_POST);
		//echo "groupid=$groupid";

		$query=$db->query("SELECT * from prov order by id");
		$pflag=0;
		while($row=$db->fetch_assoc($query)) {
			//echo $row[id]." ".$row[inter]."<br>";
			if(!$pflag && $row[prov]){
				$prov0=$row[prov];
				$pflag=1;
			}
			$prov[$row[prov]]=$row[id];
		}
		if(!$pflag)
			die("数据库没有设置服务商信息");
			
		$ext=substr($source, strlen($source) - 4);
		$name=array('no','name','info','tel','provider','name1','tel1','provider1','name2','tel2','provider2');
		$num=11;
		$date=date("YmdHis");
		if($ext=='.csv')
		{
			$fp   =   fopen($source,"r");
			/*解析列名*/  
			if( fgetcsv($fp,'1024',',')){   
				$namenum = count ($name);
				//print "<p> $num fields in line $row: <br>\n";
				//$row++;
				/*
				for ($c=0; $c < $num; $c++) {
					for($d=0;$d<$vame;$d++){
						if($data[$c]==$vname[$d])
							$sqlvname[]
					}
					print iconv("GB2312//IGNORE","UTF-8",$data[$c]) . "<br>\n";
				} 
				*/  
			}   
			$row=0;
			$srow=0;
			$sqlv="insert into receiver (";
			for ($c=0; $c < $num; $c++) {
				$sqlv.=$name[$c].",";
			}
			$sqlv.="upload_time) values";
			while($data   =   fgetcsv($fp,'1024',',')){   
				$row++;
				/*
				$no_t=$db->fetch_array($db->query("select id from receiver where no='".$data[0]."'"));
				if($no_t[0]){
					$Msg.='<br><li>已存在编号: '.$data[0].'</li>';
					continue;
				}
				*/
				if(!check_prov($data[4],$prov, $prov0, $Msg, 1)) continue;
				if(!check_prov($data[7],$prov, $prov0, $Msg, 1)) continue;
				if(!check_prov($data[10],$prov, $prov0, $Msg, 1)) continue;
				$srow++;
				//$num = count ($data);
				$sqln=NULL;
				//print "<p> $num fields in line $row: <br>\n";
	
				for ($c=0; $c < $num; $c++) {
					$sqln.="'$data[$c]',";
					//print iconv("GB2312//IGNORE","UTF-8",$data[$c]) . "<br>\n";
				}
				$sql.="($sqln '$date'),";
				if($srow%2000==0){
					$sqlutf8=iconv("GB2312//IGNORE","UTF-8", $sqlv.$sql);
					$sqlutf8[strlen($sqlutf8)-1]="";
					$db->query($sqlutf8);
					$sql="";
				}
			}
			if($sql){
				$sqlutf8=iconv("GB2312//IGNORE","UTF-8", $sqlv.$sql);
				$sqlutf8[strlen($sqlutf8)-1]="";
				$db->query($sqlutf8);
			}
			if($groupid){
				$sql1="insert into recvgroup (groupsid,recvid) select $groupid,id from receiver where upload_time='$date'";
				$db->query($sql1);
			}
			fclose($fp); 
			$Msg= "<br><li>导入接收人完毕,总共 $row 位，成功 $srow 位</li>".$Msg; 
			WriteSuccessMsg($Msg,"receiver.php");
		
		}
		else if($ext=='.xls') 
		{
			//echo "excel";
			require_once "excel_class.php";
			Read_Excel_File($source,$return);
			//$fp   =   fopen($source,"r");
			/*解析列名*/  
			//if($return[Sheet1][0]){   
				//$namenum = count($return[Sheet1][0]);

			//}    
			$srow=0;
			$sqlv="insert into receiver (";
			for ($c=0; $c < $num; $c++) {
				$sqlv.=$name[$c].",";
			}
			$sqlv.="upload_time) values";
			for ($row=1;$row<count($return[Sheet1]);$row++)
			{
/*
				$no_t=$db->fetch_array($db->query("select id from receiver where no='".$return[Sheet1][$row][0]."'"));
				if($no_t[0]){
					$Msg.='<br><li>已存在编号: '.$return[Sheet1][$row][0].'</li>';
					continue;
				}
*/
				if(!check_prov($return[Sheet1][$row][4],$prov, $prov0, $Msg)) continue;
				if(!check_prov($return[Sheet1][$row][7],$prov, $prov0, $Msg)) continue;
				if(!check_prov($return[Sheet1][$row][10],$prov, $prov0, $Msg)) continue;
				
				$srow++;
				$sqln=NULL;
				for ($j=0;$j<count($return[Sheet1][$row]);$j++)
				{
					//$sqlv.=$name[$j].",";
					$sqln.="'".$return[Sheet1][$row][$j]."',";
					//echo $return[Sheet1][$row][$j]."|";
				}
				$sql.="($sqln '$date'),";
				if($srow%2000==0){
					$sql[strlen($sql)-1]="";
					$db->query($sqlv.$sql);
					$sql="";
				}
			}
			if($sql){
				$sql[strlen($sql)-1]="";
				$db->query($sqlv.$sql);
			}
			if($groupid){
				$sql1="insert into recvgroup (groupsid,recvid) select $groupid,id from receiver where upload_time='$date'";
				//echo $sql1;
				$db->query($sql1);
                        }
			--$row;  
		}
		$Msg= "<br><li>导入接收人完毕,总共 $row 位，成功 $srow 位</li>".$Msg; 
		WriteSuccessMsg($Msg,"receiver.php");
		exit;
	}  
}

/*取出组*/
		if($_SESSION['goip_permissions'] < 2){
			$query=$db->query("SELECT groups.*,crowd.name as crowdname,crowd.id as crowdid FROM `groups`,crowd where crowd.id=groups.crowdid ORDER BY groups.crowdid,groups.id DESC ");
		}
		//$i=0;
		$crowdid=0;
		$rsdb=array();
		while($row=$db->fetch_array($query)) {
			$rsdb[$row[crowdid]][]=$row; //
			//$i++;
			if($crowdid != $row[crowdid] && $crowdid=$row[crowdid]) {$rscrowd[]=array($row[crowdid],$row[crowdname]);}
		}
		$crowdcount=count($rsdb);//总群数
		foreach($rsdb as $id => $crowdrs)
			$groupcount[]=count($crowdrs);//每个群的组数
		
echo "<script  language=javascript>\n";
echo "var select2 = new Array($crowdcount);\n";
echo "select2[0] = new Array();\nselect2[0][0] = new Option(\"请选择\", \" \");\n";
for ($i=1; $i<=$crowdcount; $i++) 
{
 echo "select2[$i] = new Array();\n";
 $j=0;
 foreach($rsdb[$rscrowd[$i-1][0]] as $group){
 	$name=$group[name];
	$id=$group[id];
 	echo "select2[$i][$j] = new Option(\"$name\",\"$id\");\n";
	$j++;
 }
}
print <<<EOT

function redirec(x)
{
 var temp = document.uploadform.group; 
 for (i=0;i<select2[x].length;i++)
 {
  temp.options[i]=new Option(select2[x][i].text,select2[x][i].value);
 }
 temp.options[0].selected=true;

}
</script>	
EOT;
	
function showerror($msg){
	//@extract($GLOBALS, EXTR_SKIP);
	//require_once GetLang('msg');
	//$lang[$msg] && $msg=$lang[$msg];
	echo "<script>"
		."alert('$msg');"
		."history.back();"
		."window.returnValue = '';"
		."window.close();"
		."</script>";
	exit;
}
/*
function num_rand($lenth){
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<$lenth;$i++){
		$randval.= mt_rand(0,9);
	}
	$randval=substr(md5($randval),mt_rand(0,32-$lenth),$lenth);
	return $randval;
}
*/
?> 

<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg"> 
    <td height="22" colspan="2" align="center"><strong>导入接收人</strong></td>
  </tr>
  <tr class="tdbg"> 
    <td wIdth="100" height="30"><strong>管理导航:</strong></td>
    <td height="30">导入接收人到一个组</td>
  </tr>
</table>
<br> 
<table wIdth="100%" border="0" align="center" cellpadding="0" cellspacing="1" class="border">
  <tr class="title">
    <td height="22"><strong> 提示： </strong></td>
  </tr>
  <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'">
    <td valign="middle"><ul>
	<li>本导入系统按组导入接收人数据，导入时可以指定一个组或不指定组，支持.xls和.csv两种格式   </strong><a href="example/uploadreceiver.xls" target="_blank"> [例子]</a></li>
	<li>文件中的列顺序必须与数据库receiver表完全相同，列参考:（编号 姓名 备注信息 号码 服务商 关联人1姓名 关联人1号码 关联人1服务商 关联人2姓名 关联人2号码 关联人2服务商） 共11项</li>
	<li>上传文件需要服务器支持文件上传并保证数据尺寸小于允许上传的上限</li>

   	 </ul>
	</td>
  </tr>
</table>
<br>
<FORM  name=uploadform action="<?php echo $PHP_SELF ?>" method="POST" enctype="multipart/form-data" onSubmit="return check()">
<center>
<div id="selectgroup"> 
<tr><td height="50" >要添加到那个组？
<select name="crowd" style="width:135" onChange="redirec(document.uploadform.crowd.options.selectedIndex)">
  <option value="0" selected>请选择</option>
<?php
$i=1;
foreach($rscrowd as $crowd) {
print <<<EOT
      <option value={$i} >{$crowd[1]}</option>
	  
EOT;
$i++;
}
?>
</select>群
<select name="group">
 <option value="0" selected>请选择</option>
</select>组

	 </td></tr>

</div> 
<br>
<input name="checkadd" type="checkbox" Id="checkadd" onclick="addgroup(this.form.checkadd)" value="0">新建一个组
<br>
<div id="add" style="display:none;">
  <table wIdth="300" border="0" align="center" cellpadding="2" cellspacing="1" >
    <tr class="title"> 
      <td height="22" colspan="2"> <div align="center"><strong>添 加 组</strong></div></td>
    </tr>
    <tr> 
      <td wIdth="100" align="right" class="tdbg"><strong>名称:</strong></td>
      <td class="tdbg"><input type="input" name="name"> </td>
    </tr>
	
    <tr> 
      <td wIdth="100" align="right" class="tdbg"><strong>所在群:</strong></td>
      <td class="tdbg">
	  	   <select name="crowdid" style="width:135px" >  

<?php
$i=0 ;
foreach($rscrowd as $crs) {
if($i==0) {
	$i=1;
?>
	<option value="<?php print($crs[0]) ?>" selected><?php print($crs[1]) ?></option>

<?php }
else {
?>
	<option value="<?php print($crs[0]) ?>" ><?php print($crs[1]) ?></option>
<?php } 
} ?>

</select>
</td>
    </tr>
    <tr> 
      <td wIdth="100" align="right" class="tdbg"><strong>备注信息:</strong></td>
      <td class="tdbg"><input type="input" name="info"> </td>
    </tr>
  </table>

</div> 
<tr><td>
<br>
请上传要导入的文件<INPUT TYPE="HIdDEN"  name="action" value="mingsenupload">
<input type=file name=img1><INPUT TYPE="SUBMIT" value="上 传">
</td></tr>
</center>
</FORM>
</body>
</html>
