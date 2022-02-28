<?php 
require_once("session.php");
if($_SESSION['goip_permissions'] > 1)	
	die("need admin permissions!");	
ignore_user_abort(true);
set_time_limit(0);
ini_set("memory_limit", "200M");
ini_set("upload_max_filesize", "100M");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Data Import</title>
<meta name="Author" content="Gaby_chen">
<link href="../style.css" rel="stylesheet" type="text/css">
</head>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg"> 
    <td height="22" colspan="2" align="center"><strong>Data Manage</strong></td>
  </tr>
  <tr class="tdbg"> 
    <td wIdth="70" height="30"><strong>Navigation Manage:</strong></td>
    <td height="30"><a href="databackup.php"  target=main>Data Backup</a> | <a href="datarestore.php" target=main>Data Import</a></td>
  </tr>
</table>
<?php
define("OK", true);
require_once('../inc/conn.inc.php');

global $mysqlhost, $mysqluser, $mysqlpwd, $mysqldb;
$mysqlhost=$dbhost; //host name
$mysqluser=$dbuser;              //login name
$mysqlpwd=$dbpw;              //password
$mysqldb=$dbname;        //name of database


require_once("datamydb.php");
$d=new datadb($mysqlhost,$mysqluser,$mysqlpwd,$mysqldb);
/******界面*/
@session_start();
if(!$_POST['act']&&!$_SESSION['goip_data_file']){
/**********************/
?>

<br> 
<table wIdth="100%" border="0" align="center" cellpadding="0" cellspacing="1" class="border">
  <tr class="title">
    <td height="22"><strong> Notice</strong></td>
  </tr>
  <tr class="tdbg">
    <td valign="middle"><ul>
        <li>This feature is in the restoration of backup data at the same time covering all the original data. Make sure that the need for recovery, in order to avoid data loss. 
	<li>Data recovery file from local should be smaller than the maxinum upload. Otherwise, you should user data file from server backup.
   	<li>If you use a sub-volume backup, only manual documents into volume v1, other data from documents into the system automatically.  
	</ul>
	</td>
  </tr>
</table>
<br>
<form action="" method="post" enctype="multipart/form-data" name="datarestore.php">
<table wIdth="100%" border="0" align="center" cellpadding="0" cellspacing="1" class="border">
  <tr class="title"> 
    <td height="22" colspan="2" align="center"><strong>Data Recovery</strong></td>
  </tr>
  <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'"> 
    <td colspan="2">Backup mode</td>
  </tr>
  <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'"> 
    <td><input type="radio" name="restorefrom" value="server" checked>
Resumption of documents from the server </td>
  <td><select name="serverfile">
    <option value="">-Please choose-</option>
    <?php
$handle=opendir('../backup');
while ($file = readdir($handle)) {
    if(eregi("^[0-9]{8,8}([0-9a-z_]+)(\.sql)$",$file)) echo "<option value='$file'>$file</option>";}
closedir($handle); 
?>
  </select></td>
  </tr>
   <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'"> 
     <td><input type="radio" name="restorefrom" value="localpc">
Resume from the local paper</td>
     <td><input type="hidden" name="MAX_FILE_SIZE" value="100000000">
      <input type="file" name="myfile"></td>
  </tr>
   <tr align="center" class="tdbg" onmouseover="this.style.backgroundColor='#BFDFFF'" onmouseout="this.style.backgroundColor=''"> 
    <td colspan="2"><input type="submit" name="act" value="Recovery"></td>
  </tr>
</table> 
</form>

<?php /**************************界面结束*/}/*************************************/
/****************************主程序*/if($_POST['act']=="Recovery"){/**************/
/***************服务器恢处17*/if($_POST['restorefrom']=="server"){/**************/
if(!$_POST['serverfile'])
	{$msgs[]="You chose to restore from the backup server document, but there is no designated back";
	 show_msg($msgs); pageend();	}
if(!eregi("_v[0-9]+",$_POST['serverfile']))
	{$filename="../backup/".$_POST['serverfile'];
	if(import($filename)) $msgs[]="Backup File".$_POST['serverfile']."Import Database Successfully";
	else $msgs[]="Backup File".$_POST['serverfile']."	Import failure";
	show_msg($msgs); pageend();		
	}
else
	{
	$filename="../backup/".$_POST['serverfile'];
	if(import($filename)) $msgs[]="Backup File".$_POST['serverfile']."Import Database Successfully";
	else {$msgs[]="Backup File".$_POST['serverfile']."Import failure";show_msg($msgs);pageend();}
	$voltmp=explode("_v",$_POST['serverfile']);
	$volname=$voltmp[0];
	$volnum=explode(".sq",$voltmp[1]);
	$volnum=intval($volnum[0])+1;
	$tmpfile=$volname."_v".$volnum.".sql";
	if(file_exists("../backup/".$tmpfile))
		{
		$msgs[]="This will automatically after three seconds into the next part of this sub-volume backup : file".$tmpfile."＄17Do not be manually operating procedures to avoid damage to the database structure ";
		$_SESSION['goip_data_file']=$tmpfile;
		show_msg($msgs);
		sleep(3);
		echo "<script language='javascript'>"; 
		echo "location='datarestore.php';"; 
		echo "</script>"; 
		}
	else
		{
		$msgs[]="Backup all of this sub-volumes into success ";
		show_msg($msgs);
		}
	}
/**************服务器恢复结杄17*/}/********************************************/
/*****************本地恢复*/if($_POST['restorefrom']=="localpc"){/**************/
	switch ($_FILES['myfile']['error'])
	{
	case 1:
	case 2:
	$msgs[]="You upload the document server than the limit, not preaching success ";
	break;
	case 3:
	$msgs[]="Upload not complete backup from the local paper";
	break;
	case 4:
	$msgs[]="Learned from the failure of backup from the local paper";
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
	$attachdir="../backup/";
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

if (file_exists("../backup/".$fname)) 
	{
	$msgs[]="Backup File Upload local success ";
	if(import("../backup/".$fname)) {$msgs[]="Backup documents into the database of local success"; unlink("../backup/".$fname);}
	else $msgs[]="Backup documents into local database failure";
	}
else ($msgs[]="Learned from the failure of backup from the local paper");
show_msg($msgs);
/****本地恢复结束*****/}/****************************************************/
/****************************主程序结杄17*/}/**********************************/
/*************************剩余分卷备份恢复**********************************/
if(!$_POST['act']&&$_SESSION['goip_data_file'])
{
	$filename="../backup/".$_SESSION['goip_data_file'];
	if(import($filename)) $msgs[]="Backup File".$_SESSION['goip_data_file']."Import Database Successfully";
	else {$msgs[]="备份文件".$_SESSION['goip_data_file']."Import failure";show_msg($msgs);pageend();}
	$voltmp=explode("_v",$_SESSION['goip_data_file']);
	$volname=$voltmp[0];
	$volnum=explode(".sq",$voltmp[1]);
	$volnum=intval($volnum[0])+1;
	$tmpfile=$volname."_v".$volnum.".sql";
	if(file_exists("../backup/".$tmpfile))
		{
		$msgs[]="This will automatically after three seconds into the next part of this sub-volume backup：file".$tmpfile."，Do not be manually operating procedures to avoid damage to the database structure";
		$_SESSION['goip_data_file']=$tmpfile;
		show_msg($msgs);
		sleep(3);
		echo "<script language='javascript'>"; 
		echo "location='datarestore.php';"; 
		echo "</script>"; 
		}
	else
		{
		$msgs[]="Backup all of this sub-volumes into success";
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
	$strErr="<html><head><title>Error Information</title><meta http-equiv='Content-Type' content='text/html; charset=ISO-8859-1'>" ;
	$strErr=$strErr."<link href='style.css' rel='stylesheet' type='text/css'></head><body>" ;
	$strErr=$strErr."<br><br><table cellpadding=2 cellspacing=1 border=0 wIdth=400 class='border' align=center>"; 
	$strErr=$strErr."  <tr align='center'><td height='22' class='title'><strong>Information </strong></td></tr>" ;
	$strErr=$strErr."  <tr><td height='100' class='tdbg' valign='top'><b>Suggest:</b><br><ul>";
	while (list($k,$v)=each($msgs))
	$strErr=$strErr."<li>".$v."</li>";
	$strErr=$strErr."</ul></td></tr>" ;
	$strErr=$strErr."  <tr align='center'><td class='tdbg'><a href=javascript:history.back();>&lt;&lt; return</a></td></tr>" ;
	$strErr=$strErr."</table>" ;
	$strErr=$strErr."</body></html>" ;
	echo $strErr;
	//exit;
}


function pageend()
{
unset($_SESSION['goip_data_file']);
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
