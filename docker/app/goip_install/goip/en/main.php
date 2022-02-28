<?php
require_once("session.php");
$sysversion=PHP_VERSION;
$sysos=$_SERVER['SERVER_SOFTWARE'];
$max_upload= ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'Disabled';
isset($_COOKIE) ? $ifcookie="SUCCESS" : $ifcookie="FAIL";

print <<<EOT

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="../style.css">
<title>GoIP SMS Management</title>
</head>

<body>
<br>
<table cellpadding="2" cellspacing="1" border="0" wIdth="100%" class="border" align=center>
  <tr align="center">
    <td height=25 colspan=2 class="topbg"><strong>Server message</strong>
  <tr>
    <td wIdth="50%"  class="tdbg" height=23>PHP version:$sysversion</td>
    <td wIdth="50%" class="tdbg">Maximum upload limit:$max_upload</td>
  </tr>
  <tr>
    <td wIdth="50%" class="tdbg" height=23>Server message:$sysos</td>
    <td wIdth="50%" class="tdbg">Cookie test:$ifcookie</td>
  </tr>
  <tr>
    <td class="tdbg" height=23>&nbsp;</td>
    <td align="right" class="tdbg">&nbsp;</td>
  </tr>
</table>
</body>
</html>

EOT;
?>

