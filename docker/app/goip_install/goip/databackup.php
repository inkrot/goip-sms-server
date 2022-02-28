<?php 
require_once("session.php");
define("OK", true);
if($_SESSION['goip_permissions'] > 1)	
	die("need admin permissions!");	
ignore_user_abort(true);
set_time_limit(0);
ini_set("memory_limit", "200M");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Column management</title>
<meta name="Author" content="Gaby_chen">
<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg"> 
    <td height="22" colspan="2" align="center"><strong>数据维护</strong></td>
  </tr>
  <tr class="tdbg"> 
    <td wIdth="100" height="30"><strong>管理导航:</strong></td>
    <td height="30"><a href="databackup.php"  target=main>数据备份</a> | <a href="datarestore.php" target=main>数据导入</a></td>
  </tr>
</table>
<?php
//define('OK', true);
require_once('inc/conn.inc.php');
//	$dbhost='localhost';	//database server
//	$dbuser='goip';		//database username
//	$dbpw='goip';		//database password
//	$dbname='goip';		//database name
	
global $mysqlhost, $mysqluser, $mysqlpwd, $mysqldb;
$mysqlhost=$dbhost; //host name
$mysqluser=$dbuser;              //login name
$mysqlpwd=$dbpw;              //password
$mysqldb=$dbname;        //name of database

