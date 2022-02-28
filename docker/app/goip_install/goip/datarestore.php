<?php 
require_once("session.php");
if($_SESSION['goip_permissions'] > 1)	
	die("need admin permissions!");	
ignore_user_abort(true);
set_time_limit(0);
ini_set("memory_limit", "200M");
ini_set("upload_max_filesize", "100M");
echo ini_get('upload_max_filesize');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>数据导入</title>
<meta name="Author" content="Gaby_chen">
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg"> 
    <td height="22" colspan="2" align="center"><strong>数据管理</strong></td>
  </tr>
  <tr class="tdbg"> 
    <td wIdth="100" height="30"><strong>管理导航:</strong></td>
    <td height="30"><a href="databackup.php"  target=main>数据备份</a> | <a href="datarestore.php" target=main>数据导入</a></td>
  </tr>
</table>
<?php
define('OK', true);
require_once('inc/conn.inc.php');

global $mysqlhost, $mysqluser, $mysqlpwd, $mysqldb;
$mysqlhost=$dbhost; //host name
$mysqluser=$dbuser;              //login name
$mysqlpwd=$dbpw;              //password
$mysqldb=$dbname;        //name of database


require_once("datamydb.php");
$d=new datadb($mysqlhost,$mysqluser,$mysqlpwd,$mysqldb);
/******界面*/
if(!$_POST['act']&&!$_SESSION['goip_data_file']){
/**********************/
?>

<br> 
<table wIdth="100%" border="0" align="center" cellpadding="0" cellspacing="1" class="border">
  <tr class="title">
    <td height="22"><strong> 提示： </strong></td>
  </tr>
  <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'">
    <td valign="middle"><ul>
        <li>本功能在恢复备份数据的同时，将全部覆盖原有数据，请确定是否需要恢复，以免造成数据损失
        <li>数据恢复功能只能恢复由本系统导出的数据文件，其他软件导出格式可能无法识别
        <li>从本地恢复数据需要服务器支持文件上传并保证数据尺寸小于允许上传的上限，否则只能使用从服务器恢复</li>
	<li>如果您使用了分卷备份，只需手工导入文件卷1，其他数据文件会由系统自动导入</li>
   	 </ul>
	</td>
  </tr>
</table>
<br>
<form action="" method="post" enctype="multipart/form-data" name="datarestore.php">
<table wIdth="100%" border="0" align="center" cellpadding="0" cellspacing="1" class="border">
  <tr class="title"> 
    <td height="22" colspan="2" align="center"><strong>数据恢复</strong></td>
  </tr>
  <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'"> 
    <td colspan="2">备份方式</td>
  </tr>
  <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'"> 
    <td><input type="radio" name="restorefrom" value="server" checked>
从服务器文件恢复</td>
  <td><select name="serverfile">
    <option value="">-请选择-</option>
    <?php
$handle=opendir('./backup');
while ($file = readdir($handle)) {
    if(eregi("^[0-9]{8,8}([0-9a-z_]+)(\.sql)$",$file)) echo "<option value='$file'>$file</option>";}
closedir($handle); 
?>
  </select></td>
  </tr>
   <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'"> 
     <td><input type="radio" name="restorefrom" value="localpc">
从本地文件恢复</td>
     <td><input type="hidden" name="MAX_FILE_SIZE" value="100000000">
      <input type="file" name="myfile"></td>
  </tr>
   <tr align="center" class="tdbg" onmouseover="this.style.backgroundColor='#BFDFFF'" onmouseout="this.style.backgroundColor=''"> 
    <td colspan="2"><input type="submit" name="act" value="恢复"></td>
  </tr>
</table> 
</form>

<?php /**************************界面结束*/}/*************************************/
/****************************主程序*/if($_POST['act']=="恢复"){/**************/
/***************服务器恢复*/if($_POST['restorefrom']=="server"){/**************/
if(!$_POST['serverfile'])
	{$msgs[]="您选择从服务器文件恢复备份，但没有指定备份文件";
	 show_msg($msgs); pageend();	}
if(!eregi("_v[0-9]+",$_POST['serverfile']))
	{$filename="./backup/".$_POST['serverfile'];
	if(import($filename)) $msgs[]="备份文件".$_POST['serverfile']."成功导入数据库";
	else $msgs[]="备份文件".$_POST['serverfile']."导入失败";
	show_msg($msgs); pageend();		
	}
else
	{
	$filename="./backup/".$_POST['serverfile'];
	if(import($filename)) $msgs[]="备份文件".$_POST['serverfile']."成功导入数据库";
	else {$msgs[]="备份文件".$_POST['serverfile']."导入失败";show_msg($msgs);pageend();}
	$voltmp=explode("_v",$_POST['serverfile']);
	$volname=$voltmp[0];
	$volnum=explode(".sq",$voltmp[1]);
	$volnum=intval($volnum[0])+1;
	$tmpfile=$volname."_v".$volnum.".sql";
	if(file_exists("./backup/".$tmpfile))
		{
		$msgs[]="程序将在3秒钟后自动开始导入此分卷备份的下一部份：文件".$tmpfile."，请勿手动中止程序的运行，以免数据库结构受损";
		$_SESSION['goip_data_file']=$tmpfile;
		show_msg($msgs);
		sleep(3);
		echo "<script language='javascript'>"; 
		echo "location='restore.php';"; 
		echo "</script>"; 
		}
	else
		{
		$msgs[]="此分卷备份全部导入成功";
		show_msg($msgs);
		}
	}
/**************服务器恢复结束*/}/********************************************/
/*****************本地恢复*/if($_POST['restorefrom']=="localpc"){/**************/
	switch ($_FILES['myfile']['error'])
	{
	case 1:
	case 2:
	$msgs[]="您上传的文件大于服务器限定值，上传未成功";
	break;
	case 3:
	$msgs[]="未能从本地完整上传备份文件";
	break;
	case 4:
	$msgs[]="从本地上传备份文件失败";
	break;
    case 0:
	break;
	}
	if($msgs){show_msg($msgs);pageend();}
$fname=date("Ymd",time())."_.sql";
if (is_uploaded_file($_FILES['myfile']['tmp_name'])) {

$attach_name=$_FILES["myfile"]['name'];
	$attach_size=$_FILES["myfile"]['size'];
	$attachment=$_FILES["myfile"]['tmp_name'];
	$db_uploadmaxsize='100000000';
	$db_uploadfiletype='sql';
	$attachdir="backup/";
	if(!$attachment || $attachment== 'none'){
		showerror('upload_content_error');
	} elseif(function_exists('is_uploaded_file') && !is_uploaded_file($attachment)){
		showerror('upload_content_error');
	} elseif(!($attachment && $attachment['error']!=4)){
		showerror('upload_content_error');
	}
	if ($attach_size>$db_uploadmaxsize){
		showerror("upload_size_error");
	}
	
	$available_type = explode(' ',trim($db_uploadfiletype));
	$attach_ext = substr(strrchr($attach_name,'.'),1);
	$attach_ext=strtolower($attach_ext);
	if($attach_ext == 'php' || empty($attach_ext) || !@in_array($attach_ext,$available_type)){
		showerror('upload_type_error');
	}
	$randvar=num_rand('15');
	$uploadname=$randvar.'.'.$attach_ext;

	if(!is_dir($attachdir)) {
			@mkdir($attachdir);
			@chmod($attachdir,0777);
		}
		
	$source=$attachdir.'/'.$fname;
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

	
//echo "./backup/".$fname;
   // copy($_FILES['myfile']['tmp_name'], "./backup/".$fname);
   }

if (file_exists("./backup/".$fname)) 
	{
	$msgs[]="本地备份文件上传成功";
	if(import("./backup/".$fname)) {$msgs[]="本地备份文件成功导入数据库"; unlink("./backup/".$fname);}
	else $msgs[]="本地备份文件导入数据库失败";
	}
else ($msgs[]="从本地上传备份文件失败");
show_msg($msgs);
/****本地恢复结束*****/}/****************************************************/
/****************************主程序结束*/}/**********************************/
/*************************剩余分卷备份恢复**********************************/
if(!$_POST['act']&&$_SESSION['goip_data_file'])
{
	$filename="./backup/".$_SESSION['goip_data_file'];
	if(import($filename)) $msgs[]="备份文件".$_SESSION['goip_data_file']."成功导入数据库";
	else {$msgs[]="备份文件".$_SESSION['goip_data_file']."导入失败";show_msg($msgs);pageend();}
	$voltmp=explode("_v",$_SESSION['goip_data_file']);
	$volname=$voltmp[0];
	$volnum=explode(".sq",$voltmp[1]);
	$volnum=intval($volnum[0])+1;
	$tmpfile=$volname."_v".$volnum.".sql";
	if(file_exists("./backup/".$tmpfile))
		{
		$msgs[]="程序将在3秒钟后自动开始导入此分卷备份的下一部份：文件".$tmpfile."，请勿手动中止程序的运行，以免数据库结构受损";
		$_SESSION['goip_data_file']=$tmpfile;
		show_msg($msgs);
		sleep(3);
		echo "<script language='javascript'>"; 
		echo "location='restore.php';"; 
		echo "</script>"; 
		}
	else
		{
		$msgs[]="此分卷备份全部导入成功";
		unset($_SESSION['goip_data_file']);
		show_msg($msgs);
		}
}
/**********************剩余分卷备份恢复结束*******************************/
function import($fname)
{global $d;
$sqls=file($fname);
foreach($sqls as $sql)
	{
	str_replace("\r","",$sql);
	str_replace("\n","",$sql);
	if(!$d->query(trim($sql))) return false;
	}
return true;
}
function show_msg($msgs)
{
	$strErr="<html><head><title>Error Information</title><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" ;
	$strErr=$strErr."<link href='style.css' rel='stylesheet' type='text/css'></head><body>" ;
	$strErr=$strErr."<br><br><table cellpadding=2 cellspacing=1 border=0 wIdth=400 class='border' align=center>"; 
	$strErr=$strErr."  <tr align='center'><td height='22' class='title'><strong>信息</strong></td></tr>" ;
	$strErr=$strErr."  <tr><td height='100' class='tdbg' valign='top'><b>提示:</b><br><ul>";
	while (list($k,$v)=each($msgs))
	$strErr=$strErr."<li>".$v."</li>";
	$strErr=$strErr."</ul></td></tr>" ;
	$strErr=$strErr."  <tr align='center'><td class='tdbg'><a href=javascript:history.back();>&lt;&lt; 返回</a></td></tr>" ;
	$strErr=$strErr."</table>" ;
	$strErr=$strErr."</body></html>" ;
	echo $strErr;
	exit;
}


function pageend()
{
exit();
}

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

function num_rand($lenth){
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<$lenth;$i++){
		$randval.= mt_rand(0,9);
	}
	$randval=substr(md5($randval),mt_rand(0,32-$lenth),$lenth);
	return $randval;
}
?>

<br><br>
</body> 
</html> 