require_once("datamydb.php");
$d=new datadb($mysqlhost,$mysqluser,$mysqlpwd,$mysqldb);
/*--------------界面--------------*/
if(!$_POST['act']){
/*----------------------*/
?>

<br> 
<table wIdth="100%" border="0" align="center" cellpadding="0" cellspacing="1" class="border">
  <tr class="title">
    <td height="22"><strong> 提示： </strong></td>
  </tr>
  <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'">
    <td valign="middle"><ul>
        <li>服务器备份目录为backup
        <li>对于较大的数据表，强烈建议使用分卷备份
        <li>只有选择备份到服务器，才能使用分卷备份功能 </li>
   	 </ul>
	</td>
  </tr>
</table>
<br>
<form name="form1" method="post" action="databackup.php">
<table wIdth="100%" border="0" align="center" cellpadding="0" cellspacing="1" class="border">
  <tr class="title"> 
    <td height="22" colspan="2" align="center"><strong>数据备份</strong></td>
  </tr>
  <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'"> 
    <td colspan="2">备份方式</td>
  </tr>
  <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'"> 
    <td><input name="bfzl" type="radio" value="quanbubiao" checked>
备份全部数据</td>
  <td>备份全部数据表中的数据到一个备份文件</td>
  </tr>

   <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'"> 
    <td colspan="2">使用分卷备份</td>
  </tr>
   <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'"> 
    <td colspan="2"><input type="checkbox" name="fenjuan" value="yes">
分卷备份
  <input name="filesize" type="text" size="10">
  K</td>
  </tr>
   <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'"> 
    <td colspan="2">选择目标位置</td>
  </tr>
   <tr class="tdbg" onmouseout="this.style.backgroundColor=''" onmouseover="this.style.backgroundColor='#BFDFFF'"> 
    <td><input type="radio" name="weizhi" value="server" checked>
      备份到服务器</td>
  <td><input type="radio" name="weizhi" value="localpc">
备份到本地</td>
  </tr>
   <tr align="center" class="tdbg" onmouseover="this.style.backgroundColor='#BFDFFF'" onmouseout="this.style.backgroundColor=''"> 
    <td colspan="2"><input type="submit" name="act" value="备份"></td>
  </tr>
</table> 
</form>

<?php /*-------------界面结束-------------*/}/*---------------------------------*/
/*----*/else{/*--------------主程序-----------------------------------------*/
print_r($_POST);
if($_POST['weizhi']=="localpc"&&$_POST['fenjuan']=='yes')
	{$msgs[]="只有选择备份到服务器，才能使用分卷备份功能";
show_msg($msgs); pageend();}
if($_POST['fenjuan']=="yes"&&!$_POST['filesize'])
	{$msgs[]="您选择了分卷备份功能，但未填写分卷文件大小";
show_msg($msgs); pageend();}
if($_POST['weizhi']=="server"&&!writeable("./backup"))
	{$msgs[]="备份文件存放目录'./backup'不可写，请修改目录属性";
show_msg($msgs); pageend();}

/*----------备份全部表-------------*/if($_POST['bfzl']=="quanbubiao"){/*----*/
/*----不分卷*/if(!$_POST['fenjuan']){/*--------------------------------*/
if(!$tables=$d->query("show table status from $mysqldb"))
	{$msgs[]="读数据库结构错误"; show_msg($msgs); pageend();}
$sql="SET FOREIGN_KEY_CHECKS = 0\n";
//print $sql;
while($d->nextrecord($tables))
	{
	//print "dddd1";
	$table=$d->f("Name");
	$sql.=make_header($table);
	$d->query("select * from $table");
	$num_fields=$d->nf();
	while($d->nextrecord())
	{
		$sql.=make_record($table,$num_fields);
	}
	//echo $sql;
}
	//print_r($sql);
	$sql.="SET FOREIGN_KEY_CHECKS = 1\n";
	//echo $sql;
$filename=date("Ymdhis",time())."_all.sql";
if($_POST['weizhi']=="localpc") {down_file($sql,$filename);}
elseif($_POST['weizhi']=="server")
	{
	//echo $sql;
	if(write_file($sql,$filename))
		$msgs[]="全部数据表数据备份完成,生成备份文件'./backup/$filename'";
	else 	
		$msgs[]="备份全部数据表失败";
	show_msg($msgs);
	pageend();
	}
/*-----------------不要卷结束*/}/*-----------------------*/
/*-----------------分卷*/else{/*-------------------------*/
if(!$_POST['filesize'])
	{$msgs[]="请填写备份文件分卷大小"; show_msg($msgs);pageend();}
if(!$tables=$d->query("show table status from $mysqldb"))
	{$msgs[]="读数据库结构错误"; show_msg($msgs); pageend();}
$sql="SET FOREIGN_KEY_CHECKS = 0\n"; $p=1;
$filename=date("Ymdhis",time())."_all";
while($d->nextrecord($tables))
{
	$table=$d->f("Name");
	$sql.=make_header($table);
	$d->query("select * from $table");
	$num_fields=$d->nf();
	while($d->nextrecord())
	{$sql.=make_record($table,$num_fields);
	if(strlen($sql)>=$_POST['filesize']*1000){
			$filename.=("_v".$p.".sql");
			if(write_file($sql,$filename))
			$msgs[]="全部数据表-卷-".$p."-数据备份完成,生成备份文件'./backup/$filename'";
			else $msgs[]="备份表-".$_POST['tablename']."-失败";
			$p++;
			$filename=date("Ymdhis",time())."_all";
			$sql="";}
	}
}
$sql.="SET FOREIGN_KEY_CHECKS = 1\n";
if($sql!=""){$filename.=("_v".$p.".sql");		
if(write_file($sql,$filename))
$msgs[]="全部数据表-卷-".$p."-数据备份完成,生成备份文件'./backup/$filename'";}
show_msg($msgs);
/*---------------------分卷结束*/}/*--------------------------------------*/
/*--------备份全部表结束*/}/*---------------------------------------------*/

/*--------备份单表------*/elseif($_POST['bfzl']=="danbiao"){/*------------*/
if(!$_POST['tablename'])
	{$msgs[]="请选择要备份的数据表"; show_msg($msgs); pageend();}
/*--------不分卷*/if(!$_POST['fenjuan']){/*-------------------------------*/
$sql=make_header($_POST['tablename']);
$d->query("select * from ".$_POST['tablename']);
$num_fields=$d->nf();
while($d->nextrecord())
	{$sql.=make_record($_POST['tablename'],$num_fields);}
$filename=date("Ymdhis",time())."_".$_POST['tablename'].".sql";
if($_POST['weizhi']=="localpc") down_file($sql,$filename);
elseif($_POST['weizhi']=="server")
	{if(write_file($sql,$filename))
$msgs[]="表-".$_POST['tablename']."-数据备份完成,生成备份文件'./backup/$filename'";
	else $msgs[]="备份表-".$_POST['tablename']."-失败";
	show_msg($msgs);
	pageend();
	}
/*----------------不要卷结束*/}/*------------------------------------*/
/*----------------分卷*/else{/*--------------------------------------*/
if(!$_POST['filesize'])
	{$msgs[]="请填写备份文件分卷大小"; show_msg($msgs);pageend();}
$sql=make_header($_POST['tablename']); $p=1; 
	$filename=date("Ymdhis",time())."_".$_POST['tablename'];
	$d->query("select * from ".$_POST['tablename']);
	$num_fields=$d->nf();
	while ($d->nextrecord()) 
	{	
		$sql.=make_record($_POST['tablename'],$num_fields);
	   if(strlen($sql)>=$_POST['filesize']*1000){
			$filename.=("_v".$p.".sql");
			if(write_file($sql,$filename))
			$msgs[]="表-".$_POST['tablename']."-卷-".$p."-数据备份完成,生成备份文件'./backup/$filename'";
			else $msgs[]="备份表-".$_POST['tablename']."-失败";
			$p++;
			$filename=date("Ymdhis",time())."_".$_POST['tablename'];
			$sql="";}
	}
if($sql!=""){$filename.=("_v".$p.".sql");		
if(write_file($sql,$filename))
$msgs[]="表-".$_POST['tablename']."-卷-".$p."-数据备份完成,生成备份文件'./backup/$filename'";}
show_msg($msgs);
/*----------分卷结束*/}/*--------------------------------------------------*/
/*----------备份单表结束*/}/*----------------------------------------------*/

/*---*/}/*-------------主程序结束------------------------------------------*/

function write_file($sql,$filename)
{
$re=true;
if(!@$fp=fopen("backup/".$filename,"w+")) {$re=false; echo "failed to open target file";}
if(!@fwrite($fp,$sql)) {$re=false; echo "failed to write file";}
if(!@fclose($fp)) {$re=false; echo "failed to close target file";}
return $re;
}



function down_file($sql,$filename)
{
	$filesize=sizeof($sql);

	ob_end_clean();

	header("Expires: 0");
	Header("Content-type: application/octet-stream");
	Header("Accept-Ranges: bytes");
	Header("Accept-Length: ".$filesize);
	Header("Content-Disposition: attachment; filename=" . $filename);
	if($filesize>0)
	{
		echo $sql;
	}
	ob_get_contents();
	ob_end_clean();
	exit;
}

function writeable($dir)
{

	if(!is_dir($dir)) {
	@mkdir($dir, 0777);
	}
	
	if(is_dir($dir)) 
	{
	
	if($fp = @fopen("$dir/test.test", 'w'))
		{
@fclose($fp);
	@unlink("$dir/test.test");
	$writeable = 1;
} 
	else {
$writeable = 0;
	}
	
}
	
	return $writeable;

}

function make_header($table)
{global $d;
$sql="DROP TABLE IF EXISTS ".$table."\n";
$d->query("show create table ".$table);
$d->nextrecord();
$tmp=preg_replace("/\n/","",$d->f("Create Table"));
$sql.=$tmp."\n";
return $sql;
}

function make_record($table,$num_fields)
{global $d;
$comma="";
$sql .= "INSERT INTO ".$table." VALUES(";
for($i = 0; $i < $num_fields; $i++) 
{$sql .= ($comma."'".mysql_escape_string($d->record[$i])."'"); $comma = ",";}
$sql .= ")\n";
return $sql;
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
?>

<br><br>
</body> 
</html> 
